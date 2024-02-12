<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\StatusHistory;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class DeliveryController extends Controller
{
    public function index(){
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

    public function getStatus(Request $request){
        if(isset($request->numero_nota)){
            $invoiceValue = $request->numero_nota; // Substitua 'X' pelo valor de invoice desejado

            $historicosStatus = StatusHistory::with('deliveries')->whereHas('deliveries', function ($query) use ($invoiceValue) {
                $query->where('invoice', $invoiceValue);
            })->get();
            if ($historicosStatus->isEmpty()) {
                echo json_encode(array("Mensagem error" => "Nota nao encrontrada no sistema"));
            }else{
             //   dd($historicosStatus[0]->deliveries);
                $data["Pedidos"] = array(
                    "NrCnpj" => "23966188000122",
                    "NrNota" => $historicosStatus[0]->deliveries->invoice,
                    "serie" => $historicosStatus[0]->deliveries->serie ,
                    "id" => $historicosStatus[0]->deliveries->id 
                );
                
                foreach($historicosStatus as $historicoStatus){
                   
                    $data["Ocorrencias"][] = array(
                        'data' => $historicoStatus->created_at ,
                        'status' =>  $historicoStatus->status ,
                        'observation' => $historicoStatus->observation ,
                        'detail' => $historicoStatus->detail ,
                    );
                  }
                  
        
                    echo json_encode($data);

            }

         
            

        }else{
            echo json_encode(array("Mensagem error" => "enviar numero_nota"));
        }

    }
}
