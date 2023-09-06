<?php

use App\Models\Shipper;
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
        Schema::create('shippers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
            $table->string('legal_name')->nullable();
            $table->string('trade_name')->nullable();
            $table->string('tax_id')->nullable();
            $table->string('tax_registration_number')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('address')->nullable();
            $table->string('number')->nullable();
            $table->string('neighborhood')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('complement')->nullable();
            $table->timestamps();
        });

        $embarcador = new Shipper([
            'code' => '164180',
            'legal_name' => 'MIRANTE INDUSTRIA E COMERCIO LTDA',
            'trade_name' => 'LOJAS MIRANTE',
            'tax_id' => '23966188000122',
            'tax_registration_number' => '257853740',
            'zip_code' => '89111-081',
            'address' => 'RUA ANFILOQUIO NUNES PIRES, 5021 - BELA VISTA',
            'number' => '5021',
            'neighborhood' => 'BELA VISTA',
            'city' => 'GASPAR',
            'state' => 'SC',
            'complement' => 'GALPAO'
        ]);
        
        $embarcador->save(); // Isso irá salvar o objeto na tabela de clientes do banco de dados.
      
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shippers');
    }
};
