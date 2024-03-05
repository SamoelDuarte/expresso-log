<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
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
        // Obtém a data de hoje
        $today = Carbon::today();

        // Conta as entregas que possuem um histórico de status com a data de hoje e status "Arquivo Recebido"
        $countToday = Delivery::whereHas('status', function ($query) use ($today) {
            $query->where('status', 'Arquivo Recebido')
                ->whereDate('created_at', $today);
        })->count();

        $in_progress = Delivery::whereDoesntHave('status', function ($query) {
            $query->where('status', 'Entregue');
        })->whereDate('updated_at', '>', '2024-02-25')->count();

        $overdue  = Delivery::where('estimated_delivery', '<=', $today)
            ->whereDoesntHave('status', function ($query) {
                $query->where('status', 'Entregue');
            })
            ->count();

        $returned = Delivery::whereHas('status', function ($query) {
            $query->where('status', 'devolvido');
        })->count();


        return view('admin.home.index', compact('countToday', 'in_progress', 'overdue', 'returned'));
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

    public function statusDash(Request $request)
    {
        $statusCounts = DB::table('deliveries')
            ->leftJoin('status_history', function ($join) {
                $join->on('deliveries.id', '=', 'status_history.delivery_id')
                    ->whereRaw('status_history.id = (select max(id) from status_history where status_history.delivery_id = deliveries.id)');
            })
            ->whereNotIn('status_history.status', ['entregue', 'devolvido']) // Exclui deliveries com status de "entregue" e "devolvido"
            ->orWhereNull('status_history.status') // Também inclui deliveries sem status_history
            ->whereDate('status_history.created_at', '>', '2024-02-25') // Considera apenas as entregas após a data específica de criação do status_history
            ->select('status_history.status', DB::raw('count(*) as count'))
            ->groupBy('status_history.status')
            ->get();


        return response()->json($statusCounts);
    }
}
