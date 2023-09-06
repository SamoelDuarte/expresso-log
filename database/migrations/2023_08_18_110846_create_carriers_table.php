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
        Schema::create('carriers', function (Blueprint $table) {
            $table->id();
            $table->string('legal_name')->nullable();
            $table->string('trade_name')->nullable();
            $table->string('state_registration')->nullable();
            $table->string('phone')->nullable();
            $table->string('rntrc')->nullable();
            $table->date('rntrc_expiry_date')->nullable();
            $table->boolean('opted_for_simples_nacional')->default(false);
            $table->string('zip_code')->nullable();
            $table->string('address')->nullable();
            $table->string('number')->nullable();
            $table->string('complement')->nullable();
            $table->string('neighborhood')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('landmark')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('contact_phone1')->nullable();
            $table->string('contact_phone2')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('customer_service_phone')->nullable();
            $table->string('customer_service_email')->nullable();
            $table->string('website')->nullable();
            $table->string('title')->nullable();
            $table->text('message')->nullable();
            $table->timestamps();
        });
        

      
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carriers');
    }
};
