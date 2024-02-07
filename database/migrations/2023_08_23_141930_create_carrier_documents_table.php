<?php

use App\Models\CarrierDocument;
use App\Models\Carrier;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('carrier_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carrier_id')->constrained('carriers');
            $table->string('number');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carrier_documents');
    }
};
