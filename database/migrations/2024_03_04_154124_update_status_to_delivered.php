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
        // Atualiza os registros na tabela status_history
        DB::table('status_history')
            ->whereIn('status', [
                'consolidado',
                'finalizado',
                'entrega realizada (mobile)',
                'assinatura de encomenda',
                'entrega realizada'
            ])
            ->update(['status' => 'Entregue']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Não há necessidade de reverter esta migração, pois ela apenas atualiza dados.
    }
};
