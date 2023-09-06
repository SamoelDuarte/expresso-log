<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ConsultaCepController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PedidosController;
use App\Http\Controllers\CarrierController;
use App\Models\Entrega;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TokenController;
use App\Models\Delivery;
use App\Models\StatusHistory;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/



// toda rota tem que está autenticada com
Route::middleware('auth.token')->group(function () {

    Route::prefix('/gerarPedido')->controller(PedidosController::class)->group(function () {
        Route::post('/', 'gerarPedido');
    });

    Route::prefix('/consulta-cep')->controller(ConsultaCepController::class)->group(function () {
        Route::post('/', 'consultaCep');
    });
});


Route::prefix('/admin')->controller(AdminController::class)->group(function () {
    Route::get('/login', 'login')->name('admin.login');
    Route::get('/sair', 'sair')->name('admin.sair');
    Route::get('/senha', 'password')->name('admin.password');
    Route::post('/attempt', 'attempt')->name('admin.attempt');
});

Route::prefix('/')->controller(AdminController::class)->group(function () {
    Route::get('/', 'login');
});




Route::middleware('auth.admin')->group(function () {


    Route::prefix('/home')->controller(HomeController::class)->group(function () {
        Route::get('/', 'index')->name('admin.dashboard');
        Route::get('/errors/filter', 'filter');
        Route::get('/status/filter', 'filterStatus');
    });

    Route::prefix('/transportadora')->controller(CarrierController::class)->group(function () {
        Route::get('/', 'index')->name('admin.transp.index');
        Route::get('/novo', 'create')->name('admin.transp.create');
        Route::post('/store', 'store')->name('admin.transp.store');
    });

    Route::prefix('/entregas')->controller(DeliveryController::class)->group(function () {
        Route::get('/', 'index')->name('admin.entrega.index');
        Route::get('/getEntregas', 'getEntregas');
    });
});

Route::get('/cunsultaSim', function () {
    // URL do endpoint
    $endpointUrl = 'http://gflapi.sinclog.app.br/api/ocorrencias/volume/consulta';

    // Chave de acesso fornecida pelo transportador
    $chaveAcesso = 'xP7aTUnqZe';

    // Dados da consulta no formato JSON
    $consultaJson = '{
    "cnpjEmbarcador": "23.966.188/0001-22",
    "dtInicioBusca": "2023-08-01",
    "dtFimBusca": "2023-08-15"
        }';

    try {
        // Criação de uma instância do cliente Guzzle
        $client = new Client();

        // Fazendo a requisição POST com o cabeçalho de autorização e os dados da consulta
        $response = $client->post($endpointUrl, [
            'headers' => [
                'Authorization' => 'Basic ' . $chaveAcesso,
                'Content-Type' => 'application/json',
            ],
            'body' => $consultaJson,
        ]);

        // Obtendo o corpo da resposta como string
        $responseBody = $response->getBody()->getContents();

        // Imprimindo a resposta
        echo $responseBody;
    } catch (Exception $e) {
        echo "Ocorreu um erro: " . $e->getMessage();
    }
});

