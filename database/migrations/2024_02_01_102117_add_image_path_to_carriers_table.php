<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddImagePathToCarriersTable extends Migration
{
    /**
     * Run the migrations.
     * 
     * @return void
     */
    public function up()
    {
        Schema::table('carriers', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('legal_name');
        });
    }

    /**
     * Reverse the migrations.
     * 
     * @return void
     */
    public function down()
    {
        Schema::table('carriers', function (Blueprint $table) {
            $table->dropColumn('image_path');
        });
    }
}
