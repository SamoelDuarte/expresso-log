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
use App\Models\Carrier;
use App\Models\Delivery;
use App\Models\Error;
use App\Models\StatusHistory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
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
        Route::get('/status', 'statusDash');
    });

    Route::prefix('/transportadora')->controller(CarrierController::class)->group(function () {
        Route::get('/', 'index')->name('admin.transp.index');
        Route::get('/novo', 'create')->name('admin.transp.create');
        Route::get('/edit/{transportadora}', 'edit')->name('admin.transp.edit');
        Route::put('/update/{transportadora}', 'update')->name('admin.transp.update'); // Adicione esta linha
        Route::delete('/delete/{transportadora}', 'destroy')->name('admin.transp.destroy');
        Route::post('/store', 'store')->name('admin.transp.store');
    });

    Route::prefix('/entregas')->controller(DeliveryController::class)->group(function () {
        Route::get('/', 'index')->name('admin.entrega.index');
        Route::get('/getEntregas', 'getEntregas');
        Route::get('/getinfoEntrega/{id}', 'show');
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

Route::get('/getFreteJT', function () {


    //Definindo parâmetros
    $privateKey = '65b24f925e2443ea83243083b2b2c5da';
    $apiAccuont = '615978675254329386';


    // dd($headerSignature);
    //Montando o JSON do envio
    $pedido = '{
		"customerCode":"J0086025107",
		"digest":"FuriWZepWBao9l9eHFy/+A==",
		"txlogisticId":"12879542",
		"expressType":"EZ",
		"orderType":"2",
		"serviceType":"02",
		"deliveryType":"03",
		"sender":{
		"name":"COMERCIO DE MOVEIS DIGITAL - AMO MOVEIS LTDA",
		"company":"AMO MOVEIS",
		"postCode":"86701474",
		"mailBox":"no-email@mail.com.br",
		"taxNumber":"06275524000171",
		"mobile":"43991090707",
		"phone":"43991090707",
		"prov":"PR",
		"city":"Arapongas",
		"street":"Rua Drongo",
		"streetNumber":"162",
		"address":"Rua Drongo, 162, Sala 4",
		"areaCode":"43",
		"ieNumber":"9085799417",
		"area":"Vila Cascata"
		},
		"receiver":{
		"name":"Rubia Pedrodo",
		"postCode":"86701474",
		"mailBox":"no-email@mail.com.br",
		"taxNumber":"87862239920",
		"mobile":"43988664740",
		"phone":"43988664740",
		"prov":"PR",
		"city":"Arapongas",
		"street":"Rua Drongo",
		"streetNumber":"162",
		"address":"Rua Drongo, 162",
		"areaCode":"43",
		"ieNumber":"0000000",
		"area":"Vila Cascata"
		},
		"translate":{
		"name":"COMERCIO DE MOVEIS DIGITAL - AMO MOVEIS LTDA",
		"company":"AMO MOVEIS",
		"postCode":"86701474",
		"mailBox":"no-email@mail.com.br",
		"taxNumber":"06275524000171",
		"mobile":"43991090707",
		"phone":"43991090707",
		"prov":"PR",
		"city":"Arapongas",
		"street":"Rua Drongo",
		"streetNumber":"251",
		"address":"Rua Drongo, 162, Sala 4",
		"areaCode":"43",
		"ieNumber":"9085799417",
		"area":"Vila Cascata"
		},
		"goodsType":"bm000008",
		"weight":"8.00",
		"totalQuantity":1,
		"invoiceMoney":"149.99",
		"remark":"CTE emitido para validacao conforme solicitado pelo cliente. Valor de frete informado para fins de referencia. Documento emitido por ME optante pelo simples nacional, nao gera direito a credito de ISS e IPI",
		"items":[
		{
			"itemType":"bm000008",
			"itemName":"DIVERSOS",
			"number":"1",
			"itemValue":"149.99",
			"priceCurrency":"BRL",
			"desc":"DIVERSOS",
			"itemNcm":"00000000"
		}
		],
		"invoiceNumber":"434",
		"invoiceSerialNumber":"1",
		"invoiceMoney":"149.99",
		"taxCode":"0000000000000",
		"invoiceAccessKey":"41230506275524000171550010000004341557749290",
		"invoiceIssueDate":"2023-05-19 15:24:23"
	}';

    //Codificando o pedido para envio
    $req_pedido = rawurlencode($pedido);



    //Montando o digest do header
    $headerDigest = base64_encode(md5($pedido . $privateKey, true));


    //  dd($headerDigest);
    //Instanciando e enviando a requisição
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://demogw.jtjms-br.com/webopenplatformapi/api/order/addOrder',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => 'bizContent=' . $req_pedido,
        CURLOPT_HTTPHEADER => array(
            'timestamp:1565238848921',
            'apiAccount:' . $apiAccuont,
            'digest:' . $headerDigest,
            'Content-Type: application/x-www-form-urlencoded'
        ),
    ));

    //Enviando a requisição e gravando a resposta
    $response = curl_exec($curl);

    //Fechando a requisição
    curl_close($curl);

    //Exibindo a resposta
    echo '<br><br>' . $response;
});

