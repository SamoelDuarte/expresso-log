<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('status_history')
            ->where('status', 'entregue')
            ->orWhere('status', 'finalizado')
            ->update(['send' => 1]);

        // Remover registros duplicados
        DB::table('status_history')
            ->where('send', 1)
            ->whereRaw('id NOT IN (SELECT MIN(id) FROM status_history WHERE send = 1 GROUP BY status, delivery_id)')
            ->delete();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Reverter as alterações, se necessário
        DB::table('status_history')
            ->whereIn('status', ['entregue', 'finalizado'])
            ->update(['send' => 0]);
    }
};
