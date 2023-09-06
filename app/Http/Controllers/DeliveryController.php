<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
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
        }])->orderBy('id');
        
        return DataTables::of($entregas)->make(true);
    }
}