Route::get('/getOrdesJT', function () {

    //Definindo parâmetros
    $privateKey = env('PRIVATE_KEY_JT');
    $apiAccount = env('API_ACCOUNT_JT');

    // Parametro de negócio
    $pedido = [
        "serialNumber" => "888030034335191",
        "digest" => "Zy+vQdOi9CKk8snUA517nA==",
        "customerCode" => "J0086026981",
        "command" => 2,


    ];

    $pedido = json_encode($pedido);

    // Codificando o pedido para envio
    $req_pedido = rawurlencode($pedido);

    // Montando o digest do header
    $headerDigest = base64_encode(md5($pedido . $privateKey, true));

    // Criando um carimbo de data/hora (timestamp)
    $timestamp = round(microtime(true) * 1000);

    // URL da API
    // $url = 'https://demoopenapi.jtjms-br.com/webopenplatformapi/api/logistics/trace';
    $url = 'https://openapi.jtjms-br.com/webopenplatformapi/api/order/getOrders';

    // Iniciando uma sessão cURL
    $curl = curl_init();

    // Configurando as opções da requisição cURL
    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => 'bizContent=' . $req_pedido,
        CURLOPT_HTTPHEADER => array(
            'timestamp:' . $timestamp,
            'apiAccount:' . $apiAccount,
            'digest:' . $headerDigest,
            'Content-Type: application/x-www-form-urlencoded'
        ),
    ));

    // Enviando a requisição e obtendo a resposta
    $response = curl_exec($curl);

    // Verificando se ocorreu algum erro na requisição
    if (curl_errno($curl)) {
        echo 'Erro cURL: ' . curl_error($curl);
    }

    // Fechando a requisição cURL
    curl_close($curl);

    // Exibindo a resposta
    echo '<br><br>' . $response;
});

