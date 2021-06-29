<?php

require_once(realpath(__DIR__ . '/..').'/vendor/autoload.php');

use NFePHP\NFe\Tools;
use NFePHP\NFe\Make;
use NFePHP\Common\Certificate;
use NFePHP\Common\Soap\SoapFake;

class NFCe {

    private $tec_settings;

    function __construct()
    {
        $this->tec_settings = new configNFCe();
        // $this->cliente_model = new ClienteModel();
        // $this->venda_model = new VendaModel();
        // $this->produtos_da_venda_model = new ProdutoDaVendaModel();
        // $this->nfe_model = new NFeModel();
    }


public function indexAction()
{
// try {
//     $xmlAssinado = $tools->signNFe($xml);
// } catch (\Exception $e) {
//     $dados_da_nfce['Erro'] = $e->getMessage();
// } 

$dados = (new configNFCe)->get_all();
$clientes = (new configNFCe)->get_name();
$pedidos = (new configNFCe)->get_product();
$vendas = (new configNFCe)->get_sales();
$nfce = (new configNFCe)->get_nfce();

// var_dump($produtos);exit;

    $make = new Make();

    //infNFe OBRIGATÓRIA
    // TAG INFORMAÇÕES
    $inf = new \stdClass();
    $inf->Id = ''; // Id de 44 digitos, se não for informado, será gerado automaticamente
    $inf->versao = '4.00'; // versão NFCe
    $infNFe = $make->taginfNFe($inf);

    //ide OBRIGATÓRIA
    // TAG IDE
    $ide = new \stdClass(); 
    $ide->cUF = $dados[0]->codigo_uf;
    $ide->cNF = rand(1, 99999999);
    $ide->natOp = $nfce[0]->natOp;
    $ide->mod = 65;
    $ide->serie = $nfce[0]->serie;
    $ide->nNF = $dados[0]->nnf;
    $ide->dhEmi = (new \DateTime())->format('Y-m-d\TH:i:sP');
    $ide->dhSaiEnt = null;
    $ide->tpNF = 1;
    $ide->idDest = 1;
    $ide->cMunFG = $dados[0]->cod_municipio_ibge;
    $ide->tpImp = 1;
    $ide->tpEmis = 1;
    $ide->cDV = 0;
    $ide->tpAmb = $nfce[0]->ambienteNFe;
    $ide->finNFe = 1;
    $ide->indFinal = 1;
    $ide->indPres = 1;
    $ide->procEmi = 0;
    $ide->verProc = '4.13';
    $ide->dhCont = null;
    $ide->xJust = null;
    $ide = $make->tagIde($ide);

    //emit OBRIGATÓRIA
    //  TAG EMITENTE
    $emitente = new \stdClass();
    $emitente->xNome = $dados[0]->razao_social;
    $emitente->xFant = $dados[0]->nome_fantasia;
    $emitente->IE = $dados[0]->ie;
    $emitente->CRT = $dados[0]->crt;
    $emitente->CNPJ = $dados[0]->cnpj;
    //$emitente->CPF = '12345678901'; //NÃO PASSE TAGS QUE NÃO EXISTEM NO CASO
    $emit = $make->tagemit($emitente);

    //enderEmit OBRIGATÓRIA
    // TAG ENDEREÇO DO EMITENTE
    $endereco_emitente = new \stdClass();
    $endereco_emitente->xLgr = $dados[0]->logradouro;
    $endereco_emitente->nro = $dados[0]->numero;
    $endereco_emitente->xBairro = $dados[0]->bairro;
    $endereco_emitente->cMun = $dados[0]->cod_municipio_ibge;
    $endereco_emitente->xMun = $dados[0]->nome_municipio;
    $endereco_emitente->UF = $dados[0]->uf;
    $endereco_emitente->CEP = $dados[0]->cep;
    $endereco_emitente->cPais = $dados[0]->cod_pais;
    $endereco_emitente->xPais = $dados[0]->nome_pais;
    $endereco_emitente->fone = $dados[0]->tel;
    $ret = $make->tagenderemit($endereco_emitente);

    //dest OPCIONAL
    // TAG DESTINATÁRIO
    // $destinatario = new \stdClass();
    // $destinatario->xNome = $clientes[20]->name;
    // // $destinatario->CNPJ = '01234123456789';
    // $destinatario->CPF = $clientes[20]->cf1;
    // //$destinatario->idEstrangeiro = 'AB1234';
    // $destinatario->indIEDest = 9;
    // //$destinatario->IE = '';
    // //$destinatario->ISUF = '12345679';
    // //$destinatario->IM = 'XYZ6543212';
    // $destinatario->email = $clientes[20]->email;
    // $dest = $make->tagdest($destinatario);

    //enderDest OPCIONAL
    // // TAG ENDEREÇO DO DESTINATÁRIO
    // $endereco_destinatario = new \stdClass();
    // $endereco_destinatario->xLgr = $clientes[0]->endereco;
    // $endereco_destinatario->nro = '458';
    // $endereco_destinatario->xCpl = null;
    // $endereco_destinatario->xBairro = 'CENTRO';
    // $endereco_destinatario->cMun = 1400100;
    // $endereco_destinatario->xMun = 'Boa Vista';
    // $endereco_destinatario->UF = 'RR';
    // $endereco_destinatario->CEP = '69301088';
    // $endereco_destinatario->cPais = 1058;
    // $endereco_destinatario->xPais = 'Brasil';
    // $endereco_destinatario->fone = '1111111111';
    // $ret = $make->tagenderdest($endereco_destinatario);


    // if($pedidos == 1)
    // {
    //     $pedidos = $this->pedidos->findAll();
    // }
    // else if($pedidos == 2)
    // {
    //     $pedidos = $this->vendas->where('id_venda', $id)->find();
    // }
    
    //prod OBRIGATÓRIA
    // TAG PRODUTOS
    $produtos = new \stdClass();
    $produtos->item = 1;
    $produtos->cProd = '1111';
    $produtos->cEAN = "SEM GTIN";
    $produtos->xProd = 'TESTE';
    $produtos->NCM = 61052000;
    //$produtos->cBenef = 'ab222222';
    $produtos->EXTIPI = '';
    $produtos->CFOP = 5101;
    $produtos->uCom = 'UNID';
    $produtos->qCom = 1;
    $produtos->vUnCom = 100.00;
    $produtos->vProd = 100.00;
    $produtos->cEANTrib = "SEM GTIN"; //'6361425485451';
    $produtos->uTrib = 'UNID';
    $produtos->qTrib = 1;
    $produtos->vUnTrib = 100.00;
    //$produtos->vFrete = 0.00;
    //$produtos->vSeg = 0;
    //$produtos->vDesc = 0;
    //$produtos->vOutro = 0;
    $produtos->indTot = 1;
    //$produtos->xPed = '12345';
    //$produtos->nItemPed = 1;
    //$produtos->nFCI = '12345678-1234-1234-1234-123456789012';
    $prod = $make->tagprod($produtos);

    $tag = new \stdClass();
    $tag->item = 1;
    $tag->infAdProd = 'TESTE';
    $make->taginfAdProd($tag);

    //Imposto 
    // TAG ICMS
    $icms = new stdClass();
    $icms->item = 1; //item da NFe
    $icms->vTotTrib = 25.00;
    $make->tagimposto($icms);

    // TAG ICMSSN
    $icmssn = new stdClass();
    $icmssn->item = 1; //item da NFe
    $icmssn->orig = 0;
    $icmssn->CSOSN = '102';
    $icmssn->pCredSN = 0.00;
    $icmssn->vCredICMSSN = 0.00;
    $icmssn->modBCST = null;
    $icmssn->pMVAST = null;
    $icmssn->pRedBCST = null;
    $icmssn->vBCST = null;
    $icmssn->pICMSST = null;
    $icmssn->vICMSST = null;
    $icmssn->vBCFCPST = null; //incluso no layout 4.00
    $icmssn->pFCPST = null; //incluso no layout 4.00
    $icmssn->vFCPST = null; //incluso no layout 4.00
    $icmssn->vBCSTRet = null;
    $icmssn->pST = null;
    $icmssn->vICMSSTRet = null;
    $icmssn->vBCFCPSTRet = null; //incluso no layout 4.00
    $icmssn->pFCPSTRet = null; //incluso no layout 4.00
    $icmssn->vFCPSTRet = null; //incluso no layout 4.00
    $icmssn->modBC = null;
    $icmssn->vBC = null;
    $icmssn->pRedBC = null;
    $icmssn->pICMS = null;
    $icmssn->vICMS = null;
    $icmssn->pRedBCEfet = null;
    $icmssn->vBCEfet = null;
    $icmssn->pICMSEfet = null;
    $icmssn->vICMSEfet = null;
    $icmssn->vICMSSubstituto = null;
    $make->tagICMSSN($icmssn);

    //PIS
    // TAG PIS
    $pis = new stdClass();
    $pis->item = 1; //item da NFe
    $pis->CST = '99';
    //$pis->vBC = 1200;
    //$pis->pPIS = 0;
    $pis->vPIS = 0.00;
    $pis->qBCProd = 0;
    $pis->vAliqProd = 0;
    $pis = $make->tagPIS($pis);

    //COFINS
    // TAG COFINS
    $cofins = new stdClass();
    $cofins->item = 1; //item da NFe
    $cofins->CST = '99';
    $cofins->vBC = null;
    $cofins->pCOFINS = null;
    $cofins->vCOFINS = 0.00;
    $cofins->qBCProd = 0;
    $cofins->vAliqProd = 0;
    $make->tagCOFINS($cofins);

    //icmstot OBRIGATÓRIA

    // TAG  ICMS TOTAL
    $icms_total = new \stdClass();
    //$icms_total->vBC = 100;
    //$icms_total->vICMS = 0;
    //$icms_total->vICMSDeson = 0;
    //$icms_total->vFCPUFDest = 0;
    //$icms_total->vICMSUFDest = 0;
    //$icms_total->vICMSUFRemet = 0;
    //$icms_total->vFCP = 0;
    //$icms_total->vBCST = 0;
    //$icms_total->vST = 0;
    //$icms_total->vFCPST = 0;
    //$icms_total->vFCPSTRet = 0.23;
    //$icms_total->vProd = 2000;
    //$icms_total->vFrete = 100;
    //$icms_total->vSeg = null;
    //$icms_total->vDesc = null;
    //$icms_total->vII = 12;
    //$icms_total->vIPI = 23;
    //$icms_total->vIPIDevol = 9;
    //$icms_total->vPIS = 6;
    //$icms_total->vCOFINS = 25;
    //$icms_total->vOutro = null;
    //$icms_total->vNF = 2345.83;
    //$icms_total->vTotTrib = 798.12;
    $icmstot = $make->tagicmstot($icms_total);

    //transp OBRIGATÓRIA
    // TAG TRANSPORTE
    $transporte = new \stdClass();
    $transporte->modFrete = 0;
    $transp = $make->tagtransp($transporte);


    //pag OBRIGATÓRIA
    // TAG PAGAMENTO
    $pagamento = new \stdClass();
    $pagamento->vTroco = 0;
    $pag = $make->tagpag($pagamento);

    //detPag OBRIGATÓRIA
    // TIPO DE PAGAMENTO
    $tipo_de_pagamento = new \stdClass();
    $tipo_de_pagamento->indPag = 1;
    $tipo_de_pagamento->tPag = '01';
    $tipo_de_pagamento->vPag = 100.00;
    $detpag = $make->tagdetpag($tipo_de_pagamento);

    //infadic
    // INFORMAÇÕES ADICIONAIS DE NFE
    $informacoes_adicionais_da_nfe = new \stdClass();
    $informacoes_adicionais_da_nfe->infAdFisco = '';
    $informacoes_adicionais_da_nfe->infCpl = '';
    $info = $make->taginfadic($informacoes_adicionais_da_nfe);

    // TAG RESPONSÁVEL TÉCNICO
    $responsavel_tecnico = new stdClass();
    $responsavel_tecnico->CNPJ = $dados[0]->cnpj; //CNPJ da pessoa jurídica responsável pelo sistema utilizado na emissão do documento fiscal eletrônico
    $responsavel_tecnico->xContato = $dados[0]->site_name; //Nome da pessoa a ser contatada
    $responsavel_tecnico->email = $dados[0]->default_email; //E-mail da pessoa jurídica a ser contatada
    $responsavel_tecnico->fone = '1155551122'; //Telefone da pessoa jurídica/física a ser contatada
    //$responsavel_tecnico->CSRT = 'G8063VRTNDMO886SFNK5LDUDEI24XJ22YIPO'; //Código de Segurança do Responsável Técnico
    //$responsavel_tecnico->idCSRT = '01'; //Identificador do CSRT
    $make->taginfRespTec($responsavel_tecnico);

    $make->monta();
    $xml = $make->getXML();

    // ----------------------------------- CONFIG
    $congif = [
        "atualizacao" => date('Y-m-d h:i:s'),
        "tpAmb"       => intval($nfce[0]->ambienteNFe),
        "razaosocial" => $dados[0]->razao_social,
        "cnpj"        => $dados[0]->cnpj,
        "ie"          => $dados[0]->ie,
        "siglaUF"     => $dados[0]->uf,
        "schemes"     => "PL_009_V4",
        "versao"      => '4.00',
        "tokenIBPT"   => "AAAAAAA",
        "CSC"         => $nfce[0]->csc,
        "CSCid"       => $nfce[0]->codCSC
    ];
    $configJson = json_encode($congif);


    $certificadoDigital = file_get_contents(realpath(__DIR__ . '/..').'/distribuidora.pfx');
    
    $tools = new Tools($configJson, Certificate::readPfx($certificadoDigital, '12345678'));
    $tools->model('65');

    try {
        $xmlAssinado = $tools->signNFe($xml); // O conteúdo do XML assinado fica armazenado na variável $xmlAssinado
    } catch (\Exception $e) {
        //aqui você trata possíveis exceptions da assinatura
        $dados_da_nfce['erro'] = $e->getMessage(); // Caso haja erro, guarda no banco de dados
    }

    // $xml = $tools->signNFe($xml);
    
    header('Content-Type: application/xml; charset=utf-8');
    echo $xmlAssinado;
    
}  
} 
