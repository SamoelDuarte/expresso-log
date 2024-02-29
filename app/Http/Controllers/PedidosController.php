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
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise\Create;
use Illuminate\Http\Request;

class PedidosController extends Controller
{
    public function gerarPedido(Request $request)
    {
        try {
            //code...
        

        $dataEntrega = $request->dataprevista;

        $xmlContent = $request->getContent();
        $xmlObject = simplexml_load_string($xmlContent); // Transformar o XML em um objeto SimpleXMLElement
        $jsonString = json_encode($xmlObject); // Transformar o objeto em uma string JSON
        $XmlArray = json_decode($jsonString, true);
        $documento = $XmlArray['NFe']['infNFe']['transp']['transporta']['CNPJ'];

        switch ($documento) {
                // Transportadora DBA
            case "50160966000164":
                $this->gerarPedidoDBA($xmlContent, $dataEntrega);
                break;

            case "42584754001077":
                $this->gerarPedidoJT($xmlContent, $dataEntrega);
                break;

            case "23820639001352":
            case "24230747094913":
                $this->gerarPedidoGFL($xmlContent, $dataEntrega);
                break;

            case "24217653000195":
                $this->gerarPedidoLoggi($xmlContent, $dataEntrega);
                break;

            case "37744796000105":
            case "08982220000170":
                $this->gerarPedidoMesh($xmlContent, $dataEntrega);
                break;

            case "17000788000139":
            case "20588287000120":

                $this->gerarPedidoAstralog($xmlContent, $dataEntrega);
                break;

            default:
                Error::create(['erro' => 'Transportadora não Integrada CNPJ:' . $documento]);
        }

    } catch (Exception $e) {
        // Tratamento da exceção aqui
        $mensagem = 'Error Geral: ' . $e->getMessage() . ' em ' . $e->getFile() . ' na linha ' . $e->getLine();
        Error::create(['erro' => $mensagem]);
    }
    }
    public function gerarPedidoLoggi($xmlContent, $dataEntrega)
    {
        $token = $this->authLoggi();
        $xmlObject = simplexml_load_string($xmlContent);
        $jsonString = json_encode($xmlObject); // Transformar o objeto em uma string JSON
        $XmlArray = json_decode($jsonString, true);
        $documento = $XmlArray['NFe']['infNFe']['transp']['transporta']['CNPJ'];

        // Extrair os dados relevantes do XML
        $enderecoDestinatario = $xmlObject->NFe->infNFe->dest->enderDest;
        $enderecoRemetente = $xmlObject->NFe->infNFe->emit->enderEmit;
        $dadosDestinatario = $xmlObject->NFe->infNFe->dest;
        $dadosRemetente = $xmlObject->NFe->infNFe->emit;


        //   dd($xmlObject->NFe->infNFe->dest);
        // Construir o corpo da requisição para a API da Loggi
        $requestData = [
            'shipFrom' => [
                'address' => [
                    'correiosAddress' => [
                        'logradouro' => (string) $enderecoRemetente->xLgr,
                        'numero' => (string) $enderecoRemetente->nro,
                        'cep' => (string) $enderecoRemetente->CEP,
                        'cidade' => (string) $enderecoRemetente->xMun,
                        'bairro' => (string) $enderecoRemetente->xBairro,
                        'complemento' => (string) $enderecoRemetente->xBairro,
                        'uf' => (string) $enderecoRemetente->UF,
                    ]
                ],
                'name' => (string) $dadosRemetente->xNome,
                'phoneNumber' => (string) $enderecoRemetente->fone,
                'federalTaxId' => (string) $dadosRemetente->CNPJ,
            ],
            'shipTo' => [
                'address' => [
                    'correiosAddress' => [
                        'logradouro' => (string) $enderecoDestinatario->xLgr,
                        'cep' => (string) $enderecoDestinatario->CEP,
                        'cidade' => (string) $enderecoDestinatario->xMun,
                        'uf' => (string) $enderecoDestinatario->UF,
                    ]
                ],
                'name' => (string) $dadosDestinatario->xNome,
                'federalTaxId' => (string) $dadosDestinatario->CPF ?: (string) $dadosDestinatario->CNPJ,
            ],

            'pickupType' => 'PICKUP_TYPE_MILK_RUN',
            'packages' => [
                [
                    'freightType' => 'FREIGHT_TYPE_ECONOMIC',
                    'documentType' => [
                        'invoice' => [
                            'icms' => 'ICMS_TAXED',
                            'key' => (string) $xmlObject->protNFe->infProt->chNFe,
                            'series' => '4',
                            'number' => (string) $xmlObject->NFe->infNFe->ide->nNF,
                            'totalValue' => (string) $xmlObject->NFe->infNFe->total->ICMSTot->vNF,
                        ],
                    ],
                    'trackingCode' => (string) $xmlObject->protNFe->infProt->chNFe,
                    'barcode' => (string) $xmlObject->protNFe->infProt->chNFe,
                    'weightG' => round((float) $xmlObject->NFe->infNFe->transp->vol->pesoB * 1000),
                    'lengthCm' =>  (int) $xmlObject->NFe->infNFe->transp->vol->qVol,
                    'widthCm' => '30', // Valor padrão ou apropriado
                    'heightCm' => '16', // Valor padrão ou apropriado
                ],
            ]
        ];

        // $json = '{"shipFrom":{"address":{"correiosAddress":{"logradouro":"aki","cep":"89111081","cidade":"aki","uf":"ak","numero":"aki","complemento":"aki","bairro":"aki"},"instructions":"Instruções de envio do de"},"name":"MIRANTE IND. E COM. ","federalTaxId":"23966188000122"},"shipTo":{"address":{"correiosAddress":{"cep":"17065211","logradouro":"RUA IRENE PRE","uf":"SP","cidade":"BAURU"},"instructions":"Instruções "},"name":"LUCAS CRUZ","federalTaxId":"47144406833","email":"aki","phoneNumber":"aki","stateTaxId":"aki"},"pickupType":"PICKUP_TYPE_SPOT","packages":[{"freightType":"FREIGHT_TYPE_ECONOMIC","documentType":{"invoice":{"icms":"ICMS_NOT_TAXED","key":"42240223966188000122550010002676491520151416","series":"1","number":"1306","totalValue":"884"}},"weightG":100,"lengthCm":16,"widthCm":30,"heightCm":16}]}';
        //  echo json_encode($requestData);
        //    dd(json_encode($requestData));

        // Enviar a requisição para a API da Loggi
        try {
            // Enviar a requisição para a API da Loggi
            $client = new \GuzzleHttp\Client();
            $response = $client->request('POST', 'https://api.loggi.com/v1/companies/394829/shipments', [
                'json' => $requestData,
                'headers' => [
                    'accept' => 'application/json',
                    'authorization' => 'Bearer ' . $token['idToken'],
                ],
            ]);

            // Lidar com a resposta da API da Loggi conforme necessário
            $statusCode = $response->getStatusCode();
            $responseBody = $response->getBody()->getContents();
            // Exibir o corpo da resposta da API da Loggi
            //  echo $responseBody;


            $responseData = json_decode($responseBody, true);
            $trackingCode = "";
            // Verifica se a chave 'success' está presente no array retornado
            if (isset($responseData['success'])) {
                // Acessa o array de sucesso
                $successData = $responseData['success'];
                // Verifica se há pacotes na resposta
                if (isset($successData['packages']) && !empty($successData['packages'])) {
                    // Como só há um pacote, podemos acessá-lo diretamente no índice 0
                    $package = $successData['packages'][0];
                    // Verifica se o 'trackingCode' está presente no pacote
                    if (isset($package['trackingCode'])) {
                        $trackingCode = $package['trackingCode'];
                    }
                }
            }

            $transp = Carrier::whereHas('documents', function ($query) use ($documento) {
                $query->where('number', $documento);
            })->first();




            $numNota = $XmlArray['NFe']['infNFe']['ide']['nNF'];
            $serie = $XmlArray['NFe']['infNFe']['ide']['serie'];
            $ufUnidadeDestino = $transp->estado;
            $qtdVolume = $XmlArray['NFe']['infNFe']['transp']['vol']['qVol'];
            $numeroDoVolume = $XmlArray['NFe']['infNFe']['transp']['vol']['nVol'];
            $peso = $XmlArray['NFe']['infNFe']['transp']['vol']['pesoL'];
            $totalPeso = $XmlArray['NFe']['infNFe']['transp']['vol']['pesoB'];
            $chaveNf = $XmlArray['protNFe']['infProt']['chNFe'];
            $destNome = $XmlArray['NFe']['infNFe']['dest']['xNome'];
            $destCpfCnpj = isset($XmlArray['NFe']['infNFe']['dest']['CPF']) ? $XmlArray['NFe']['infNFe']['dest']['CPF'] : $XmlArray['NFe']['infNFe']['dest']['CNPJ'];
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
            $delivery->received = $dataEntrega;
            $delivery->scheduled = $dataEntrega;
            $delivery->estimated_delivery = $dataEntrega;
            $delivery->invoice = $numNota;
            $delivery->destination_state = $ufUnidadeDestino;
            $delivery->quantity_of_packages = $qtdVolume;
            $delivery->invoice_key = $chaveNf;
            $delivery->package_number = $numeroDoVolume;
            $delivery->weight = $peso;
            $delivery->external_code = $trackingCode;
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
            $delivery->serie = $serie;
            $delivery->destination_state = $destEstado;


            try {
                $delivery->save();
                $status = new StatusHistory();
                $status->status = "Arquivo Recebido";
                $status->delivery_id = $delivery->id;
                $status->save();

                echo json_encode(array("mensagem" => "sucesso"));
            } catch (Exception $e) {
                // Verifique se a mensagem de erro contém "SQLSTATE[23000]" (case-sensitive)
                if (strpos($e->getMessage(), "SQLSTATE[23000]") !== false) {
                    Error::create(['erro' => 'Nota já processada' . $numNota]);
                    exit;
                }
            }
        } catch (\GuzzleHttp\Exception\ClientException $e) {

            Error::create(['erro' => 'Error LOGGI' . $e->getMessage()]);
            // Capturar a exceção do Guzzle para obter o corpo da resposta da API da Loggi
            $response = $e->getResponse();
            if ($response) {
                $responseBody = $response->getBody()->getContents();
                // Exibir o corpo da resposta da API da Loggi
                echo $responseBody;
            } else {
                // Exibir uma mensagem de erro genérica caso não seja possível obter o corpo da resposta
                echo "Erro ao chamar a API da Loggi: " . $e->getMessage();
            }
        } catch (\Exception $e) {
            // Exibir qualquer outra exceção que possa ocorrer
            echo "Erro: na Loggi" . $e->getMessage();
        }
    }
    public function gerarPedidoAstralog($xmlContent, $dataEntrega)
    {


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
                $jsonString = json_encode($xmlObj); // Transformar o objeto em uma string JSON

                $XmlArray = json_decode($jsonString, true);
                $documento = $XmlArray['NFe']['infNFe']['transp']['transporta']['CNPJ'];


                //    dd((string)$xmlObj->NFe->infNFe->dest->CPF != "" ? (string)$xmlObj->NFe->infNFe->dest->CPF : (string)$xmlObj->NFe->infNFe->dest->CNPJ);
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
                                "nDoc" => (string)$xmlObj->NFe->infNFe->dest->CPF != "" ? (string)$xmlObj->NFe->infNFe->dest->CPF : (string)$xmlObj->NFe->infNFe->dest->CNPJ,
                                "IE" => "ISENTO",
                                "cFiscal" => 1,
                                "xNome" => (string)$xmlObj->NFe->infNFe->dest->xNome,
                                "xFant" => (string)$xmlObj->NFe->infNFe->dest->xNome,
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
                                "nDoc" => (string)$xmlObj->NFe->infNFe->dest->CPF != "" ? (string)$xmlObj->NFe->infNFe->dest->CPF : (string)$xmlObj->NFe->infNFe->dest->CNPJ,
                                "IE" => "ISENTO",
                                "cFiscal" => 1,
                                "xNome" => (string)$xmlObj->NFe->infNFe->dest->xNome,
                                "xFant" => (string)$xmlObj->NFe->infNFe->dest->xNome,
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
                                    "xEsp" => "string",
                                    "xNat" => "string"
                                ),
                            ),
                        )
                    )
                );

                //  echo json_encode($data);
                //  exit;
                //  dd($data);






                $headers = [
                    "Authorization" => "Bearer  " . $accessKey,
                    "Content-Type" => "application/json",
                    "accept" => "application/json"
                ];


                try {
                    $response = $client->post("https://grupoastrolog.brudam.com.br/api/v1/operacional/emissao/minuta", [
                        'headers' => $headers,
                        'json' => $data
                    ]);




                    echo json_encode(array('mensagem' => 'sucesso'));
                } catch (RequestException $e) {
                    if ($e->getResponse()->getReasonPhrase() == "Internal Server Error") {
                        Error::create(['erro' => 'Erro servidor interno Astralog' . $e->getMessage()]);
                    }
                }



                $transp = Carrier::whereHas('documents', function ($query) use ($documento) {
                    $query->where('number', $documento);
                })->first();




                $numNota = $XmlArray['NFe']['infNFe']['ide']['nNF'];
                $serie = $XmlArray['NFe']['infNFe']['ide']['serie'];
                $ufUnidadeDestino = $transp->estado;
                $qtdVolume = $XmlArray['NFe']['infNFe']['transp']['vol']['qVol'];
                $numeroDoVolume = $XmlArray['NFe']['infNFe']['transp']['vol']['nVol'];
                $peso = $XmlArray['NFe']['infNFe']['transp']['vol']['pesoL'];
                $totalPeso = $XmlArray['NFe']['infNFe']['transp']['vol']['pesoB'];
                $chaveNf = $XmlArray['protNFe']['infProt']['chNFe'];
                $destNome = $XmlArray['NFe']['infNFe']['dest']['xNome'];
                $destCpfCnpj = isset($XmlArray['NFe']['infNFe']['dest']['CPF']) ? $XmlArray['NFe']['infNFe']['dest']['CPF'] : $XmlArray['NFe']['infNFe']['dest']['CNPJ'];
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
                $delivery->received = $dataEntrega;
                $delivery->scheduled = $dataEntrega;
                $delivery->estimated_delivery = $dataEntrega;
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
                $delivery->serie = $serie;
                $delivery->destination_state = $destEstado;


                try {
                    $delivery->save();
                    $status = new StatusHistory();
                    $status->status = "Arquivo Recebido";
                    $status->delivery_id = $delivery->id;
                    $status->save();

                    echo json_encode(array("mensagem" => "sucesso"));
                } catch (Exception $e) {
                    // Verifique se a mensagem de erro contém "SQLSTATE[23000]" (case-sensitive)
                    if (strpos($e->getMessage(), "SQLSTATE[23000]") !== false) {
                        Error::create(['erro' => 'Nota já processada' . $numNota]);
                        exit;
                    }
                }
            } else {
                echo "A chave 'access_key' não foi encontrada na resposta.\n";
            }
        } else {

            echo "A solicitação não foi bem-sucedida.\n";
        }
    }
    public function gerarPedidoGFL($xmlContent, $dataEntrega)
    {
        // URL do endpoint

        $xmlObject = simplexml_load_string($xmlContent); // Transformar o XML em um objeto SimpleXMLElement
        $jsonString = json_encode($xmlObject); // Transformar o objeto em uma string JSON
        $XmlArray = json_decode($jsonString, true);
        $documento = $XmlArray['NFe']['infNFe']['transp']['transporta']['CNPJ'];


        // Chave de acesso fornecida pelo transportador
        $chaveAcesso = 'xP7aTUnqZe';

        $exeternalCode = time() . mt_rand(1000, 9999);
        // Extrair os dados relevantes do XML
        $cnpjEmbarcadorOrigem = (string) $xmlObject->NFe->infNFe->emit->CNPJ;
        $idSolicitacaoInterno = $exeternalCode; // Você pode definir o ID de solicitação interno conforme necessário



        $endpointUrl = 'https://gflapi.sinclog.app.br/Api/Solicitacoes/RegistrarNovaSolicitacao';
        try {
            $data = [
                "cnpjEmbarcadorOrigem" => $cnpjEmbarcadorOrigem,
                "listaSolicitacoes" => [
                    [
                        "idSolicitacaoInterno" => $idSolicitacaoInterno,
                        "idServico" => 4,
                        "flagLiberacaoEmbarcador" => null,
                        "dtPrazoInicio" => null,
                        "dtPrazoFim" => null,
                        "TomadorServico" => [
                            "cpf" => null,
                            "cnpj" => $XmlArray['NFe']['infNFe']['emit']['CNPJ'],
                            "inscricaoEstadual" => null,
                            "nome" => null,
                            "razaoSocial" => $XmlArray['NFe']['infNFe']['emit']['xNome'],
                            "telefone" => null,
                            "email" => null,
                            "Endereco" => null
                        ],
                        "Remetente" => [
                            "cpf" => null,
                            "cnpj" => $XmlArray['NFe']['infNFe']['emit']['CNPJ'],
                            "inscricaoEstadual" => null,
                            "nome" => $XmlArray['NFe']['infNFe']['emit']['xNome'],
                            "razaoSocial" => null,
                            "telefone" => null,
                            "email" => null,
                            "Endereco" => [
                                "cep" => $XmlArray['NFe']['infNFe']['emit']['enderEmit']['CEP'],
                                "logradouro" => $XmlArray['NFe']['infNFe']['emit']['enderEmit']['xLgr'],
                                "numero" => $XmlArray['NFe']['infNFe']['emit']['enderEmit']['nro'],
                                "pontoReferencia" => null,
                                "bairro" => $XmlArray['NFe']['infNFe']['emit']['enderEmit']['xBairro'],
                                "nomeCidade" => $XmlArray['NFe']['infNFe']['emit']['enderEmit']['xMun'],
                                "siglaEstado" => $XmlArray['NFe']['infNFe']['emit']['enderEmit']['UF'],
                                "idCidadeIBGE" => null
                            ]
                        ],
                        "Destinatario" => [
                            "cpf" => empty($XmlArray['NFe']['infNFe']['dest']['CPF']) ? null : $XmlArray['NFe']['infNFe']['dest']['CPF'],
                            "cnpj" => empty($XmlArray['NFe']['infNFe']['dest']['CNPJ']) ? null : $XmlArray['NFe']['infNFe']['dest']['CNPJ'],
                            "nome" => $XmlArray['NFe']['infNFe']['dest']['xNome'],
                            "Endereco" => [
                                "cep" => $XmlArray['NFe']['infNFe']['dest']['enderDest']['CEP'],
                                "logradouro" => $XmlArray['NFe']['infNFe']['dest']['enderDest']['xLgr'],
                                "numero" => $XmlArray['NFe']['infNFe']['dest']['enderDest']['nro'],
                                "pontoReferencia" => null,
                                "bairro" => $XmlArray['NFe']['infNFe']['dest']['enderDest']['xBairro'],
                                "nomeCidade" => $XmlArray['NFe']['infNFe']['dest']['enderDest']['xMun'],
                                "siglaEstado" => $XmlArray['NFe']['infNFe']['dest']['enderDest']['UF'],
                                "idCidadeIBGE" => null
                            ]
                        ],
                        "Expedidor" => [
                            "cpf" => null,
                            "cnpj" => $XmlArray['NFe']['infNFe']['emit']['CNPJ'],
                            "inscricaoEstadual" => null,
                            "nome" => $XmlArray['NFe']['infNFe']['emit']['xNome'],
                            "razaoSocial" => null,
                            "telefone" => null,
                            "email" => null,
                            "Endereco" => [
                                "cep" => $XmlArray['NFe']['infNFe']['emit']['enderEmit']['CEP'],
                                "logradouro" => $XmlArray['NFe']['infNFe']['emit']['enderEmit']['xLgr'],
                                "numero" => $XmlArray['NFe']['infNFe']['emit']['enderEmit']['nro'],
                                "pontoReferencia" => null,
                                "bairro" => $XmlArray['NFe']['infNFe']['emit']['enderEmit']['xBairro'],
                                "nomeCidade" => $XmlArray['NFe']['infNFe']['emit']['enderEmit']['xMun'],
                                "siglaEstado" => $XmlArray['NFe']['infNFe']['emit']['enderEmit']['UF'],
                                "idCidadeIBGE" => null
                            ]
                        ],
                        "LogisticaReversa" => null,
                        "DadosAgendamento" => null,
                        "listaOperacoes" => [
                            [
                                "nroNotaFiscal" => $XmlArray['NFe']['infNFe']['ide']['nNF'],
                                "serieNotaFiscal" => $XmlArray['NFe']['infNFe']['ide']['serie'],
                                "dtEmissaoNotaFiscal" => $XmlArray['NFe']['infNFe']['ide']['dhEmi'],
                                "chaveNotaFiscal" => $XmlArray['protNFe']['infProt']['chNFe'],
                                "nroCarga" => $XmlArray['NFe']['infNFe']['transp']['vol']['qVol'],
                                "nroPedido" => $XmlArray['NFe']['infNFe']['ide']['nNF'],
                                "nroEntrega" => $XmlArray['NFe']['infNFe']['ide']['nNF'],
                                "qtdeVolumes" => $XmlArray['NFe']['infNFe']['transp']['vol']['qVol'],
                                "qtdeItens" => count($XmlArray['NFe']['infNFe']['det']),
                                "pesoTotal" => $XmlArray['NFe']['infNFe']['transp']['vol']['pesoB'],
                                "valorMercadoria" => $XmlArray['NFe']['infNFe']['total']['ICMSTot']['vProd'],
                                "valorICMS" => $XmlArray['NFe']['infNFe']['total']['ICMSTot']['vICMS'],
                                "listaVolumes" => [
                                    [
                                        "idVolume" => null,
                                        "nroEtiqueta" => $XmlArray['NFe']['infNFe']['transp']['vol']['qVol'],
                                        "altura" => $XmlArray['NFe']['infNFe']['transp']['vol']['qVol'],
                                        "largura" => $XmlArray['NFe']['infNFe']['transp']['vol']['qVol'],
                                        "comprimento" => $XmlArray['NFe']['infNFe']['transp']['vol']['qVol'],
                                        "descricaoVolumes" => $XmlArray['NFe']['infNFe']['transp']['vol']['qVol']
                                    ]
                                ],

                            ]
                        ],
                        "linkCTe" => null,
                        "base64CTe" => null,
                        "xmlCTeAnterior" => "",
                        "chaveCTeAnterior" => null
                    ]
                ]
            ];
            $transp = Carrier::whereHas('documents', function ($query) use ($documento) {
                $query->where('number', $documento);
            })->first();


            // dd($documento);

            $numNota = $XmlArray['NFe']['infNFe']['ide']['nNF'];
            $serie = $XmlArray['NFe']['infNFe']['ide']['serie'];
            $ufUnidadeDestino = $transp->state;
            $qtdVolume = $XmlArray['NFe']['infNFe']['transp']['vol']['qVol'];
            $numeroDoVolume = $XmlArray['NFe']['infNFe']['transp']['vol']['nVol'];
            $peso = $XmlArray['NFe']['infNFe']['transp']['vol']['pesoL'];
            $totalPeso = $XmlArray['NFe']['infNFe']['transp']['vol']['pesoB'];
            $chaveNf = $XmlArray['protNFe']['infProt']['chNFe'];
            $destNome = $XmlArray['NFe']['infNFe']['dest']['xNome'];
            $destCpfCnpj = isset($XmlArray['NFe']['infNFe']['dest']['CPF']) ? $XmlArray['NFe']['infNFe']['dest']['CPF'] : $XmlArray['NFe']['infNFe']['dest']['CNPJ'];
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
            $delivery->received = $dataEntrega;
            $delivery->scheduled = $dataEntrega;
            $delivery->estimated_delivery = $dataEntrega;
            $delivery->invoice = $numNota;
            $delivery->destination_state = $ufUnidadeDestino;
            $delivery->quantity_of_packages = $qtdVolume;
            $delivery->invoice_key = $chaveNf;
            $delivery->package_number = $numeroDoVolume;
            $delivery->weight = $peso;
            $delivery->external_code = $exeternalCode;
            $delivery->total_weight = $totalPeso;
            $delivery->destination_name = $destNome;
            $delivery->destination_tax_id = $destCpfCnpj;
            $delivery->destination_phone = $destTelefone;
            $delivery->destination_email = $destEmail;
            $delivery->destination_zip_code = $destCep;
            $delivery->destination_address = $destLogradouro;
            $delivery->destination_number = $destNumero;
            $delivery->destination_neighborhood = $destBairro;
            $delivery->serie = $serie;
            $delivery->destination_city = $destCidade;
            $delivery->destination_state = $destEstado;



            $delivery->save();
            $status = new StatusHistory();
            $status->status = "Arquivo Recebido";
            $status->delivery_id = $delivery->id;
            $status->save();
            echo json_encode(array('mensagem' => 'sucesso'));
        } catch (Exception $e) {
            // Verifique se a mensagem de erro contém "SQLSTATE[23000]" (case-sensitive)
            if (strpos($e->getMessage(), "SQLSTATE[23000]") !== false) {
                Error::create(['erro' => 'Nota já processada' . $numNota]);
                exit;
            }
            Error::create(['erro' => 'Error GFL : ' . $e->getMessage()]);
        }

        try {
            // Criação de uma instância do cliente Guzzle
            $client = new Client();

            // Fazendo a requisição POST com o cabeçalho de autorização e os dados da solicitação
            $response = $client->post($endpointUrl, [
                'headers' => [
                    'Authorization' => 'Basic ' . $chaveAcesso,
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode($data),
            ]);

            // Obtendo o corpo da resposta como string
            $responseBody = $response->getBody()->getContents();
        } catch (Exception $e) {
            Error::create(['erro' => 'Erro GFL ' . $e->getMessage()]);
        }
    }
    public function gerarPedidoDBA($xmlContent, $dataEntrega)
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



            $doc = "";

            if(isset($XmlArray['NFe']['infNFe']['dest']['CPF'])){
                $doc =  $XmlArray['NFe']['infNFe']['dest']['CNPJ'];
            }

            $numNota = $XmlArray['NFe']['infNFe']['ide']['nNF'];
            $serie = $XmlArray['NFe']['infNFe']['ide']['serie'];
            $ufUnidadeDestino = $transp->estado;
            $qtdVolume = $XmlArray['NFe']['infNFe']['transp']['vol']['qVol'];
            $numeroDoVolume = $XmlArray['NFe']['infNFe']['transp']['vol']['nVol'];
            $peso = $XmlArray['NFe']['infNFe']['transp']['vol']['pesoL'];
            $totalPeso = $XmlArray['NFe']['infNFe']['transp']['vol']['pesoB'];
            $chaveNf = $XmlArray['protNFe']['infProt']['chNFe'];
            $destNome = $XmlArray['NFe']['infNFe']['dest']['xNome'];
            $destCpfCnpj = $doc;
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
            $delivery->received = $dataEntrega;
            $delivery->scheduled = $dataEntrega;
            $delivery->estimated_delivery = $dataEntrega;
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
            $delivery->serie = $serie;
            $delivery->destination_city = $destCidade;
            $delivery->destination_state = $destEstado;


            try {
                $delivery->save();
                $status = new StatusHistory();
                $status->status = "Arquivo Recebido";
                $status->delivery_id = $delivery->id;
                $status->save();
                echo json_encode(array('mensagem' => 'sucesso'));
            } catch (Exception $e) {
                // Verifique se a mensagem de erro contém "SQLSTATE[23000]" (case-sensitive)
                echo "Erro: na DBA " . $e->getMessage();
                if (strpos($e->getMessage(), "SQLSTATE[23000]") !== false) {
                    Error::create(['erro' => 'Nota já processada' . $numNota]);
                    exit;
                }
            }
        }
    }
    public function gerarPedidoMesh($xmlContent, $dataEntrega)
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
        $serie = $XmlArray['NFe']['infNFe']['ide']['serie'];
        $ufUnidadeDestino = $transp->estado;
        $qtdVolume = $XmlArray['NFe']['infNFe']['transp']['vol']['qVol'];
        $numeroDoVolume = $XmlArray['NFe']['infNFe']['transp']['vol']['nVol'];
        $peso = $XmlArray['NFe']['infNFe']['transp']['vol']['pesoL'];
        $totalPeso = $XmlArray['NFe']['infNFe']['transp']['vol']['pesoB'];
        $chaveNf = $XmlArray['protNFe']['infProt']['chNFe'];
        $destNome = $XmlArray['NFe']['infNFe']['dest']['xNome'];
        $destCpfCnpj = $XmlArray['NFe']['infNFe']['dest']['CPF'] ?: $XmlArray['NFe']['infNFe']['dest']['CNPJ'];
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
            ], //37744796000105
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
        $delivery->received = $dataEntrega;
        $delivery->scheduled = $dataEntrega;
        $delivery->estimated_delivery = $dataEntrega;
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
        $delivery->serie = $serie;
        $delivery->destination_state = $destEstado;


        try {
            $delivery->save();
            $status = new StatusHistory();
            $status->status = "Arquivo Recebido";
            $status->delivery_id = $delivery->id;
            $status->save();
            echo json_encode(array('mensagem' => 'sucesso'));
        } catch (Exception $e) {
            echo "Erro: na MESH" . $e->getMessage();
            // Verifique se a mensagem de erro contém "SQLSTATE[23000]" (case-sensitive)
            if (strpos($e->getMessage(), "SQLSTATE[23000]") !== false) {
                Error::create(['erro' => 'Nota já processada' . $numNota]);
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

    public function gerarPedidoJT($xmlContent, $dataEntrega)
    {
        $client = new Client();
        $dateTime = new DateTime();
        $formattedDate = $dateTime->format("Y-m-d");

        $xmlContent = file_get_contents('php://input');
        $xmlObj = simplexml_load_string($xmlContent); // Transformar o XML em um objeto SimpleXMLElement
        $jsonString = json_encode($xmlObj); // Transformar o objeto em uma string JSON

        $XmlArray = json_decode($jsonString, true);
        $documento = $XmlArray['NFe']['infNFe']['transp']['transporta']['CNPJ'];
        $chaveNf = $XmlArray['protNFe']['infProt']['chNFe'];

        $entrega = Delivery::where('invoice_key', $chaveNf)->first();

        if ($entrega) {

            //Definindo parâmetros
            $privateKey = env('PRIVATE_KEY_JT');
            $apiAccount = env('API_ACCOUNT_JT');

            $totalQuantity = 0;
            $invoiceMoney = 0;

            $weightL = (float)$xmlObj->NFe->infNFe->transp->vol->pesoL;


            foreach ($xmlObj->NFe->infNFe->det as $item) {
                $totalQuantity += (float)$item->prod->qCom;
                $invoiceMoney += (float)$item->prod->vProd;
            }

            $invoiceMoney = number_format($invoiceMoney, 2, '.', '');

            // dd($headerSignature);
            //Montando o JSON do envio
            $pedido = [
                "customerCode" => 'J0086026981',
                "digest" => '1RSteqUoNxyCYPg2yHwAng==',
                "txlogisticId" => (string)$xmlObj->NFe->infNFe->ide->nNF,
                "expressType" => "EZ", // Você pode definir este valor conforme necessário
                "orderType" => "1", // Você pode definir este valor conforme necessário
                "serviceType" => "02", // Você pode definir este valor conforme necessário
                "deliveryType" => "03", // Você pode definir este valor conforme necessário
                "sender" => [
                    "name" => (string)$xmlObj->NFe->infNFe->emit->xNome,
                    "company" => (string)$xmlObj->NFe->infNFe->emit->xFant,
                    "postCode" => (string)$xmlObj->NFe->infNFe->emit->enderEmit->CEP,
                    "mailBox" => "no-email@mail.com.br", // Preencha conforme necessário
                    "taxNumber" => (string)$xmlObj->NFe->infNFe->emit->CNPJ,
                    "mobile" => (string)$xmlObj->NFe->infNFe->emit->enderEmit->fone,
                    "phone" => (string)$xmlObj->NFe->infNFe->emit->enderEmit->fone,
                    "prov" => (string)$xmlObj->NFe->infNFe->emit->enderEmit->UF,
                    "city" => (string)$xmlObj->NFe->infNFe->emit->enderEmit->xMun,
                    "street" => (string)$xmlObj->NFe->infNFe->emit->enderEmit->xLgr,
                    "streetNumber" => (string)$xmlObj->NFe->infNFe->emit->enderEmit->nro,
                    "address" => (string)$xmlObj->NFe->infNFe->emit->enderEmit->xLgr . ', ' . (string)$xmlObj->NFe->infNFe->emit->enderEmit->nro,
                    "areaCode" => "", // Preencha conforme necessário
                    "ieNumber" => (string)$xmlObj->NFe->infNFe->emit->IE,
                    "area" => (string)$xmlObj->NFe->infNFe->emit->enderEmit->xBairro,
                ],
                "receiver" => [
                    "name" => (string)$xmlObj->NFe->infNFe->dest->xNome,
                    "postCode" => (string)$xmlObj->NFe->infNFe->dest->enderDest->CEP,
                    "mailBox" => "no-email@mail.com.br", // Preencha conforme necessário
                    "taxNumber" => (string)$xmlObj->NFe->infNFe->dest->CPF ?: (string)$xmlObj->NFe->infNFe->dest->CNPJ,
                    "mobile" => (string)$xmlObj->NFe->infNFe->dest->enderDest->fone,
                    "phone" => (string)$xmlObj->NFe->infNFe->dest->enderDest->fone,
                    "prov" => (string)$xmlObj->NFe->infNFe->dest->enderDest->UF,
                    "city" => (string)$xmlObj->NFe->infNFe->dest->enderDest->xMun,
                    "street" => (string)$xmlObj->NFe->infNFe->dest->enderDest->xLgr,
                    "streetNumber" => (string)$xmlObj->NFe->infNFe->dest->enderDest->nro,
                    "address" => (string)$xmlObj->NFe->infNFe->dest->enderDest->xLgr . ', ' . (string)$xmlObj->NFe->infNFe->dest->enderDest->nro,
                    "areaCode" => "", // Preencha conforme necessário
                    "ieNumber" => "", // Preencha conforme necessário
                    "area" => (string)$xmlObj->NFe->infNFe->dest->enderDest->xBairro,
                ],
                "translate" => [
                    "name" => (string)$xmlObj->NFe->infNFe->emit->xNome,
                    "company" => (string)$xmlObj->NFe->infNFe->emit->xFant,
                    "postCode" => (string)$xmlObj->NFe->infNFe->emit->enderEmit->CEP,
                    "mailBox" => "no-email@mail.com.br", // Preencha conforme necessário
                    "taxNumber" => (string)$xmlObj->NFe->infNFe->emit->CNPJ,
                    "mobile" => (string)$xmlObj->NFe->infNFe->emit->enderEmit->fone,
                    "phone" => (string)$xmlObj->NFe->infNFe->emit->enderEmit->fone,
                    "prov" => (string)$xmlObj->NFe->infNFe->emit->enderEmit->UF,
                    "city" => (string)$xmlObj->NFe->infNFe->emit->enderEmit->xMun,
                    "street" => (string)$xmlObj->NFe->infNFe->emit->enderEmit->xLgr,
                    "streetNumber" => (string)$xmlObj->NFe->infNFe->emit->enderEmit->nro,
                    "address" => (string)$xmlObj->NFe->infNFe->emit->enderEmit->xLgr . ', ' . (string)$xmlObj->NFe->infNFe->emit->enderEmit->nro,
                    "areaCode" => "", // Preencha conforme necessário
                    "ieNumber" => (string)$xmlObj->NFe->infNFe->emit->IE,
                    "area" => (string)$xmlObj->NFe->infNFe->emit->enderEmit->xBairro,
                ],
                "goodsType" => "bm000008",
                "weight" => $weightL,
                "totalQuantity" => $totalQuantity,
                "invoiceMoney" => $invoiceMoney,
                "remark" => "CTE emitido para validacao conforme solicitado pelo cliente. Valor de frete informado para fins de referencia. Documento emitido por ME optante pelo simples nacional, nao gera direito a credito de ISS e IPI",
                // Continue a adicionar os demais campos do array $pedido
            ];



            // Informações dos Itens do Pedido
            $pedido['items'] = [];
            foreach ($xmlObj->NFe->infNFe->det as $item) {
                $pedido['items'][] = [
                    "sku" => (string)$item->prod->cProd,
                    "description" => (string)$item->prod->xProd,
                    "quantity" => (string)$item->prod->qCom,
                    "unitPrice" => (string)$item->prod->vUnCom,
                    "totalPrice" => (string)$item->prod->vProd,
                ];
            }



            $pedido += [
                "invoiceNumber" => (string)$xmlObj->NFe->infNFe->ide->nNF,
                "invoiceSerialNumber" => (string)$xmlObj->NFe->infNFe->ide->serie,
                "invoiceMoney" => (string)$xmlObj->NFe->infNFe->total->ICMSTot->vNF,
                "taxCode" => (string)$xmlObj->NFe->infNFe->emit->CNPJ,
                "invoiceAccessKey" => (string)$xmlObj->NFe->infNFe->attributes()['Id'],
                "invoiceIssueDate" => (string)$xmlObj->NFe->infNFe->ide->dhEmi,
            ];


            // dd($pedido);
            $pedido = json_encode($pedido);


            //Codificando o pedido para envio
            $req_pedido = rawurlencode($pedido);



            //Montando o digest do header
            $headerDigest = base64_encode(md5($pedido . $privateKey, true));
          
            try {

                //Instanciando e enviando a requisição
                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => 'https://openapi.jtjms-br.com/webopenplatformapi/api/order/addOrder',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => 'bizContent=' . $req_pedido,
                    CURLOPT_HTTPHEADER => array(
                        'timestamp: ' . time(),
                        'apiAccount:' . $apiAccount,
                        'digest:' . $headerDigest,
                        'Content-Type: application/x-www-form-urlencoded'
                    ),
                ));

                //Enviando a requisição e gravando a resposta
                $response = curl_exec($curl);
                $responseArray =  json_decode($response, true);

                //Fechando a requisição
                curl_close($curl);

                $billcode = $responseArray['data']['orderList'][0]['billCode'];
                //Exibindo a resposta
                echo '<br><br>' . $response;


                echo json_encode(array('mensagem' => 'sucesso'));
            } catch (RequestException $e) {
                echo "Erro: na J&T" . $e->getMessage();
                if ($e->getResponse()->getReasonPhrase() == "Internal Server Error") {
                    Error::create(['erro' => 'Erro servidor interno J&T' . $e->getMessage()]);
                }
            }
            


            $transp = Carrier::whereHas('documents', function ($query) use ($documento) {
                $query->where('number', $documento);
            })->first();




            $numNota = $XmlArray['NFe']['infNFe']['ide']['nNF'];
            $serie = $XmlArray['NFe']['infNFe']['ide']['serie'];
            $ufUnidadeDestino = $transp->estado;
            $qtdVolume = $XmlArray['NFe']['infNFe']['transp']['vol']['qVol'];
            $numeroDoVolume = $XmlArray['NFe']['infNFe']['transp']['vol']['nVol'];
            $peso = $XmlArray['NFe']['infNFe']['transp']['vol']['pesoL'];
            $totalPeso = $XmlArray['NFe']['infNFe']['transp']['vol']['pesoB'];
            $chaveNf = $XmlArray['protNFe']['infProt']['chNFe'];
            $destNome = $XmlArray['NFe']['infNFe']['dest']['xNome'];
            $destCpfCnpj = isset($XmlArray['NFe']['infNFe']['dest']['CPF']) ? $XmlArray['NFe']['infNFe']['dest']['CPF'] : $XmlArray['NFe']['infNFe']['dest']['CNPJ'];
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
            $delivery->received = $dataEntrega;
            $delivery->scheduled = $dataEntrega;
            $delivery->estimated_delivery = $dataEntrega;
            $delivery->invoice = $numNota;
            $delivery->external_code = $billcode;
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
            $delivery->serie = $serie;
            $delivery->destination_state = $destEstado;


            try {
                $delivery->save();
                $status = new StatusHistory();
                $status->status = "Arquivo Recebido";
                $status->delivery_id = $delivery->id;
                $status->save();

                echo json_encode(array("mensagem" => "sucesso"));
            } catch (Exception $e) {
                echo "Erro: na TJ" . $e->getMessage();
                // Verifique se a mensagem de erro contém "SQLSTATE[23000]" (case-sensitive)
                if (strpos($e->getMessage(), "SQLSTATE[23000]") !== false) {
                    Error::create(['erro' => 'Nota já processada' . $numNota]);
                    exit;
                }
            }
        } 
    }

    public function authGfl()
    {
        // Crie uma instância do cliente Guzzle
        $clientA = new Client();

        // Defina a URL do endpoint
        $urlA = 'https://grupoastrolog.brudam.com.br/api/v1/acesso/auth/login';

        // Defina os dados que serão enviados no corpo da solicitação
        $data = [
            'usuario' => env('ASTROLOG_USER'),
            'senha' => env('ASTROLOG_PASSWORD'),
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
    public static function authLoggi()
    {
        // Crie uma instância do cliente Guzzle
        $client = new Client();


        $response = $client->request('POST', 'https://api.loggi.com/oauth2/token', [
            'body' => json_encode([
                'client_secret' => env('LOGGI_CLIENT_SECRET'),
                'client_id' => env('LOGGI_CLIENT_ID'),
            ]),
            'headers' => [
                'accept' => 'application/json',
                'content-type' => 'application/json',
            ],
        ]);

        // dd(json_decode($response->getBody(), true));
        return json_decode($response->getBody(), true);
    }
}