Route::get('/createSim', function () {
    // URL do endpoint
    $endpointUrl = 'http://gflapi.sinclog.app.br/Api/Solicitacoes/RegistrarNovaSolicitacao';

    // Chave de acesso fornecida pelo transportador
    $chaveAcesso = 'xP7aTUnqZe';


    $solicitacaoJson = '{
        "cnpjEmbarcadorOrigem": "23.966.188/0001-22",
        "listaSolicitacoes": [
            {
                "idSolicitacaoInterno": "5433454345",
                "idServico": 4,
                "Destinatario": {
                    "cpf": "37785652813",
                    "Endereco": {
                        "cep": "12283870",
                        "logradouro": "RUA LUIZ DE CARVALHO GONCALVES",
                        "numero": "195",
                        "bairro": "PARQUE RESIDENCIAL S",
                        "nomeCidade": "CACAPAVA ",
                        "siglaEstado": "SP"
                    }
                },
                "listaOperacoes": [
                    {
                        "idTipoDocumento": 55,
                        "nroNotaFiscal": 209835 ,
                        "serieNotaFiscal": 1,
                        "qtdeVolumes": 1
                    }
                ]
            }
        ]
    }';

    try {
        // Criação de uma instância do cliente Guzzle
        $client = new Client();

        // Fazendo a requisição POST com o cabeçalho de autorização e os dados da solicitação
        $response = $client->post($endpointUrl, [
            'headers' => [
                'Authorization' => 'Basic ' . $chaveAcesso,
                'Content-Type' => 'application/json',
            ],
            'body' => $solicitacaoJson,
        ]);

        // Obtendo o corpo da resposta como string
        $responseBody = $response->getBody()->getContents();

        // Imprimindo a resposta
        echo $responseBody;
    } catch (Exception $e) {
        echo "Ocorreu um erro: " . $e->getMessage();
    }
});

Route::get('/getSim', function () {
    // Código do item a ser substituído na URL
    $codigoItem = $_GET['cod'];



    // URL base do endpoint
    $baseUrl = 'https://gflapi.sinclog.app.br/api/solicitacoes/etiquetas/';

    // Criação de uma instância do cliente Guzzle
    $client = new Client();

    // Montagem da URL completa com o código do item
    $url = $baseUrl . $codigoItem;


    try {
        // Fazendo a requisição GET com o cabeçalho de autorização
        $response = $client->get($url, [
            'headers' => [
                'Authorization' => 'Basic ' . env('DBA_KEY_ACESS'),
            ],
        ]);

        // Obtendo o corpo da resposta como string
        $responseBody = $response->getBody()->getContents();

        // Imprimindo a resposta
        echo $responseBody;
    } catch (Exception $e) {
        echo "Ocorreu um erro: " . $e->getMessage();
    }
});


Route::get('/updateStatusDBA', function () {

    $numbersToSearch = ['08982220000170', '50160966000164'];

    $deliveryes = Delivery::with('carriers.documents')
        ->whereHas('carriers', function ($query) use ($numbersToSearch) {
            $query->whereHas('documents', function ($documentQuery) use ($numbersToSearch) {
                $documentQuery->whereIn('number', $numbersToSearch);
            });
        })
        ->whereDoesntHave('status', function ($query) {
            $query->where('status', 'finalizado');
        })
        ->orderBy('id')
        ->get();




    foreach ($deliveryes as $key => $value) {

        // Chave da API
        $apiKey = env('DBA_API_KEY');


        // Chave da NF-e
        $chaveNfe = $value->invoice_key;

        $client = new Client();

        $uri = new Uri("https://englobasistemas.com.br/arquivos/api/PegarOcorrencias/RastreamentoChaveNfe");
        $uri = Uri::withQueryValue($uri, 'apikey', $apiKey);
        $uri = Uri::withQueryValue($uri, 'chaveNfe', $chaveNfe);

        try {
            $response = $client->request('POST', $uri);

            $responseArray = json_decode($response->getBody()->getContents(), true);
            $occurrences =  $responseArray[1];


            foreach ($occurrences as $key => $occurrence) {


                $codigo = $occurrence['codigo'];

                // Verifique se o código já existe na tabela status_history.
                $existeRegistro = StatusHistory::where('external_code', $codigo)->exists();

                if (!$existeRegistro) {
                    // O código não existe, então você pode inserir o registro.
                    StatusHistory::create([
                        'delivery_id' => $value->id,
                        'external_code' => $codigo,
                        'status' => $occurrence['ocorrencia'],
                        'observation' => $occurrence['obs'],
                    ]);
                }
            }
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
        }
    }
});

