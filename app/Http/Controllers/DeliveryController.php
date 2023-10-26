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
        }])->orderBy('created_at', 'desc');
        
        return DataTables::of($entregas)->make(true);
    }

    public function getStatus(Request $request){
        if(isset($request->numero_nota)){
            $invoiceValue = $request->numero_nota; // Substitua 'X' pelo valor de invoice desejado

            $historicosStatus = StatusHistory::whereHas('deliveries', function ($query) use ($invoiceValue) {
                $query->where('invoice', $invoiceValue);
            })->get();
            if ($historicosStatus->isEmpty()) {
                echo json_encode(array("Mensagem error" => "Nota nao encrontrada no sistema"));
            }else{
                foreach($historicosStatus as $historicoStatus){
                    $data[] = array(
                        'data' => $historicoStatus->created_at ,
                        'status' =>  $historicoStatus->status 
                    );
                  }
                  
        
                    echo json_encode($data);

            }

         
            

        }else{
            echo json_encode(array("Mensagem error" => "enviar numero_nota"));
        }

    }
}
