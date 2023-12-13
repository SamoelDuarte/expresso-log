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
use App\Http\Controllers\Utils;
use App\Models\Delivery;
use App\Models\StatusHistory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

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
        Route::get('/', 'gerarPedido');
        Route::post('/', 'gerarPedido');
    });

    Route::prefix('/consulta-cep')->controller(ConsultaCepController::class)->group(function () {
        Route::post('/', 'consultaCep');
    });

    Route::prefix('/getStatus')->controller(DeliveryController::class)->group(function () {
        Route::post('/', 'getStatus');
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
            $query->where('status', 'finalizado')
            ->orWhere('status', 'entregue')
            ->orWhere('status', 'devolvido')
            ;;
        })
        ->where(function ($query) {
            $query->whereNull('updated_at')
                ->orWhere('updated_at', '<=', Carbon::now()->subHour()->format('Y-m-d H:i:s'));
        })
        ->orderBy('id')
        ->limit(15)
        ->get();



    foreach ($deliveryes as $key => $value) {
        // dd($key);
        // Chave da API
        $apiKey = env('DBA_API_KEY');



        // Chave da NF-e
        $chaveNfe = $value->invoice_key;

        // dd($value);
        echo $chaveNfe . "------- ".$value->updated_at."---------------- atual".Carbon::now()->format('Y-m-d H:i:s')."<br>";
        $client = new Client();

        $uri = new Uri("https://englobasistemas.com.br/arquivos/api/PegarOcorrencias/RastreamentoChaveNfe");
        $uri = Uri::withQueryValue($uri, 'apikey', $apiKey);
        $uri = Uri::withQueryValue($uri, 'chaveNfe', $chaveNfe);

        try {
            $response = $client->request('POST', $uri);

            $responseArray = json_decode($response->getBody()->getContents(), true);
            if(isset( $responseArray[1])){
                $occurrences =  $responseArray[1];


                foreach ($occurrences as $key => $occurrence) {
    
                    Log::info("Iteração do loop externo: " . $key);
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
                $value->update(['updated_at' => Carbon::now()->format('Y-m-d H:i:s')]);
            }
            
        } catch (Exception $e) {
            Log::error("Error: " . $e->getMessage());
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

Route::get('/updateAstrlog', function () {

    $deliveryes = Delivery::with('carriers.documents')
        ->whereHas('carriers', function ($query) {
            $query->whereHas('documents', function ($documentQuery) {
                $documentQuery->where('number', '17000788000139');
            });
        })
        ->whereDoesntHave('status', function ($query) {
            $query->where('status', 'finalizado');
        })
        ->orderBy('id')
        ->get();




    foreach ($deliveryes as $key => $value) {
        $authResp = authGfl();
        if ($authResp->getStatusCode() === 200) {
            $responseData = json_decode($authResp->getBody(), true); // Decodifique a resposta JSON para um array associativo
            if (isset($responseData['data']['access_key'])) {
                $client = new Client();
                $accessKey = $responseData['data']['access_key'];
                $headers = [
                    "Authorization" => "Bearer  " . $accessKey
                ];

                $response = $client->get("https://grupoastrolog.brudam.com.br/api/v1/tracking/ocorrencias/nfe?chave=" . $value->invoice_key, [
                    'headers' => $headers
                ]);

                $body = $response->getBody()->getContents();
                $result = json_decode($body, true);

                StatusHistory::where('external_code', $result['data'][0]['documento'])->delete();


                for ($i = 0; $i < count($result['data'][0]['dados']); $i++) {


                    StatusHistory::create([
                        'delivery_id' => $value->id,
                        'external_code' => $result['data'][0]['documento'],
                        'status' => $result['data'][0]['dados'][$i]['descricao'],
                        'observation' => $result['data'][0]['dados'][$i]['obs'],
                        'detail' => $result['data'][0]['dados'][$i]['obs'],
                    ]);
                }
            }
        }
    }
});

Route::post('/astralog', function () {


    $authResp = authGfl();

    $accessKey = null;

    if ($authResp->getStatusCode() === 200) {
        $responseData = json_decode($authResp->getBody(), true); // Decodifique a resposta JSON para um array associativo
        if (isset($responseData['data']['access_key'])) {
            $accessKey = $responseData['data']['access_key'];


            $client = new Client();
            $dateTime = new DateTime();
            $formattedDate = $dateTime->format("Y-m-d");

            $xmlContent = file_get_contents('php://input');
            $xmlObj = simplexml_load_string($xmlContent); // Transformar o XML em um objeto SimpleXMLElement



            $data = array(
                "documentos" => array(
                    array(
                        "minuta" => array(
                            "toma" => (int)$xmlObj->NFe->infNFe->ide->tpNF,
                            "nDocEmit" => "17000788000139",
                            "dEmi" => $formattedDate,
                            "rSeg" => 4,
                            "cSeg" => '33164021000100',
                            "cServ" => "123",
                            "cTab" => "FRETE BARATO",
                            "tpEmi" => (int)$xmlObj->NFe->infNFe->ide->tpEmis,
                            "nOcc" => 1,
                            "cAut" => (string)$xmlObj->protNFe->infProt->chNFe,
                            "transf" => array(
                                "cAeroIni" => "sbsp",
                                "cAeroFim" => "sbsp"
                            ),
                            "carga" => array(
                                "pBru" => (float) $xmlObj->NFe->infNFe->det->prod->vFrete,
                                "pCub" => (float) $xmlObj->NFe->infNFe->det->prod->vDesc,
                                "qVol" => (int) $xmlObj->NFe->infNFe->transp->vol->qVol,
                                "vTot" => (float) $xmlObj->NFe->infNFe->total->ICMSTot->vNF
                            ),
                            "xReferencia" => "1",
                            "xOrdemServico" => "1"
                        ),
                        "compl" => array(
                            "entrega" => array(
                                "dPrev" => $formattedDate,
                                "hPrev" => "12:00:00"
                            ),
                            "respEntrega" => array(
                                "tpResp" => 3,
                                "nDoc" => "16620067808"
                            ), (string)
                            "cOrigCalc" => (string)$xmlObj->NFe->infNFe->emit->enderEmit->cMun,
                            "cDestCalc" => (string)$xmlObj->NFe->infNFe->dest->enderDest->cMun,
                            "xObs" => substr((string)$xmlObj->NFe->infNFe->infAdic->infCpl, 0, 40)
                        ),
                        "toma" => array(
                            "nDoc" => (string)$xmlObj->NFe->infNFe->emit->CNPJ,
                            "IE" =>  (string)$xmlObj->NFe->infNFe->emit->IE,
                            "cFiscal" => 1,
                            "xNome" => (string)$xmlObj->NFe->infNFe->emit->xNome,
                            "xFant" => (string)$xmlObj->NFe->infNFe->emit->xFant,
                            "nFone" => (string)$xmlObj->NFe->infNFe->emit->enderEmit->fone,
                            "ISUF" => "12345678",
                            "xLgr" => (string)$xmlObj->NFe->infNFe->emit->enderEmit->xLgr,
                            "nro" => (string)$xmlObj->NFe->infNFe->emit->enderEmit->nro,
                            "xCpl" => "1",
                            "xBairro" => (string)$xmlObj->NFe->infNFe->emit->enderEmit->xBairro,
                            "cMun" => (int)$xmlObj->NFe->infNFe->emit->enderEmit->cMun,
                            "CEP" => (string)$xmlObj->NFe->infNFe->emit->enderEmit->CEP,
                            "cPais" => (int)$xmlObj->NFe->infNFe->emit->enderEmit->cPais,
                            "email" => 'email@email.com'
                        ),
                        "rem" => array(
                            "nDoc" => (string)$xmlObj->NFe->infNFe->emit->CNPJ,
                            "IE" => (string)$xmlObj->NFe->infNFe->emit->IE,
                            "cFiscal" => 1,
                            "xNome" => (string)$xmlObj->NFe->infNFe->emit->xNome,
                            "xFant" => (string)$xmlObj->NFe->infNFe->emit->xFant,
                            "nFone" => (string)$xmlObj->NFe->infNFe->emit->enderEmit->fone,
                            "xLgr" => (string)$xmlObj->NFe->infNFe->emit->enderEmit->xLgr,
                            "nro" => (string)$xmlObj->NFe->infNFe->emit->enderEmit->nro,
                            "xCpl" => "1",
                            "xBairro" => (string)$xmlObj->NFe->infNFe->emit->enderEmit->xBairro,
                            "cMun" => (int)$xmlObj->NFe->infNFe->emit->enderEmit->cMun,
                            "CEP" => (string)$xmlObj->NFe->infNFe->emit->enderEmit->CEP,
                            "cPais" => (int)$xmlObj->NFe->infNFe->emit->enderEmit->cPais,
                            "email" => 'email@email.com'
                        ),
                        "exped" => array(
                            "nDoc" => (string)$xmlObj->NFe->infNFe->emit->CNPJ,
                            "IE" => (string)$xmlObj->NFe->infNFe->emit->IE,
                            "cFiscal" => 1,
                            "xNome" => (string)$xmlObj->NFe->infNFe->emit->xNome,
                            "xFant" => (string)$xmlObj->NFe->infNFe->emit->xFant,
                            "nFone" => (string)$xmlObj->NFe->infNFe->emit->enderEmit->fone,
                            "xLgr" => (string)$xmlObj->NFe->infNFe->emit->enderEmit->xLgr,
                            "nro" => (string)$xmlObj->NFe->infNFe->emit->enderEmit->nro,
                            "xCpl" => "1",
                            "xBairro" => (string)$xmlObj->NFe->infNFe->emit->enderEmit->xBairro,
                            "cMun" => (int)$xmlObj->NFe->infNFe->emit->enderEmit->cMun,
                            "CEP" => (string)$xmlObj->NFe->infNFe->emit->enderEmit->CEP,
                            "cPais" => (int)$xmlObj->NFe->infNFe->emit->enderEmit->cPais,
                            "email" => 'email@email.com'
                        ),
                        "receb" => array(
                            "nDoc" => (string)$xmlObj->NFe->infNFe->dest->CPF,
                            "IE" => "ISENTO",
                            "cFiscal" => 1,
                            "xNome" => (string)$xmlObj->NFe->infNFe->dest->xNome,
                            "xFant" =>  "ERC PRATO",
                            "nFone" => (string)$xmlObj->NFe->infNFe->dest->enderDest->fone,
                            "xLgr" => (string)$xmlObj->NFe->infNFe->dest->enderDest->xLgr,
                            "nro" => (string)$xmlObj->NFe->infNFe->dest->enderDest->nro,
                            "xCpl" => "1",
                            "xBairro" => (string)$xmlObj->NFe->infNFe->dest->enderDest->xBairro,
                            "cMun" => (int)$xmlObj->NFe->infNFe->dest->enderDest->cMun,
                            "CEP" => Utils::sanitizeZipcode($xmlObj->NFe->infNFe->dest->enderDest->CEP),
                            "cPais" => (int)$xmlObj->NFe->infNFe->dest->enderDest->cPais,
                            "email" => (string)$xmlObj->NFe->infNFe->dest->email
                        ),
                        "dest" => array(
                            "nDoc" => (string)$xmlObj->NFe->infNFe->dest->CPF,
                            "IE" => "ISENTO",
                            "cFiscal" => 1,
                            "xNome" => (string)$xmlObj->NFe->infNFe->dest->xNome,
                            "xFant" => "ERC PRATO",
                            "nFone" => (string)$xmlObj->NFe->infNFe->dest->enderDest->fone,
                            "xLgr" => (string)$xmlObj->NFe->infNFe->dest->enderDest->xLgr,
                            "nro" => (string)$xmlObj->NFe->infNFe->dest->enderDest->nro,
                            "xCpl" => "1",
                            "xBairro" => (string)$xmlObj->NFe->infNFe->dest->enderDest->xBairro,
                            "cMun" => (int)$xmlObj->NFe->infNFe->dest->enderDest->cMun,
                            "CEP" => Utils::sanitizeZipcode($xmlObj->NFe->infNFe->dest->enderDest->CEP),
                            "cPais" => (int)$xmlObj->NFe->infNFe->dest->enderDest->cPais,
                            "email" => (string)$xmlObj->NFe->infNFe->dest->email
                        ),
                        "documentos" => array(
                            array(
                                "nPed" => "1",
                                "serie" => (string)$xmlObj->NFe->infNFe->ide->serie,
                                "nDoc" => (string)$xmlObj->NFe->infNFe->ide->nNF,
                                "dEmi" => $formattedDate,
                                "vBC" => (float)$xmlObj->NFe->infNFe->det[0]->imposto->ICMS->ICMS00->vBC,
                                "vICMS" => (float)$xmlObj->NFe->infNFe->det[0]->imposto->ICMS->ICMS00->vICMS,
                                "vBCST" => (float)$xmlObj->NFe->infNFe->det[0]->imposto->ICMS->ICMS00->vBCST,
                                "vST" => (float)$xmlObj->NFe->infNFe->det[0]->imposto->ICMS->ICMS00->vICMS,
                                "vProd" => (float)$xmlObj->NFe->infNFe->det[0]->prod->vProd,
                                "vNF" => (float)$xmlObj->NFe->infNFe->total->ICMSTot->vNF,
                                "nCFOP" => (string)$xmlObj->NFe->infNFe->det[0]->CFOP,
                                "pBru" => (float)$xmlObj->NFe->infNFe->det[0]->prod->vFrete,
                                "qVol" => (float)$xmlObj->NFe->infNFe->det[0]->prod->qCom,
                                "chave" => (string)$xmlObj->protNFe->infProt->chNFe,
                                "tpDoc" => "00",
                                "xNat" => $xmlObj->NFe->infNFe->ide->natOp
                            ),
                        ),
                    )
                )
            );

            // echo json_encode($data);
            // exit;




            $headers = [
                "Authorization" => "Bearer  " . $accessKey,
                "Content-Type" => "application/json",
                "accept" => "application/json"
            ];




            $response = $client->post("https://grupoastrolog.brudam.com.br/api/v1/operacional/emissao/minuta", [
                'headers' => $headers,
                'json' => $data
            ]);

            $body = $response->getBody()->getContents();
            $result = json_decode($body, true);
            dd($result);
        } else {
            echo "A chave 'access_key' não foi encontrada na resposta.\n";
        }
    } else {
        echo "A solicitação não foi bem-sucedida.\n";
    }
});

function authGfl()
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

    return $response;
}

Route::get('/daytonaCotação', function () {
    // URL do serviço web
    $wsdl = 'http://daytona.azurewebsites.net/WS/V1/WSDaytonaV1.asmx?wsdl';

    // Parâmetros da requisição
    $parameters = array(
        'consCEP' => array(
            'sCnpjOrigem' => '23966188000122',
            'sCEPDestino' => '04941080',
            'sPais' => 'brasil',
            'dPeso' => '2',
            'dNotas' => '10090',
            'iUrgente' => 0,
            'lstVolumes' => array(
                'ConsultaPreco_volumesV1' => array(
                    'cvl_largura' => '16',
                    'cvl_altura' => '16',
                    'cvl_comprimento' => '16',
                ),
                'ConsultaPreco_volumesV1' => array(
                    'cvl_largura' => '16',
                    'cvl_altura' => '16',
                    'cvl_comprimento' => '16',
                ),
            ),
        ),
    );

    try {
        // Cria o cliente SOAP
        $client = new SoapClient($wsdl, array('trace' => 1));

        // Chama o método do serviço web
        $response = $client->ConsultaTabPrecoCEPV1($parameters);

        // Exibe a resposta
        dd($response);
    } catch (SoapFault $e) {
        // Trata erros
        echo "Erro: " . $e->getMessage();
    }
});

Route::get('/daytonaCotação2', function () {
    // Defina a URL do serviço SOAP
    $soapURL = 'http://daytona.azurewebsites.net/WS/V1/WSDaytonaV1.asmx?wsdl';

    // Parâmetros da solicitação SOAP
    $consCEP = array(
        'sCnpjOrigem' => '23966188000122',
        'sCEPDestino' => '04941080',
        'sPais' => 'BR',
        'dPeso' => 10.0, // Substitua pelo peso desejado
        'dNotas' => 100.0, // Substitua pelo valor das notas desejado
        'iUrgente' => 1, // Substitua pelo valor desejado
        'lstVolumes' => array(
            array(
                'cvl_largura' => 10.0, // Substitua pelas dimensões desejadas
                'cvl_altura' => 5.0,
                'cvl_comprimento' => 15.0,
            ),
            array(
                'cvl_largura' => 8.0,
                'cvl_altura' => 4.0,
                'cvl_comprimento' => 12.0,
            )
        )
    );

    // Crie um cliente SOAP
    $client = new SoapClient($soapURL, array('soap_version' => SOAP_1_2));

    try {
        // Faça a chamada ao método desejado
        $result = $client->ConsultaTabPrecoCEPV1(array('consCEP' => $consCEP));

        // Manipule o resultado aqui (depende da estrutura da resposta)
        dd($result);
    } catch (SoapFault $e) {
        // Trate erros de SOAP aqui
        dd($e->getMessage());
    }
});
