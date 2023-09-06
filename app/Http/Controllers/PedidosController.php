<?php

namespace App\Http\Controllers;

use App\Models\Carrier;
use App\Models\Embarcador;
use App\Models\Entrega;
use App\Models\StatusHistory;
use App\Models\Cerrier;
use App\Models\Delivery;
use App\Models\Error;
use App\Models\Shipper;
use DateTime;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Promise\Create;
use Illuminate\Http\Request;

class PedidosController extends Controller
{
    public function gerarPedido(Request $request)
    {

        $xmlContent = $request->getContent();
        $xmlObject = simplexml_load_string($xmlContent); // Transformar o XML em um objeto SimpleXMLElement
        $jsonString = json_encode($xmlObject); // Transformar o objeto em uma string JSON
        $XmlArray = json_decode($jsonString, true);
        $documento = $XmlArray['NFe']['infNFe']['transp']['transporta']['CNPJ'];

        switch ($documento) {
                // tranportadora DBA
            case "50160966000164":
                $this->gerarPedidoDBA($xmlContent);
                break;

            case "37744796000105":
                $this->gerarPedidoMesh($xmlContent);
                break;
            default:
                Error::create(['erro' => 'Transportadora não Integrada']);
        }
    }

    public function gerarPedidoDBA($xmlContent)
    {
        $xmlObject = simplexml_load_string($xmlContent); // Transformar o XML em um objeto SimpleXMLElement
        $jsonString = json_encode($xmlObject); // Transformar o objeto em uma string JSON

        $XmlArray = json_decode($jsonString, true);
        $documento = $XmlArray['NFe']['infNFe']['transp']['transporta']['CNPJ'];
        // URL da API
        $url = 'https://englobasistemas.com.br/arquivos/api/GerarPedido/Gerar';

        // Chave da API
        $apiKey = 'd1Zqb0Yvb0w1S0JDc0JycmpXSlJuZVFEWFFJVnk2U25iQTB0c0NOVk1mc202QXVyZ21VRk1UTUsrRGtiYjZNamtEOU1raGRiZHozYjFETnhyeXUxRXRlMUhqNmtmMVlndTFRM1ZubEs4L2c5NGMvRFRlSWV3TGtBRzkxNi9QT3ZIS0RWeWtvaDJDSThUQzlmcDh4TGFjZG94MHhLWlY0ZERmNkoxcHNuNThDdENYSzEwckNoRW0wTVVzcUpYY2dybnN0NFpkUnRGcldkRU5iSUFGYkFJdFc4cElRWkxFVUdDd2QrV0M1UWo5OWlnTXA2K05PeEcvdDlTamVHWXVGblBnNy9WVE83Wjg0bUJ6QVJLMCtCdHUvcDN2VVBWbTQvZ096TFNvTEM1TmFSY3BQWGtNbUwvTFdRTWIvQ0lVR0Jxei9SaFJkNG9Yb2MxelBMQXBORmRFdXNGUElDVzJkN1JkekQ2NVNNOXRaZVRZWCtCTWh1eFh1V1BwU1cvYURBSGVQN0RzMWhUMEhyVmp5dUNWaWhCZz09';



        // Criar uma instância do cliente Guzzle
        $client = new Client();

        // Fazer a solicitação POST
        $response = $client->post($url, [
            'headers' => [
                'apikey' => $apiKey,
                'Content-Type' => 'text/xml',
            ],
            'body' => $xmlContent,
        ]);


        // Converter o corpo em uma string
        $responseData = $response->getBody()->getContents();
        $respostaXML = simplexml_load_string($responseData);
        $jsonData = json_encode($respostaXML);
        $arrayData = json_decode($jsonData, true);



        if ($arrayData[0] == "true") {
            $transp = Carrier::whereHas('documents', function ($query) use ($documento) {
                $query->where('number', $documento);
            })->first();




            $numNota = $XmlArray['NFe']['infNFe']['ide']['nNF'];
            $ufUnidadeDestino = $transp->estado;
            $qtdVolume = $XmlArray['NFe']['infNFe']['transp']['vol']['qVol'];
            $numeroDoVolume = $XmlArray['NFe']['infNFe']['transp']['vol']['nVol'];
            $peso = $XmlArray['NFe']['infNFe']['transp']['vol']['pesoL'];
            $totalPeso = $XmlArray['NFe']['infNFe']['transp']['vol']['pesoB'];
            $chaveNf = $XmlArray['protNFe']['infProt']['chNFe'];
            $destNome = $XmlArray['NFe']['infNFe']['dest']['xNome'];
            $destCpfCnpj = $XmlArray['NFe']['infNFe']['dest']['CPF'];
            $destTelefone = $XmlArray['NFe']['infNFe']['dest']['enderDest']['fone'];
            $destEmail = $XmlArray['NFe']['infNFe']['dest']['email'];
            $destCep = $XmlArray['NFe']['infNFe']['dest']['enderDest']['CEP'];
            $destLogradouro = $XmlArray['NFe']['infNFe']['dest']['enderDest']['xLgr'];
            $destNumero = $XmlArray['NFe']['infNFe']['dest']['enderDest']['nro'];
            $destBairro = $XmlArray['NFe']['infNFe']['dest']['enderDest']['xBairro'];
            $destCidade = $XmlArray['NFe']['infNFe']['dest']['enderDest']['xMun'];
            $destEstado = $XmlArray['NFe']['infNFe']['dest']['enderDest']['UF'];



            $embarcador = Shipper::first();
            $delivery = new Delivery();

            $delivery->carrier_id = $transp->id; // Replace with the actual carrier ID
            $delivery->shipper_id = $embarcador->id; // Replace with the actual shipper ID
            $delivery->parcel = Utils::createTwoFactorCode();
            $delivery->received = '2023-08-28';
            $delivery->scheduled = '2023-08-29';
            $delivery->estimated_delivery = '2023-08-30';
            $delivery->invoice = $numNota;
            $delivery->destination_state = $ufUnidadeDestino;
            $delivery->quantity_of_packages = $qtdVolume;
            $delivery->invoice_key = $chaveNf;
            $delivery->package_number = $numeroDoVolume;
            $delivery->weight = $peso;
            $delivery->total_weight = $totalPeso;
            $delivery->destination_name = $destNome;
            $delivery->destination_tax_id = $destCpfCnpj;
            $delivery->destination_phone = $destTelefone;
            $delivery->destination_email = $destEmail;
            $delivery->destination_zip_code = $destCep;
            $delivery->destination_address = $destLogradouro;
            $delivery->destination_number = $destNumero;
            $delivery->destination_neighborhood = $destBairro;
            $delivery->destination_city = $destCidade;
            $delivery->destination_state = $destEstado;


            try {
                $delivery->save();
                $status = new StatusHistory();
                $status->status = "Arquivo Recebido";
                $status->delivery_id = $delivery->id;
                $status->save();
            } catch (Exception $e) {
                // Verifique se a mensagem de erro contém "SQLSTATE[23000]" (case-sensitive)
                if (strpos($e->getMessage(), "SQLSTATE[23000]") !== false) {
                    Error::create(['erro' => 'Nota já processada']);
                    exit;
                }
            }
        }
    }
    public function gerarPedidoMesh($xmlContent)
    {
        $xmlObject = simplexml_load_string($xmlContent); // Transformar o XML em um objeto SimpleXMLElement
        $jsonString = json_encode($xmlObject); // Transformar o objeto em uma string JSON

        $XmlArray = json_decode($jsonString, true);
        $documento = $XmlArray['NFe']['infNFe']['transp']['transporta']['CNPJ'];

        $transp = Carrier::whereHas('documents', function ($query) use ($documento) {
            $query->where('number', $documento);
        })->first();



        $dateTime = new DateTime();
        $numNota = $XmlArray['NFe']['infNFe']['ide']['nNF'];
        $ufUnidadeDestino = $transp->estado;
        $qtdVolume = $XmlArray['NFe']['infNFe']['transp']['vol']['qVol'];
        $numeroDoVolume = $XmlArray['NFe']['infNFe']['transp']['vol']['nVol'];
        $peso = $XmlArray['NFe']['infNFe']['transp']['vol']['pesoL'];
        $totalPeso = $XmlArray['NFe']['infNFe']['transp']['vol']['pesoB'];
        $chaveNf = $XmlArray['protNFe']['infProt']['chNFe'];
        $destNome = $XmlArray['NFe']['infNFe']['dest']['xNome'];
        $destCpfCnpj = $XmlArray['NFe']['infNFe']['dest']['CPF'];
        $destTelefone = $XmlArray['NFe']['infNFe']['dest']['enderDest']['fone'];
        $destEmail = $XmlArray['NFe']['infNFe']['dest']['email'];
        $destCep = $XmlArray['NFe']['infNFe']['dest']['enderDest']['CEP'];
        $destLogradouro = $XmlArray['NFe']['infNFe']['dest']['enderDest']['xLgr'];
        $destNumero = $XmlArray['NFe']['infNFe']['dest']['enderDest']['nro'];
        $destBairro = $XmlArray['NFe']['infNFe']['dest']['enderDest']['xBairro'];
        $destCidade = $XmlArray['NFe']['infNFe']['dest']['enderDest']['xMun'];
        $destEstado = $XmlArray['NFe']['infNFe']['dest']['enderDest']['UF'];
        $valorTotal = $XmlArray['NFe']['infNFe']['total']['ICMSTot']['vNF'];

     

        $shipper = Shipper::first();
        $produtos = [];
        foreach ($XmlArray['NFe']['infNFe']['det'] as $detalhe) {
            $produto = [
                'cProd' => $detalhe['prod']['cProd'],
                'xProd' => $detalhe['prod']['xProd'],
                'NCM' => $detalhe['prod']['NCM'],
                'CFOP' => $detalhe['prod']['CFOP'],
                'uCom' => $detalhe['prod']['uCom'],
                'qCom' => $detalhe['prod']['qCom'],
                'vUnCom' => $detalhe['prod']['vUnCom'],
                'vProd' => $detalhe['prod']['vProd'],
                'vFrete' => $detalhe['prod']['vFrete'],
            ];
            $produtos[] = $produto;
        }

        $data = [
            "keyNFe" => "35210317770708000124550000000000101000000002",
            "dateIssueAt" => "2023-08-28T20:59:23.983Z",
            "merchandisePrice" => 159.9,
            "volumeQuantity" => 1,
            "client" => [
                "document" => '23966188000122',
                "name" => $shipper->legal_name,
                "address" => $shipper->street,
                "addressNumber" => $shipper->number,
                "addressComplement" => $shipper->complement,
                "addressNeighborhood" => $shipper->neighborhood,
                "addressCityName" => $shipper->city,
                "addressStateAcronym" => $shipper->state,
                "addressZipCode" => $shipper->zip_code,
                "phone" => "",
                "cellphone" => "",
                "email" => "",
                "businessName" => $shipper->trade_name,
                "registrationStateNumber" => "",
                "addressLatitude" => 0,
                "addressLongitude" => 0,
            ],//37744796000105
            "sender" => [
                "document" => '23966188000122',
                "name" => $shipper->legal_name,
                "address" => $shipper->street,
                "addressNumber" => $shipper->number,
                "addressComplement" => $shipper->complement,
                "addressNeighborhood" => $shipper->neighborhood,
                "addressCityName" => $shipper->city,
                "addressStateAcronym" => $shipper->state,
                "addressZipCode" => $shipper->zip_code,
                "phone" => "",
                "cellphone" => "",
                "email" => "",
                "businessName" => $shipper->trade_name,
                "registrationStateNumber" => "",
                "addressLatitude" => 0,
                "addressLongitude" => 0,
            ],
            "recipient" => [
                "document" => $destCpfCnpj,
                "name" => $destNome,
                "address" => $destLogradouro,
                "addressNumber" => $destNumero,
                "addressComplement" => "",
                "addressNeighborhood" => $destBairro,
                "addressCityName" => $destCidade,
                "addressStateAcronym" => $destEstado,
                "addressZipCode" => $destCep,
                "phone" => $destTelefone,
                "cellphone" => "",
                "email" => $destEmail,
                "businessName" => "",
                "registrationStateNumber" => "",
                "addressLatitude" => 0,
                "addressLongitude" => 0
            ],
            "merchandiseList" => [
                [
                    "code" => $produtos[0]['cProd'],
                    "description" => $produtos[0]['xProd'],
                    "quantity" => 1,
                    "price" => intVal($valorTotal),
                    "length" => 1,
                    "width" => 1,
                    "height" => 1,
                    "weight" => 1,
                    "cubicWeight" => 0
                ]
            ],
            "isTest" => false,
            "pudoTypeId" => 0
        ];

        $embarcador = Shipper::first();
        $delivery = new Delivery();

        $delivery->carrier_id = $transp->id; // Replace with the actual carrier ID
        $delivery->shipper_id = $embarcador->id; // Replace with the actual shipper ID
        $delivery->parcel = Utils::createTwoFactorCode();
        $delivery->received = '2023-08-28';
        $delivery->scheduled = '2023-08-29';
        $delivery->estimated_delivery = '2023-08-30';
        $delivery->invoice = $numNota;
        $delivery->destination_state = $ufUnidadeDestino;
        $delivery->quantity_of_packages = $qtdVolume;
        $delivery->invoice_key = $chaveNf;
        $delivery->package_number = $numeroDoVolume;
        $delivery->weight = $peso;
        $delivery->total_weight = $totalPeso;
        $delivery->destination_name = $destNome;
        $delivery->destination_tax_id = $destCpfCnpj;
        $delivery->destination_phone = $destTelefone;
        $delivery->destination_email = $destEmail;
        $delivery->destination_zip_code = $destCep;
        $delivery->destination_address = $destLogradouro;
        $delivery->destination_number = $destNumero;
        $delivery->destination_neighborhood = $destBairro;
        $delivery->destination_city = $destCidade;
        $delivery->destination_state = $destEstado;


        try {
            $delivery->save();
            $status = new StatusHistory();
            $status->status = "Arquivo Recebido";
            $status->delivery_id = $delivery->id;
            $status->save();
        } catch (Exception $e) {
            // Verifique se a mensagem de erro contém "SQLSTATE[23000]" (case-sensitive)
            if (strpos($e->getMessage(), "SQLSTATE[23000]") !== false) {
                Error::create(['erro' => 'Nota já processada']);
                exit;
            }
        }

        


        // URL do endpoint
        $endpoint = 'https://apicliente.minha.is/api/v1/Tms/CreateOrder';

        // Chave da API e ID do usuário

        $apiKey = env('MESH_API_KEY'); // Replace with your actual API key
        $userId = env('MESH_USER_ID');  // Replace with your actual User ID

        // Crie um cliente Guzzle
        $client = new Client([
            'headers' => [
                'X-Api-Key' => $apiKey,
                'X-Api-User' => $userId,
            ],
        ]);

        // Faça uma solicitação POST com os dados
        $response = $client->post($endpoint, [
            'json' => $data,
        ]);

        // Obtenha a resposta
        $responseData = $response->getBody()->getContents();

        // Faça o que você precisa com a resposta (por exemplo, imprimir ou processar)
        echo $responseData;
    }
}