Route::get('/updateStatusDBA', function () {

    $numbersToSearch = ['08982220000170', '50160966000164'];

    $deliveryes = DeliveryController::getDeliverys($numbersToSearch);



    foreach ($deliveryes as $key => $value) {
        // dd($key);
        // Chave da API
        $apiKey = env('DBA_API_KEY');



        // Chave da NF-e
        $chaveNfe = $value->invoice_key;

        // dd($value);
        echo $chaveNfe . "------- horaValue" . $value->updated_at . "------- horaAtual" . Carbon::now()->format('Y-m-d H:i:s') . "<br>";
        $client = new Client();

        $uri = new Uri("https://englobasistemas.com.br/arquivos/api/PegarOcorrencias/RastreamentoChaveNfe");
        $uri = Uri::withQueryValue($uri, 'apikey', $apiKey);
        $uri = Uri::withQueryValue($uri, 'chaveNfe', $chaveNfe);

        try {
            $response = $client->request('POST', $uri);

            $responseArray = json_decode($response->getBody()->getContents(), true);

            $occurrences = isset($responseArray[1]) ? $responseArray[1] : null;

            if ($occurrences !== null) {
                foreach ($occurrences as $key => $occurrence) {
                    $codigo = $occurrence['codigo'];

                    // Verifique se o código já existe na tabela status_history.
                    $existeRegistro = StatusHistory::where('external_code', $codigo)->exists();

                    if (!$existeRegistro) {
                        // O código não existe, então você pode inserir so registro.
                        StatusHistory::create([
                            'delivery_id' => $value->id,
                            'external_code' => $codigo,
                            'status' => $occurrence['ocorrencia'],
                            'observation' => $occurrence['obs'],
                        ]);
                    }
                }
                $value->update(['updated_at' => Carbon::now()->format('Y-m-d H:i:s')]);
            } else {
                $value->update(['updated_at' => Carbon::now()->format('Y-m-d H:i:s')]);
            }
        } catch (Exception $e) {
            Log::error("Error: " . $e->getMessage());
            echo "Error: " . $e->getMessage() . "\n";
        }
    }
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

Route::get('/updateStatusJET', function () {
    $numbersToSearch = ['42584754001077'];

    $deliveryes = DeliveryController::getDeliverys($numbersToSearch);


    // // dd($deliveryes);
    foreach ($deliveryes as $key => $value) {
        //  dd($value->external_code);
        //Definindo parâmetros
        $privateKey = env('PRIVATE_KEY_JT');
        $apiAccount = env('API_ACCOUNT_JT');


        // Montando o JSON do envio
        $pedido = [
            "billCodes" => $value->external_code,

        ];

        $pedido = json_encode($pedido);

        // Codificando o pedido para envio
        $req_pedido = rawurlencode($pedido);

        // Montando o digest do header
        $headerDigest = base64_encode(md5($pedido . $privateKey, true));

        // Criando um carimbo de data/hora (timestamp)
        $timestamp = round(microtime(true) * 1000);

        // URL da API
        // $url = 'https://demoopenapi.jtjms-br.com/webopenplatformapi/api/logistics/trace';
        $url = 'https://openapi.jtjms-br.com/webopenplatformapi/api/logistics/trace';

        // Iniciando uma sessão cURL
        $curl = curl_init();

        // Configurando as opções da requisição cURL
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => 'bizContent=' . $req_pedido,
            CURLOPT_HTTPHEADER => array(
                'timestamp:' . $timestamp,
                'apiAccount:' . $apiAccount,
                'digest:' . $headerDigest,
                'Content-Type: application/x-www-form-urlencoded'
            ),
        ));

        // Enviando a requisição e obtendo a resposta
        $response = curl_exec($curl);

        // Verificando se ocorreu algum erro na requisição
        if (curl_errno($curl)) {
            echo 'Erro cURL: ' . curl_error($curl);
        }


        // Fechando a requisição cURL
        curl_close($curl);

        $resonseArray = json_decode($response, true);
        // Exibindo a resposta

        echo   "Numero Nota : " . $value->invoice . " ,  Resposta : " . $resonseArray['data'][0]['billCode'] . " <br>";

        foreach ($resonseArray['data'][0]['details'] as  $detail) {

            // dd($detail);
             // Verifique se o código já existe na tabela status_history.
             $existeRegistro = StatusHistory::where('external_code', $detail['scanTime'])->exists();

             if (!$existeRegistro) {
                StatusHistory::create([
                    'delivery_id' => $value->id,
                    'external_code' => $detail['scanTime'],
                    'status' => $detail['scanType'],
                    'observation' => $detail['scanNetworkCity'],
                    'detail' => $detail['scanNetworkProvince'],
                ]);
             }

           
        }
        $value->update(['updated_at' => Carbon::now()->format('Y-m-d H:i:s')]);
    }
});

