<?php

namespace App\Http\Controllers;

use App\Models\Error as ModelsError;
use App\Models\StatusHistory;
use Carbon\Carbon;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        return view('admin.home.index');
    }

    public function filter(Request $request)
    {
        // Obtenha as datas de início e fim do request
        $dateStart = $request->input('dateStart');
        $dateEnd = $request->input('dateEnd');


        $dateStart = Carbon::parse($dateStart)->startOfDay();
        $dateEnd = Carbon::parse($dateEnd)->endOfDay();
        // Filtre os erros com base nas datas fornecidas
        $errors = ModelsError::whereBetween('created_at', [$dateStart, $dateEnd])
            ->orderBy('created_at', 'desc')
            ->get();

        // Formate a data de 'created_at' em 'd/m/Y H:i' para cada erro
        $errors->transform(function ($error) {
            $error->formatted_created_at = Carbon::parse($error->created_at)->format('d/m/Y H:i');
            return $error;
        });


        return response()->json(['errors' => $errors]);
    }

    public function filterStatus(Request $request)
    {
        // Obtenha as datas de início e fim do request
        $dateStart = $request->input('dateStart');
        $dateEnd = $request->input('dateEnd');


        $dateStart = Carbon::parse($dateStart)->startOfDay();
        $dateEnd = Carbon::parse($dateEnd)->endOfDay();
        // Filtre os erros com base nas datas fornecidas
        $errors = ModelsError::whereBetween('created_at', [$dateStart, $dateEnd])
            ->orderBy('created_at', 'desc')
            ->get();
        $dadosAgrupados = DB::table('status_history as sh1')
            ->select('sh1.delivery_id', 'sh1.status', 'sh1.created_at', DB::raw('COUNT(*) as amount'))
            ->join(DB::raw('(SELECT MAX(id) as max_id, delivery_id FROM status_history GROUP BY delivery_id) as sh2'), function ($join) {
                $join->on('sh1.id', '=', 'sh2.max_id');
            })
            ->groupBy('sh1.delivery_id', 'sh1.status', 'sh1.created_at')
            ->orderBy('sh1.delivery_id')
            ->get();

        return response()->json($dadosAgrupados);
    }
}
