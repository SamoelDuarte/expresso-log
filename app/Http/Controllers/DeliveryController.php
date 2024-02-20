<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\StatusHistory;
use Carbon\Carbon;
use Illuminate\Http\Request;
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

                foreach ($historicosStatus as $historicoStatus) {

                    $data["Ocorrencias"][] = array(
                        'data' => $historicoStatus->created_at,
                        'status' =>  $historicoStatus->status,
                        'observation' => $historicoStatus->observation,
                        'detail' => $historicoStatus->detail,
                    );
                }


                echo json_encode($data);
            }
        } else {
            echo json_encode(array("Mensagem error" => "enviar numero_nota"));
        }
    }

    public static function getDeliverys($numbersToSearch){
        $deliveryes = Delivery::with('carriers.documents')
        ->whereHas('carriers', function ($query) use ($numbersToSearch) {
            $query->whereHas('documents', function ($documentQuery) use ($numbersToSearch) {
                $documentQuery->whereIn('number', $numbersToSearch);
            });
        })
        ->whereDoesntHave('status', function ($query) {
            $query->where('status', 'finalizado')
                ->orWhere('status', 'entregue')
                ->orWhere('status', 'Entrega Realizada')
                ->orWhere('status', 'Entrega Realizada (Mobile)')
                ->orWhere('status', 'devolvido');
        })
        ->where(function ($query) {
            $query->whereNull('updated_at')
                ->orWhere('updated_at', '<=', Carbon::now()->subHour()->format('Y-m-d H:i:s'));
        })
        ->orderBy('id')
        ->limit(15)
        ->get();


        return $deliveryes;
    }
}
