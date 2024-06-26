<?php

namespace App\Http\Controllers;

use App\Models\Carrier;
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

        // Obtém a data de 7 dias atrás
    $sevenDaysAgo = $today->copy()->subDays(7);

    // Conta as entregas com status "Arquivo Recebido" nos últimos 7 dias
    $countLast7Days = Delivery::whereHas('status', function ($query) use ($today, $sevenDaysAgo) {
        $query->where('status', 'Arquivo Recebido')
              ->whereBetween('created_at', [$sevenDaysAgo, $today]);
    })->count();

    // Obtém a data de 30 dias atrás
    $thirtyDaysAgo = $today->copy()->subDays(30);

    // Conta as entregas com status "Arquivo Recebido" nos últimos 30 dias
    $countLast30Days = Delivery::whereHas('status', function ($query) use ($today, $thirtyDaysAgo) {
        $query->where('status', 'Arquivo Recebido')
              ->whereBetween('created_at', [$thirtyDaysAgo, $today]);
    })->count();

        $in_progress = Delivery::whereDoesntHave('status', function ($query) {
            $query->where('status', 'Entregue');
        })->count();

        $overdue  = Delivery::where('estimated_delivery', '<=', $today)
            ->whereDoesntHave('status', function ($query) {
                $query->where('status', 'Entregue');
            })
            ->count();

        $returned = Delivery::whereHas('status', function ($query) {
            $query->where('status', 'devolvido');
        })->count();

        $carries = Carrier::all();

        $carriesResult = [];
        foreach ($carries as $carrie) {
            $total = Delivery::where('carrier_id', $carrie->id)->count();

            $in_progress_carrie = Delivery::whereDoesntHave('status', function ($query) {
                $query->where('status', 'Entregue');
            })->where('carrier_id', $carrie->id)->count();
            // Calcula o total pendente (overdue)

            $inProgressOnTime = Delivery::whereDoesntHave('status', function ($query) {
                $query->where('status', 'Entregue');
            })->where('carrier_id', $carrie->id)
                ->where(function ($query) {
                    $query->whereRaw('(
                    SELECT MAX(created_at)
                    FROM status_history
                    WHERE delivery_id = deliveries.id
                    AND status != "Entregue"
                ) <= deliveries.estimated_delivery');
                })
                ->count();

            $inProgressDelayed =    $in_progress_carrie - $inProgressOnTime;
            // Porcentagem de entregas em progresso no prazo
            $percentageInProgressOnTime = ($inProgressOnTime / $in_progress_carrie) * 100;

        

            // Porcentagem de entregas em progresso atrasadas
            $percentageInProgressDelayed = ($inProgressDelayed / $in_progress_carrie) * 100;

            $percentageInProgressOnTime = number_format($percentageInProgressOnTime, 2);
            $percentageInProgressDelayed = number_format($percentageInProgressDelayed, 2);
            $finished = $total - $in_progress_carrie;
            // Calcula as porcentagens
            $percentage_in_progress = ($in_progress_carrie / $total) * 100;
            $percentage_finished = ($finished / $total) * 100;

            // Formata as porcentagens com duas casas decimais
            $percentage_in_progress = number_format($percentage_in_progress, 2);
            $percentage_finished = number_format($percentage_finished, 2);

            $deliveriesOnTime = Delivery::whereHas('status', function ($query) {
                $query->where('status', 'Entregue');
            })->where('carrier_id', $carrie->id)
                ->where(function ($query) {
                    $query->whereRaw('(
                    SELECT MAX(created_at)
                    FROM status_history
                    WHERE delivery_id = deliveries.id
                    AND status = "Entregue"
                ) <= deliveries.estimated_delivery');
                })
                ->count();

            $deliveriesDelayed = $finished - $deliveriesOnTime;

            // Calcula as porcentagens
            $percentage_delayed = ($deliveriesDelayed / $total) * 100;
            $percentage_ontime = ($deliveriesOnTime / $total) * 100;

            // Formata as porcentagens com duas casas decimais
            $percentage_delayed = number_format($percentage_delayed, 2);
            $percentage_ontime = number_format($percentage_ontime, 2);
            // Adiciona os dados ao array $carriesResult
            $carriesResult[] = [
                'carrie' => $carrie,
                'total' => $total,
                'in_progress' => $in_progress_carrie,
                'finished' => $finished,
                'percentage_in_progress' => $percentage_in_progress,
                'percentage_finished' => $percentage_finished,
                'deliveriesOnTime' => $deliveriesOnTime,
                'deliveriesDelayed' => $deliveriesDelayed,
                'percentage_delayed' => $percentage_delayed,
                'percentage_ontime' => $percentage_ontime,
                'inProgressOnTime' => $inProgressOnTime,
                'inProgressDelayed' => $inProgressDelayed,
                'percentageInProgressOnTime' => $percentageInProgressOnTime,
                'percentageInProgressDelayed' => $percentageInProgressDelayed,
            ];
        }
        $data = array(
            'carriesResult' => $carriesResult,
            'countToday' => $countToday,
            'count_last_7_days' => $countLast7Days,
            'count_last_30_days' => $countLast30Days,
            'in_progress' => $in_progress,
            'overdue' => $overdue,
            'returned' => $returned,
        );




        return view('admin.home.index', $data);
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

    public function devolucao(){
        return view('admin.home.devolucao');
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
