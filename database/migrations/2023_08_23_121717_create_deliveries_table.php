<?php

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
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carrier_id')->constrained('carriers');
            $table->foreignId('shipper_id')->constrained('shippers');
            $table->date('received')->nullable();
            $table->date('scheduled')->nullable();
            $table->date('estimated_delivery')->nullable();
            $table->string('parcel')->nullable();
            $table->string('invoice')->nullable()->unique();
            $table->string('quantity_of_packages')->nullable();
            $table->string('invoice_key')->nullable();
            $table->string('package_number')->nullable();
            $table->string('weight')->nullable();
            $table->string('total_weight')->nullable();
            $table->string('destination_name')->nullable();
            $table->string('destination_tax_id')->nullable();
            $table->string('destination_phone')->nullable();
            $table->string('destination_email')->nullable();
            $table->string('destination_zip_code')->nullable();
            $table->string('destination_address')->nullable();
            $table->string('destination_number')->nullable();
            $table->string('destination_neighborhood')->nullable();
            $table->string('destination_city')->nullable();
            $table->string('destination_state')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};