Route::get('/teste', function () {


    $apiKey = 'qbdksvjghmgfqjmkjmwmsfkkjdfaigpxaniqssegbddenydefy'; // Replace with your actual API key
    $userId = '37774';   // Replace with your actual User ID

    // Define the parameters in an array
    $params = [
        'cnpjPayer' => '23966188000122',
        'cepOrigin' => '89111094',
        'cepDestiny' => '03295000',
        'valueNF' => '159.9',
        'quantity' => '1',
        'weight' => '0.65',
        'volume' => '0.0054',
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

    // Define the base URL
    $baseUrl = 'https://apicliente.minha.is/api/v1/Tms/quote';

    // Construct the full URL
    $url = $baseUrl . '?' . $queryString;

    // Make a GET request with the API Key and User ID in the header
    $response = $client->get($url);

    // Get the response body as a string
    $responseBody = $response->getBody()->getContents();

    echo $responseBody;
    // You can then parse the response JSON if needed
    // $data = json_decode($responseBody, true);

    // // Do something with the response data
    // var_dump($data);
});


Route::get('/getMesh', function () {


    $apiKey = 'qbdksvjghmgfqjmkjmwmsfkkjdfaigpxaniqssegbddenydefy'; // Replace with your actual API key
    $userId = '37774';   // Replace with your actual User ID

    // Define the parameters in an array
    $params = [
        'SearchText' => '42230823966188000122550010002125621132542561',
        'DeliveryZipCode' => '07072010',
        'SearchTextEnum' => 4,
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

    // Define the base URL
    $baseUrl = 'https://apicliente.minha.is/api/v1/Tms/GetTrackingInfo';

    // Construct the full URL
    $url = $baseUrl . '?' . $queryString;

    // Make a GET request with the API Key and User ID in the header
    $response = $client->get($url);

    // Get the response body as a string
    $responseBody = $response->getBody()->getContents();

    echo $responseBody;
    // You can then parse the response JSON if needed
    // $data = json_decode($responseBody, true);

    // // Do something with the response data
    // var_dump($data);
});

Route::get('/updateStatusMesh', function () {

    $deliveryes = Delivery::with('carriers.documents')
        ->whereHas('carriers', function ($query) {
            $query->whereHas('documents', function ($documentQuery) {
                $documentQuery->where('number', '37744796000105');
            });
        })
        ->whereDoesntHave('status', function ($query) {
            $query->where('status', 'finalizado');
        })
        ->orderBy('id')
        ->get();




    foreach ($deliveryes as $key => $value) {
        $apiKey = env('MESH_API_KEY'); // Replace with your actual API key
        $userId = env('MESH_USER_ID');  // Replace with your actual User ID

        // Define the parameters in an array
        $params = [
            'SearchText' => $value->invoice_key,
            'DeliveryZipCode' => '07072010',
            'SearchTextEnum' => 4,
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

        // Define the base URL
        $baseUrl = 'https://apicliente.minha.is/api/v1/Tms/GetTrackingInfo';

        // Construct the full URL
        $url = $baseUrl . '?' . $queryString;

        // Make a GET request with the API Key and User ID in the header
        $response = $client->get($url);

        // Get the response body as a string
        $responseBody = $response->getBody()->getContents();

        $responseArray = json_decode($responseBody, true);




        $occurrences = $responseArray['content']['result']['occurrences'];

        foreach ($occurrences as $key => $occurrence) {


            $codigo = $occurrence['id'];

            // Verifique se o código já existe na tabela status_history.
            $existeRegistro = StatusHistory::where('external_code', $codigo)->exists();

            if (!$existeRegistro) {
                // O código não existe, então você pode inserir o registro.
                StatusHistory::create([
                    'delivery_id' => $value->id,
                    'external_code' => $codigo,
                    'status' => $occurrence['occurrenceStatusCategoryTypeName'],
                    'observation' => $occurrence['observation'],
                    'detail' => $occurrence['nameProtocolToUser'],
                ]);
            }
        }
    }
});

Route::get('/tokenGLF', function () {
    // Crie uma instância do cliente Guzzle
    $client = new Client();

    // Defina a URL do endpoint
    $url = 'https://grupoastrolog.brudam.com.br/api/v1/acesso/auth/login';

    // Defina os dados que serão enviados no corpo da solicitação
    $data = [
        'usuario' => 'f0ea6a089cfbe2063d1f1b95e32aa744',
        'senha' => '65842d7544889a6b6f6b11aa72fb2826c7d482f2ebde2c777c1658fcaa1fb193',
    ];

    // Faça a solicitação POST
    $response = $client->post($url, [
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
            echo "Access Key: $accessKey\n";
        } else {
            echo "A chave 'access_key' não foi encontrada na resposta.\n";
        }
    } else {
        echo "A solicitação não foi bem-sucedida.\n";
    }
});
Route::get('/glf', function () {


    $client = new Client();

    // Dados de solicitação
    $data = [

        "nDocEmit" => "23966188000122",
        "nDocCli" => "37785652813",
        "nDocRem" => "",
        "nDocDest" => "",
        "cOrigCalc" => 3550308,
        "cDestCalc" => 3550308,
        "CEP" => "04941080",
        "cTab" => "string",
        "cServ" => "string",
        "pBru" => 0,
        "pCub" => 0,
        "qVol" => 1,
        "vNF" => 1,
        "volumes" => [
            [
                "dCom" => 0,
                "dLar" => 0,
                "dAlt" => 0,
                "qVol" => 0,
                "pBru" => 0
            ]
        ]
    ];

    $headers = [
        "Authorization" => "Bearer  eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJ1c3VhcmlvIjp7Im5vbWUiOiJUZXN0ZSIsImlkIjpudWxsLCJlbXByZXNhIjoiMzc5OTY4IiwidGlwbyI6IjEiLCJjbnBqIjoiMjM5NjYxODgwMDAxMjIifSwiYXV0aCI6eyJ1c3VhcmlvIjoialJuQWZXdEE5N09TcE9BcUxqRitrNDhEQ1VuMXFlTEp2ZnFqZ2tLWGxTY3hQdXFLU0VsMWhreDVIYlc5M1FRTUpWQlg2VVhUYkZ6ZlNJT21ydG01UnNjTmg1WjZ5MmVQRjFcL0FndVBTdnc5SWoyUnVYVWhxYmdESFFzWTZGTVdEK2ozUUZtTEhzTUd4UHFqV2c5Yko2dz09Iiwic2VuaGEiOiI2U0dVY2dLekVUYm1FVVVEdGdVT1hUVnZoZlo0WHBXZnFacFwvQ1hieTBxbkYrQnBIQ3VocGZVeUM0YllVMEFlUkpWQlg2VVhUYkZ6ZlNJT21ydG01Um5KeDVcLzlsclYxQzZQQVhndXd2dnU3VXhDdkp6REZKRzVscW9acTBTNVR4ZHBNaGZ3VkpPc1VyNlhMbTFZUDNMT29kbHlkUjViSU9PMjZtOEt2cWFFaWdcLzd1WURIUG01b25tUmNzanlcL0phIn0sImV4cCI6MTY5MzkzOTU3M30.mXUQhlc4bslMupxCFf4YSZt3URDc3Kw_sfd6Dfno-_jGqgKJ54u3ShjmKD7C204i0VOoJ6Ud5L9h00nFuRQr1g",
        "Content-Type" => "application/json",
        "accept" => "application/json"
    ];

    try {
        $response = $client->post("https://grupoastrolog.brudam.com.br/api/v1/frete/cotacao/calcula", [
            'headers' => $headers,
            'json' => $data
        ]);

        $statusCode = $response->getStatusCode();
        $body = $response->getBody()->getContents();

        echo "Status Code: " . $statusCode . "\n";
        echo "Response Body: " . $body . "\n";
    } catch (Exception $e) {
        echo "Erro: " . $e->getMessage() . "\n";
    }
});
