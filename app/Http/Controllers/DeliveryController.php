<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\StatusHistory;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class DeliveryController extends Controller
{
    public function index()
    {
        return view('admin.entrega.index');
    }

    public function getEntregas()
    {

        $entregas = Delivery::with('carriers')
            ->with(['status' => function ($query) {
                $query->orderBy('created_at', 'desc');
            }])
            ->orderByDesc(
                StatusHistory::select('created_at')
                    ->whereColumn('delivery_id', 'deliveries.id')
                    ->orderBy('created_at', 'desc')
                    ->limit(1)
            );

        return DataTables::of($entregas)->make(true);
    }

    public function getEntregasDevolution()
    {

        $entregas = Delivery::with('carriers')
            ->with(['status' => function ($query) {
                $query->orderBy('created_at', 'desc');
            }])
            ->orderByDesc(
                StatusHistory::select('created_at')
                    ->whereColumn('delivery_id', 'deliveries.id')
                    ->orderBy('created_at', 'desc')
                    ->limit(1)
            );

            $returned = Delivery::with('carriers')->whereHas('status', function ($query) {
                $query->where('status', 'devolvido');
            })->get();

        return DataTables::of($returned)->make(true);
    }

    public function show($id)
    {
        // Busca a entrega pelo ID
        $entrega = Delivery::findOrFail($id);

        // Retorna a view 'entrega.show' passando a entrega como parâmetro
        return view('admin.entrega.show', compact('entrega'));
    }

    public function getStatus(Request $request)
    {
        if (isset($request->numero_nota)) {
            $invoiceValue = $request->numero_nota; // Substitua 'X' pelo valor de invoice desejado

            $historicosStatus = StatusHistory::with('deliveries')->whereHas('deliveries', function ($query) use ($invoiceValue) {
                $query->where('invoice', $invoiceValue);
            })->get();
            if ($historicosStatus->isEmpty()) {
                echo json_encode(array("Mensagem error" => "Nota nao encrontrada no sistema"));
            } else {
                //   dd($historicosStatus[0]->deliveries);
                $data["Pedidos"] = array(
                    "NrCnpj" => "23966188000122",
                    "NrNota" => $historicosStatus[0]->deliveries->invoice,
                    "serie" => $historicosStatus[0]->deliveries->serie,
                    "id" => $historicosStatus[0]->deliveries->id
                );

                foreach ($historicosStatus as $historicoStatus) {
                    $data["Ocorrencias"][] = array(
                        'data' => $historicoStatus->created_at,
                        'status' =>  $historicoStatus->status,
                        'observation' => $historicoStatus->observation,
                        'detail' => $historicoStatus->detail,
                    );
                }

                // Ordena os status
                usort($data["Ocorrencias"], function ($a, $b) {
                    if ($a['status'] === 'Entregue') return 1; // Garante que 'Entregue' seja o último
                    if ($b['status'] === 'Entregue') return -1;
                    if ($a['status'] === 'Saiu para Entrega') return 1; // Garante que 'Saiu para Entrega' seja o penúltimo
                    if ($b['status'] === 'Saiu para Entrega') return -1;
                    return 0; // Para os outros casos, mantenha a ordem original
                });

                echo json_encode($data);
            }
        } else {
            echo json_encode(array("Mensagem error" => "enviar numero_nota"));
        }
    }

    public function getStatusCorreio(Request $request)
    {


        $token = $this->authCorreios();
        $client = new Client();
        $response = $client->request('GET', 'https://api.correios.com.br/srorastro/v1/objetos', [
            'query' => [
                'codigosObjetos' => $request->codigo,
                'resultado' => 'T'
            ],
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $token
            ]
        ]);

        // Converte os dados JSON para um array associativo
        $data = json_decode($response->getBody()->getContents(), true);

        // Extrai as informações relevantes
        $pedidos = [];
        $ocorrencias = [];

        foreach ($data['objetos'] as $objeto) {


            foreach ($objeto['eventos'] as $evento) {

                if ($evento['descricao'] == "Objeto entregue ao destinatário") {
                    $evento['descricao'] = "Entregue";
                } else if ($evento['descricao'] == "Objeto saiu para entrega ao destinatário") {
                    $evento['descricao'] = "Saiu para entregar";
                } else if ($evento['descricao'] == "Objeto em transferência - por favor aguarde") {
                    $evento['descricao'] = "Em transferência entre cidades";
                }
                $ocorrencias[] = [
                    'data' => $evento['dtHrCriado'],
                    'status' => $evento['descricao'],
                    'observation' => null,
                    'detail' => null,
                ];
            }
        }

        // Monta o array final
        $resultado = [
            'Ocorrencias' => $ocorrencias,
        ];
        // Reverte a ordem dos índices no array $resultado
        $resultado['Ocorrencias'] = array_reverse($resultado['Ocorrencias']);
        // Converte o array para JSON e exibe
        echo json_encode($resultado, JSON_PRETTY_PRINT);
    }
    function authCorreios()
    {
        // URL para solicitar o token
        $tokenUrl = 'https://api.correios.com.br/token/v1/autentica/cartaopostagem';

        // Credenciais de autenticação
        $username = env('USER_CORREIO'); // Usuário Correios
        $password = env('PASS_CORREIO'); // Senha Correios

        // Configuração do cliente Guzzle
        $client = new Client();

        try {
            // Fazendo a solicitação HTTP para obter o token
            $response = $client->request('POST', $tokenUrl, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Basic ' . base64_encode($username . ':' . $password)
                ],
                'json' => [
                    'numero' =>  env('COD_POSTAGEM_CORREIO')
                ]
            ]);

            // dd(json_decode($response->getBody(), true));
            // Verifica se a resposta foi bem-sucedida (código de status 200)
            if ($response->getStatusCode() === 201) {
                // Extrai o corpo da resposta (JSON)
                $tokenData = json_decode($response->getBody(), true);
                // Faça o que quiser com os dados do token
                return $tokenData['token'];
            } else {
                echo 'Erro ao obter o token: ' . $response->getStatusCode() . ' - ' . $response->getReasonPhrase() . PHP_EOL;
            }
        } catch (RequestException $e) {
            // Em caso de erro na requisição
            echo 'Erro na solicitação: ' . $e->getMessage() . PHP_EOL;
        }
    }
    public static function getDeliverys($numbersToSearch)
    {
        // Primeiro, obtemos os IDs únicos dos deliveries que correspondem aos critérios, excluindo status específicos
        $uniqueDeliveries = Delivery::select('external_code', DB::raw('MIN(id) as id'))
            ->whereHas('carriers', function ($query) use ($numbersToSearch) {
                $query->whereHas('documents', function ($documentQuery) use ($numbersToSearch) {
                    $documentQuery->whereIn('number', $numbersToSearch);
                });
            })
            ->whereDoesntHave('status', function ($query) {
                $query->whereIn('status', ['finalizado', 'Entregue', 'Entrega Realizada', 'Entrega Realizada (Mobile)', 'devolvido']);
            })
            ->where(function ($query) {
                $query->whereNull('updated_at')
                    ->orWhere('updated_at', '<=', Carbon::now()->subHour()->format('Y-m-d H:i:s'));
            })
            ->groupBy('external_code')
            ->pluck('id');

        // Depois, usamos os IDs únicos para obter as entregas, garantindo que cada external_code seja único
        $deliveries = Delivery::with('carriers.documents')
            ->whereIn('id', $uniqueDeliveries)
            ->orderBy('id')
            ->limit(20)
            ->get();

        return $deliveries;
    }
}