Route::get('/updateStatusGFL', function () {
    $numbersToSearch = ['23820639001352', '24230747094913'];

    $deliveryes = DeliveryController::getDeliverys($numbersToSearch);


    // dd($deliveryes);
    foreach ($deliveryes as $key => $value) {



        // URL base do endpoint
        $baseUrl = 'http://gflapi.sinclog.app.br/Api/Ocorrencias/OcorrenciaNotaFiscalDePara/';

        // Criação de uma instância do cliente Guzzle
        $client = new Client();

        // Montagem do JSON com os dados da solicitação
        $requestData = [
            "cnpjEmbarcador" => "23966188000122",
            "cnpjRemetente" => "23966188000122",
            "listaNotasFiscais" => [$value->invoice . "/" . $value->serie],
        ];

        try {
            // Fazendo a requisição POST com o cabeçalho de autorização e corpo JSON
            $response = $client->post($baseUrl, [
                'headers' => [
                    'Authorization' => 'Basic ' . env('GFL_KEY_ACESS'),
                    'Content-Type' => 'application/json', // Especifique o tipo de conteúdo como JSON
                ],
                'body' => json_encode($requestData), // Passa o JSON como corpo da requisição
            ]);

            // Obtendo o corpo da resposta como string
            $responseBody = $response->getBody()->getContents();

            $responseArray = json_decode($responseBody, true);

            // Reverte a ordem dos índices
            $arrayReversed = array_reverse($responseArray['listaResultados']);

            //  dd($arrayReversed);

            foreach ($arrayReversed as $ocorrencia) {



                $row = StatusHistory::where('external_code', $ocorrencia['idOcorrencia'])->exists();


                if (!$row) {
                    $row = new StatusHistory();
                    $row->delivery_id  = $value->id;
                    $row->status = $ocorrencia['descricaoOcorrencia'];
                    $row->detail = $ocorrencia['unidadeOcorrencia'] ? $ocorrencia['unidadeOcorrencia'] : "";
                    $row->observation = $ocorrencia['nomeCidade'] ? $ocorrencia['nomeCidade'] : "";
                    $row->external_code = $ocorrencia['idOcorrencia'] ? $ocorrencia['idOcorrencia'] : "";

                    $row->save();
                }
                // dd($row);
                //  dd( $ocorrencia);
            }
            // Imprimindo a resposta
            // echo $responseBody;
        } catch (Exception $e) {
            echo "Ocorreu um erro: " . $e->getMessage();
        }
        $value->update(['updated_at' => Carbon::now()->format('Y-m-d H:i:s')]);
    }
});


Route::get('/updateStatusLoggi', function () {
    $numbersToSearch = ['24217653000195'];

    $deliveryes = DeliveryController::getDeliverys($numbersToSearch);


    foreach ($deliveryes as $key => $value) {




        $token = PedidosController::authLoggi();
        $client = new \GuzzleHttp\Client();
        try {
            $response = $client->request('GET', 'https://api.loggi.com/v1/companies/394829/packages/' . $value->external_code . '/tracking', [
                'headers' => [
                    'accept' => 'application/json',
                    'authorization' => 'Bearer ' . $token['idToken'],
                ],
            ]);

            $responseArray = json_decode($response->getBody(), true);

            foreach ($responseArray['packages'][0]['trackingHistory'] as $ocorrencia) {


                $row = StatusHistory::where('external_code', $ocorrencia['status']['code'])->exists();


                if (!$row) {

                    $row = new StatusHistory();
                    $row->delivery_id  = $value->id;
                    $row->status = $ocorrencia['status']['highLevelStatus'];
                    $row->detail = $ocorrencia['status']['description'];
                    $row->observation = '';
                    $row->external_code = $ocorrencia['status']['code'];

                    $row->save();
                }
            }
        } catch (Exception $e) {
        }
        $value->update(['updated_at' => Carbon::now()->format('Y-m-d H:i:s')]);
    }
});


