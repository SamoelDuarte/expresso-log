<?php

namespace App\Http\Controllers;

use Exception;
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
        $result['ShippingSevicesArray'][] = $this->calculateShippingGlf($request);

        echo json_encode($result);
    }

    public function calculateShippingGlf($request)
    {
        // Crie uma instância do cliente Guzzle
        $clientA = new Client();

        // Defina a URL do endpoint
        $urlA = 'https://grupoastrolog.brudam.com.br/api/v1/acesso/auth/login';

        // Defina os dados que serão enviados no corpo da solicitação
        $data = [
            'usuario' => 'f0ea6a089cfbe2063d1f1b95e32aa744',
            'senha' => '65842d7544889a6b6f6b11aa72fb2826c7d482f2ebde2c777c1658fcaa1fb193',
        ];

        // Faça a solicitação POST
        $response = $clientA->post($urlA, [
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
            'json' => $data, // Os dados são enviados como JSON no corpo da solicitação
        ]);


        $accessKey = null;
        // Verifique se a solicitação foi bem-sucedida
        if ($response->getStatusCode() === 200) {
            $responseData = json_decode($response->getBody(), true); // Decodifique a resposta JSON para um array associativo
            if (isset($responseData['data']['access_key'])) {
                $accessKey = $responseData['data']['access_key'];
                $client = new Client();

                // Dados de solicitação
                $data = [

                    "nDocEmit" => "17000788000139",
                    "nDocCli" => "23966188000122",
                    "nDocRem" => "23966188000122",
                    "nDocDest" => "37785652813",
                    "cOrigCalc" => 3550308,
                    "cDestCalc" => 3550308,
                    "CEP" => Utils::sanitizeZipcode($request->get('SellerCEP')),
                    "cTab" => "FRETE BARATO",
                    "cServ" => "123",
                    "pBru" => 5,
                    "qVol" => 3,
                    "vNF" => number_format($request->get('ShipmentInvoiceValue') / 100, 2, '.', ''),
                    "volumes" => [
                        [
                            "dCom" => 16,
                            "dLar" => 16,
                            "dAlt" => 16,
                            "qVol" => 3,
                            "pBru" => 5
                        ]
                    ]
                ];

                $headers = [
                    "Authorization" => "Bearer  " . $accessKey,
                    "Content-Type" => "application/json",
                    "accept" => "application/json"
                ];


                $response = $client->post("https://grupoastrolog.brudam.com.br/api/v1/frete/cotacao/calcula", [
                    'headers' => $headers,
                    'json' => $data
                ]);

                $body = $response->getBody()->getContents();
                $result = json_decode($body, true);


                $data = [
                    "Carrier" =>  "GLf Entrega",
                    "CarrierCode" =>  "",
                    "DeliveryTime" => $result['data'][0]['nDias'],
                    "Msg" => "",
                    "ServiceCode" => "",
                    "ServiceDescription" => "",
                    "ShippingPrice" => $result['data'][0]['vEntrega'],
                    "OriginalDeliveryTime" => "",
                    "OriginalShippingPrice" => "",
                    "Error" => false
                ];

                // Do something with the response data
                return $data;
            } else {
                echo "A chave 'access_key' não foi encontrada na resposta.\n";
            }
        } else {
            echo "A solicitação não foi bem-sucedida.\n";
        }
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
            https: //apicliente.minha.is/api/v1/Tms/CreateOrder
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
