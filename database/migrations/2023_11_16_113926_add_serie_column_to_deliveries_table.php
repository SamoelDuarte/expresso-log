<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSerieColumnToDeliveriesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('deliveries', function (Blueprint $table) {
            $table->string('serie')->nullable(); // Adiciona a nova coluna 'serie'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropColumn('serie'); // Remove a coluna 'serie' se a migração for revertida
        });
    }
}