Route::get('/JT', function () {

    function generateBusinessParameterSignature($customerCode, $pwd, $privateKey)
    {
        // Concatenate customerCode, pwd, and privateKey
        $concatenatedString = $customerCode . $pwd . $privateKey;

        // Generate MD5 hash of the concatenated string
        $md5Hash = md5($concatenatedString, true);

        // Encode the MD5 hash using Base64
        $base64Encoded = base64_encode($md5Hash);

        return $base64Encoded;
    }

    // Example usage:
    $customerCode = "J0086026981";
    // $plainTextPwd = "G3H0b644";
    // $plainTextPwd = "G3H0b644";
    $plainTextPwd = "M1r4nt3981";

    $encryptedPwd = strtoupper(md5($plainTextPwd . "jadada236t2")); // Assuming pwd is in uppercase MD5 format
    $privateKey = "bccf1dc5e47a4cb7a69d644d8c597c3a";

    $businessParameterSignature = generateBusinessParameterSignature($customerCode, $encryptedPwd, $privateKey);
    echo "Business Parameter Signature Digest: " . $businessParameterSignature;
});

Route::get('/countAlerta', function () {
    // Buscar os registros de StatusHistory com send igual a 1
    $statusArray = StatusHistory::where('send', 1)->limit(15)->get();

    echo " <h2>Quantidade de alerta  ( " . count($statusArray) . " ) </h2>.";
});

Route::get('/updateAlerta', function () {

    // Data de ontem
    $ontem = Carbon::yesterday();

    // Consulta na tabela StatusHistory
    $statusArray = StatusHistory::select('id', 'delivery_id', 'status', 'send', \DB::raw('MAX(updated_at) as max_created_at'))
        ->whereDate('updated_at', $ontem)
        ->whereIn('status', ['entregue', 'finalizado', 'entrega realizada (mobile)', 'entrega realizada', 'Saiu para Entrega'])
        ->groupBy('id', 'delivery_id', 'status', 'send')
        ->orderByRaw("FIELD(status, 'entregue', 'finalizado', 'entrega realizada (mobile)', 'entrega realizada', 'Saiu para Entrega')")
        ->get();

    foreach ($statusArray as $status) {
        // Define o valor de 'send' como 1
        $status->send = 1;
        // Salva a alteração no banco de dados
        $status->save();
    }
});
Route::get('/alerta_entregue', function () {
    // Buscar os registros de StatusHistory com send igual a 1
    $statusArray = StatusHistory::where('send', 1)->limit(15)->get();

    // dd($statusArray);
    foreach ($statusArray as $status) {
        // Criar uma instância do cliente Guzzle
        $client = new Client();
        $headers = [];
        // Definir os parâmetros da requisição
        $options = [
            'multipart' => [
                [
                    'name' => 'numero',
                    'contents' => $status->deliveries->invoice,

                ],
                [
                    'name' => 'status',
                    'contents' => $status->status,
                ]
            ]
        ];

        // Criar a requisição POST para a URL desejada
        $request = new Request('POST', 'https://lojamirante.com.br/Cron/atualiza_status_pedido', $headers);

        // Enviar a requisição de forma assíncrona e esperar pela resposta
        $res = $client->sendAsync($request, $options)->wait();

        // Exibir o corpo da resposta
        echo $res->getBody();

        // Alterar o valor do campo send para 0
        $status->send = 0;

        // Salvar as alterações no banco de dados
        $status->save();
    }
});


Route::get('/getEtiqueta', function () {

    //Definindo parâmetros
    $privateKey = env('PRIVATE_KEY_JT');
    $apiAccount = env('API_ACCOUNT_JT');

    // Montando o JSON do envio
    $pedido = [
        "billCodes" => $value->external_code,

    ];

    $pedido = json_encode($pedido);

    // Codificando o pedido para envio
    $req_pedido = rawurlencode($pedido);

    // Montando o digest do header
    $headerDigest = base64_encode(md5($pedido . $privateKey, true));

    // Criando um carimbo de data/hora (timestamp)
    $timestamp = round(microtime(true) * 1000);

    // URL da API
    $url = 'https://demoopenapi.jtjms-br.com/webopenplatformapi/api/logistics/trace';
    //   $url = 'https://demoopenapi.jtjms-br.com/webopenplatformapi/api/order/printOrder';

    // Iniciando uma sessão cURL
    $curl = curl_init();

    // Configurando as opções da requisição cURL
    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => 'bizContent=' . $req_pedido,
        CURLOPT_HTTPHEADER => array(
            'timestamp:' . $timestamp,
            'apiAccount:' . $apiAccount,
            'digest:' . $headerDigest,
            'Content-Type: application/x-www-form-urlencoded'
        ),
    ));

    // Enviando a requisição e obtendo a resposta
    $response = curl_exec($curl);

    // Verificando se ocorreu algum erro na requisição
    if (curl_errno($curl)) {
        echo 'Erro cURL: ' . curl_error($curl);
    }

    // Fechando a requisição cURL
    curl_close($curl);

    // Exibindo a resposta
    echo '<br><br>' . $response . '<br><br>';
});

