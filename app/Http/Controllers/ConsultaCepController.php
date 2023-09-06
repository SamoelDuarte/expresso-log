<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use PhpParser\Node\Stmt\TryCatch;

class ConsultaCepController extends Controller
{
    public function consultaCep(Request $request)
    {


        $result['ShippingSevicesArray'][] = $this->calculateShippingDBA($request);
        $result['ShippingSevicesArray'][] = $this->calculateShippingIS($request);

        echo json_encode($result);
    }
    
    public function calculateShippingIs($request)
    {
      
        $apiKey = env('MESH_API_KEY'); // Replace with your actual API key
        $userId = env('MESH_USER_ID');  // Replace with your actual User ID
        $client = new Client();

        // Defina os parâmetros da solicitação
        $weight = $request->get('Weight'); // Peso real total em KG dos produtos
        $densidadePadrao = 0.2; // Densidade padrão em KG por litro (ajuste conforme necessário)

        // Calcule o volume total em litros (assumindo que os produtos têm dimensões específicas)
        $comprimento = 30; // Substitua pelo comprimento real em cm
        $largura = 20; // Substitua pela largura real em cm
        $altura = 10; // Substitua pela altura real em cm

        $volumeLitros = ($comprimento / 100) * ($largura / 100) * ($altura / 100); // Converter cm para metros cúbicos

        // Calcule o peso cúbico
        $volume = $volumeLitros * $densidadePadrao;



        try {

            // Define the parameters in an array
            $params = [
                'cnpjPayer' => '23966188000122',
                'cepOrigin' => '89111094',
                'cepDestiny' => Utils::sanitizeZipcode($request->get('SellerCEP')),
                'valueNF' => number_format($request->get('ShipmentInvoiceValue') / 100, 2, '.', ''),
                'quantity' => '1',
                'weight' => $weight,
                'volume' => $volume,
            ];



            // Create a query string from the parameters
            $queryString = http_build_query($params);

            // Create a new Guzzle client with the API Key and User ID in the header
            $client = new Client([
                'headers' => [
                    'X-Api-Key' => $apiKey,
                    'X-Api-User' => $userId,
                ],
            ]);
            https://apicliente.minha.is/api/v1/Tms/CreateOrder
            // Define the base URL
            $baseUrl = 'https://apicliente.minha.is/api/v1/Tms/quote';

            // Construct the full URL
            $url = $baseUrl . '?' . $queryString;

            // Make a GET request with the API Key and User ID in the header
            $response = $client->get($url);

            // Get the response body as a string
            $responseBody = $response->getBody()->getContents();


            $result = json_decode($responseBody, true);


            $data = [
                "Carrier" =>  "MESH Entrega",
                "CarrierCode" =>  "",
                "DeliveryTime" =>  $result['content']['prazo'],
                "Msg" => "",
                "ServiceCode" => "",
                "ServiceDescription" => "",
                "ShippingPrice" => $result['content']['totalFrete'],
                "OriginalDeliveryTime" => "",
                "OriginalShippingPrice" => "",
                "Error" => false
            ];

            // Do something with the response data
            return $data;
        } catch (RequestException $e) {
            return false;
        }
        // Faça a solicitação POST

    }
    public function calculateShippingDBA($request)
    {

        $client = new Client();

        // Defina os parâmetros da solicitação
        $weight = $request->get('Weight'); // Peso real total em KG dos produtos
        $densidadePadrao = 0.2; // Densidade padrão em KG por litro (ajuste conforme necessário)

        // Calcule o volume total em litros (assumindo que os produtos têm dimensões específicas)
        $comprimento = 30; // Substitua pelo comprimento real em cm
        $largura = 20; // Substitua pela largura real em cm
        $altura = 10; // Substitua pela altura real em cm

        $volumeLitros = ($comprimento / 100) * ($largura / 100) * ($altura / 100); // Converter cm para metros cúbicos

        // Calcule o peso cúbico
        $volume = $volumeLitros * $densidadePadrao;

        // Defina os parâmetros da solicitação
        $params = [
            'form_params' => [
                'apikey' => env('DBA_API_KEY'),
                'local' => 'BR',
                'peso' => $weight,
                'peso_cubado' => $volume,
                'valor' => $request->get('ShipmentInvoiceValue'),
                'cep' => $request->get('SellerCEP'),
            ],
        ];

        try {
            $response = $client->post('https://englobasistemas.com.br/financeiro/api/fretes/calcularFrete', $params);

            // Obtenha a resposta JSON
            $result = json_decode($response->getBody(), true);


            $data = [
                "Carrier" =>  $result['transportadora'],
                "CarrierCode" =>  $result['descricao_servico'],
                "DeliveryTime" =>  $result['prazo'],
                "Msg" => "",
                "ServiceCode" => $result['codigo_regra_calculo_frete'],
                "ServiceDescription" => $result['descricao_servico'],
                "ShippingPrice" => $result['frete'],
                "OriginalDeliveryTime" => $result['prazo'],
                "OriginalShippingPrice" => $result['frete'],
                "Error" => false
            ];



            return $data;
        } catch (RequestException $e) {
            return false;
        }
        // Faça a solicitação POST

    }
}
