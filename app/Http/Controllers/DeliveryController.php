<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\StatusHistory;
use Carbon\Carbon;
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


                $statusOrder = array(
                    'Entregue' => 1,
                    'Saiu para Entregar' => 2,
                    // Adicione outros status conforme necessário
                );
                
                $data["Ocorrencias"] = array(); // Inicialize o array
                
                foreach ($historicosStatus as $historicoStatus) {
                    $data["Ocorrencias"][] = array(
                        'data' => $historicoStatus->created_at,
                        'status' =>  $historicoStatus->status,
                        'observation' => $historicoStatus->observation,
                        'detail' => $historicoStatus->detail,
                    );
                }
                
                // Função de comparação para ordenar os status
                function compareStatus($a, $b) {
                    global $statusOrder;
                    $aOrder = isset($statusOrder[$a['status']]) ? $statusOrder[$a['status']] : PHP_INT_MAX;
                    $bOrder = isset($statusOrder[$b['status']]) ? $statusOrder[$b['status']] : PHP_INT_MAX;
                    return $aOrder - $bOrder;
                }
                
                // Ordena o array de acordo com o status
                usort($data["Ocorrencias"], 'compareStatus');

                echo json_encode($data);
            }
        } else {
            echo json_encode(array("Mensagem error" => "enviar numero_nota"));
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
