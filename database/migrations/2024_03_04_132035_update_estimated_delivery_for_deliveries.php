<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateEstimatedDeliveryForDeliveries extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        DB::table('deliveries')
            ->whereNull('estimated_delivery')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('status_history')
                      ->whereRaw('deliveries.id = status_history.delivery_id')
                      ->where('status', 'Arquivo Recebido');
            })
            ->update([
                'estimated_delivery' => DB::raw('DATE_ADD((SELECT created_at FROM status_history WHERE deliveries.id = status_history.delivery_id AND status = "Arquivo Recebido"), INTERVAL 10 DAY)')
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      
    }
}
