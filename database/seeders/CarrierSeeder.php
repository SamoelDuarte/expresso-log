<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Carrier;
use App\Models\CarrierDocument;

class CarrierSeeder extends Seeder
{
    /**
     * Run the seeder.
     */
    public function run()
    {
        // $carrier = new Carrier([
        //     'legal_name' => 'DBA EXPRESS LOGISTICS AND TRANSPORT LTD',
        //     'trade_name' => 'DBA EXPRESS',
        //     'state_registration' => 'EXEMPT',
        //     'phone' => 'your_phone',
        //     'rntrc' => 'your_rntrc',
        //     'opted_for_simples_nacional' => false,
        //     'zip_code' => '07430350',
        //     'address' => 'AVENIDA TOWER AUTOMOTIVE, 701 - LARANJA AZEDA',
        //     'number' => '701',
        //     'complement' => 'your_complement',
        //     'neighborhood' => 'LARANJA AZEDA',
        //     'city' => 'ARUJÁ',
        //     'state' => 'SP',
        //     'landmark' => 'your_landmark',
        //     'contact_name' => 'contact_name',
        //     'contact_phone1' => 'contact_phone_1',
        //     'contact_phone2' => 'contact_phone_2',
        //     'contact_email' => 'contact_email',
        //     'customer_service_phone' => 'customer_service_phone',
        //     'customer_service_email' => 'customer_service_email',
        //     'website' => 'company_website',
        //     'title' => 'your_title',
        //     'message' => 'your_message',
        // ]);

        // $carrier->save();

        // $document = new CarrierDocument();
        // $document->carrier_id = $carrier->id;
        // $document->number = "50160966000164";
        // $document->save();

        // $document = new CarrierDocument();
        // $document->carrier_id = $carrier->id;
        // $document->number = "08982220000170";
        // $document->save();

        // Repeat the same process for other carriers and documents.

        // $carrier1 = Carrier::create([
        //     'legal_name' => 'MESH TRANSPORTES E LOGISTICA LTDA',
        //     'trade_name' => 'MESH TRANSPORTES E LOGISTICA LTDA',
        //     'state_registration' => 'EXEMPT',
        //     'phone' => '47992912222',
        //     'rntrc' => 'your_rntrc',
        //     'opted_for_simples_nacional' => false,
        //     'zip_code' => '06186260',
        //     'address' => 'Avenida Leonil Crê Bortolosso',
        //     'number' => '945',
        //     'complement' => 'your_complement',
        //     'neighborhood' => 'Quitaúna',
        //     'city' => 'Osasco',
        //     'state' => 'SP',
        //     'landmark' => 'your_landmark',
        //     'contact_name' => 'Mesh',
        //     'contact_phone1' => '(47) 99291-2222',
        //     'contact_phone2' => 'contact_phone_2',
        //     'contact_email' => 'contact_email',
        //     'customer_service_phone' => 'customer_service_phone',
        //     'customer_service_email' => 'customer_service_email',
        //     'website' => 'company_website',
        //     'title' => 'your_title',
        //     'message' => 'your_message',
        // ]);
        
        // $carrier1->documents()->create([
        //     'number' => '37744796000105',
        // ]);

        // $carrier3 = Carrier::create([
        //     'legal_name' => 'ASTROLOG SERVICOS DE TRANSPORTE LTDA',
        //     'trade_name' => 'ASTROLOG SERVICOS DE TRANSPORTE LTDA',
        //     'state_registration' => 'EXEMPT',
        //     'phone' => '4799291-2222',
        //     'rntrc' => 'your_rntrc',
        //     'opted_for_simples_nacional' => false,
        //     'zip_code' => '04180075',
        //     'address' => 'Travessa Ernesto Portante',
        //     'number' => '55',
        //     'complement' => 'your_complement',
        //     'neighborhood' => 'Jardim Maria Estela',
        //     'city' => 'São Paulo',
        //     'state' => 'SP',
        //     'landmark' => 'your_landmark',
        //     'contact_name' => 'Astralog',
        //     'contact_phone1' => '(47) 99291-2222',
        //     'contact_phone2' => 'contact_phone_2',
        //     'contact_email' => 'astrolog@fretebarato.com',
        //     'customer_service_phone' => 'customer_service_phone',
        //     'customer_service_email' => 'customer_service_email',
        //     'website' => 'company_website',
        //     'title' => 'your_title',
        //     'message' => 'your_message',
        // ]);

        // $carrier3->documents()->createMany([
        //     ['number' => '20588287000120'],
        //     ['number' => '17000788000139'],
        // ]);



        $carrier4 = Carrier::create([
            'legal_name' => 'J&T EXPRESS BRAZIL LTDA.',
            'trade_name' => 'J&T EXPRESS',
            'state_registration' => '123456',
            'phone' => '4733974281',
            'rntrc' => 'your_rntrc',
            'opted_for_simples_nacional' => false,
            'zip_code' => '89111081',
            'address' => 'RUA ANFILOQUIO NUNES PIRES',
            'number' => '5021',
            'complement' => 'your_complement',
            'neighborhood' => 'BELA VISTA',
            'city' => 'GASPAR',
            'state' => 'SC',
            'landmark' => 'your_landmark',
            'contact_name' => 'J&T EXPRESS',
            'contact_phone1' => '4733974281',
            'contact_phone2' => 'contact_phone_2',
            'contact_email' => 'jtexpress@example.com',
            'customer_service_phone' => 'customer_service_phone',
            'customer_service_email' => 'customer_service_email',
            'website' => 'www.jtexpress.com.br',
            'title' => 'your_title',
            'message' => 'your_message',
        ]);
        
        $carrier4->documents()->createMany([
            ['number' => '42584754001077'],
        ]);

        $carrier5 = Carrier::create([
            'legal_name' => 'GFL LOGISTICA LTDA',
            'trade_name' => 'GFL LOGISTICA',
            'state_registration' => '261527584',
            'phone' => '4733974281',
            'rntrc' => 'your_rntrc',
            'opted_for_simples_nacional' => false,
            'zip_code' => '88310-000',
            'address' => 'R DOMINGOS RAMPELOTTI, 3501',
            'number' => '',
            'complement' => '',
            'neighborhood' => '',
            'city' => 'ITAJAI',
            'state' => 'SC',
            'landmark' => '',
            'contact_name' => 'GFL LOGISTICA LTDA',
            'contact_phone1' => '4733974281',
            'contact_phone2' => '',
            'contact_email' => 'gfllogistica@example.com',
            'customer_service_phone' => '',
            'customer_service_email' => '',
            'website' => '',
            'title' => '',
            'message' => '',
        ]);
        
        $carrier5->documents()->createMany([
            ['number' => '23820639001352'], // CNPJ do transportador
        ]);
        

        

    }
}