Route::get('/atualiza', function () {
    
});

Route::get('/getPorNota', function () {



    //Definindo parâmetros
    $privateKey = env('PRIVATE_KEY_JT');
    $apiAccount = env('API_ACCOUNT_JT');


    // Montando o JSON do envio
    $pedido = [
        "billCodes" => '888030039543530',

    ];

    $pedido = json_encode($pedido);

    // Codificando o pedido para envio
    $req_pedido = rawurlencode($pedido);

    // Montando o digest do header
    $headerDigest = base64_encode(md5($pedido . $privateKey, true));

    // Criando um carimbo de data/hora (timestamp)
    $timestamp = round(microtime(true) * 1000);

    // URL da API
    // $url = 'https://demoopenapi.jtjms-br.com/webopenplatformapi/api/logistics/trace';
    $url = 'https://openapi.jtjms-br.com/webopenplatformapi/api/logistics/trace';

    // Iniciando uma sessão cURL
    $curl = curl_init();

    // Configurando as opções da requisição cURL
    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => 'bizContent=' . $req_pedido,
        CURLOPT_HTTPHEADER => array(
            'timestamp:' . $timestamp,
            'apiAccount:' . $apiAccount,
            'digest:' . $headerDigest,
            'Content-Type: application/x-www-form-urlencoded'
        ),
    ));

    // Enviando a requisição e obtendo a resposta
    $response = curl_exec($curl);

    // Verificando se ocorreu algum erro na requisição
    if (curl_errno($curl)) {
        echo 'Erro cURL: ' . curl_error($curl);
    }


    // Fechando a requisição cURL
    curl_close($curl);

    $resonseArray = json_decode($response, true);
    // Exibindo a resposta

    // echo   "Numero Nota : ".$value->invoice." ,  Resposta : ".$resonseArray['data'][0]['billCode']." <br>" ; 

    $invertedArray = array_flip($resonseArray['data'][0]['details']);
    dd($invertedArray);
    // foreach ($resonseArray['data'][0]['details'] as  $detail) {

    //     StatusHistory::create([
    //         'delivery_id' => $value->id,
    //         'external_code' => $detail['scanCode'],
    //         'status' => $detail['scanType'],
    //         'observation' => $detail['scanNetworkCity'],
    //         'detail' => $detail['scanNetworkProvince'],
    //     ]);
    // }
    // $value->update(['updated_at' => Carbon::now()->format('Y-m-d H:i:s')]);
});


Route::get('/statusAll', function () {

    $statusList = StatusHistory::distinct()->pluck('status');

    dd($statusList);
});

Route::get('/dash',function(){
    $statusCounts = DB::table('deliveries')
    ->leftJoin('status_history', function ($join) {
        $join->on('deliveries.id', '=', 'status_history.delivery_id')
            ->whereRaw('status_history.id = (select max(id) from status_history where status_history.delivery_id = deliveries.id)');
    })
    ->whereNotIn('status_history.status', ['entregue', 'devolvido']) // Exclui deliveries com status de "entregue" e "devolvido"
    ->orWhereNull('status_history.status') // Também inclui deliveries sem status_history
    ->whereDate('status_history.created_at', '>', '2024-02-25') // Considera apenas as entregas após a data específica de criação do status_history
    ->select('status_history.status', DB::raw('count(*) as count'))
    ->groupBy('status_history.status')
    ->get();

    dd($statusCounts);
});