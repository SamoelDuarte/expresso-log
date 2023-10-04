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

        $carrier = new Carrier([
            'legal_name' => 'DBA EXPRESS LOGISTICS AND TRANSPORT LTD',
            'trade_name' => 'DBA EXPRESS',
            'state_registration' => 'EXEMPT',
            'phone' => 'your_phone',
            'rntrc' => 'your_rntrc',
            'opted_for_simples_nacional' => false,
            'zip_code' => '07430350',
            'address' => 'AVENIDA TOWER AUTOMOTIVE, 701 - LARANJA AZEDA',
            'number' => '701',
            'complement' => 'your_complement',
            'neighborhood' => 'LARANJA AZEDA',
            'city' => 'ARUJÁ',
            'state' => 'SP',
            'landmark' => 'your_landmark',
            'contact_name' => 'contact_name',
            'contact_phone1' => 'contact_phone_1',
            'contact_phone2' => 'contact_phone_2',
            'contact_email' => 'contact_email',
            'customer_service_phone' => 'customer_service_phone',
            'customer_service_email' => 'customer_service_email',
            'website' => 'company_website',
            'title' => 'your_title',
            'message' => 'your_message',
        ]);
        
        $carrier->save();
        
        $document = new CarrierDocument();
        $document->carrier_id = $carrier->id;
        $document->number = "50160966000164";
        $document->save();
        
        $document = new CarrierDocument();
        $document->carrier_id = $carrier->id;
        $document->number = "08982220000170";
        $document->save();


        $carrier1 = new Carrier([
            'legal_name' => 'MESH TRANSPORTES E LOGISTICA LTDA',
            'trade_name' => 'MESH TRANSPORTES E LOGISTICA LTDA',
            'state_registration' => 'EXEMPT',
            'phone' => '47992912222',
            'rntrc' => 'your_rntrc',
            'opted_for_simples_nacional' => false,
            'zip_code' => '06186260',
            'address' => 'Avenida Leonil Crê Bortolosso',
            'number' => '945',
            'complement' => 'your_complement',
            'neighborhood' => 'Quitaúna',
            'city' => 'Osasco',
            'state' => 'SP',
            'landmark' => 'your_landmark',
            'contact_name' => 'Mesh',
            'contact_phone1' => '(47) 99291-2222',
            'contact_phone2' => 'contact_phone_2',
            'contact_email' => 'contact_email',
            'customer_service_phone' => 'customer_service_phone',
            'customer_service_email' => 'customer_service_email',
            'website' => 'company_website',
            'title' => 'your_title',
            'message' => 'your_message',
        ]);
        
        $carrier1->save();
        
        $document = new CarrierDocument();
        $document->carrier_id = $carrier1->id;
        $document->number = "37744796000105";
        $document->save();


        $carrier2 = new Carrier([
            'legal_name' => 'ASTROLOG SERVICOS DE TRANSPORTE LTDA',
            'trade_name' => 'ASTROLOG SERVICOS DE TRANSPORTE LTDA',
            'state_registration' => 'EXEMPT',
            'phone' => '4799291-2222',
            'rntrc' => 'your_rntrc',
            'opted_for_simples_nacional' => false,
            'zip_code' => '04180075',
            'address' => 'Travessa Ernesto Portante',
            'number' => '55',
            'complement' => 'your_complement',
            'neighborhood' => 'Jardim Maria Estela',
            'city' => 'São Paulo',
            'state' => 'SP',
            'landmark' => 'your_landmark',
            'contact_name' => 'Astralog',
            'contact_phone1' => '(47) 99291-2222',
            'contact_phone2' => 'contact_phone_2',
            'contact_email' => 'astrolog@fretebarato.com',
            'customer_service_phone' => 'customer_service_phone',
            'customer_service_email' => 'customer_service_email',
            'website' => 'company_website',
            'title' => 'your_title',
            'message' => 'your_message',
        ]);
        
        $carrier2->save();
        
        $document = new CarrierDocument();
        $document->carrier_id = $carrier2->id;
        $document->number = "20588287000120";
        $document->save();
        $document = new CarrierDocument();
        $document->carrier_id = $carrier2->id;
        $document->number = "17000788000139";
        $document->save();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carrier_documents');
    }
};
