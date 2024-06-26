<!-- entrega/show.blade.php -->

<div>
    <h2>Detalhes da Entrega</h2>
    <div class="row">
        <div class="col-md-6">
            <p><strong>ID:</strong> {{ $entrega->id }}</p>
            <p><strong>Data de Criação:</strong> {{ $entrega->created_at }}</p>
            <p><strong>ID do Transportador:</strong> {{ $entrega->carrier_id }}</p>
            <p><strong>ID do Remetente:</strong> {{ $entrega->shipper_id }}</p>
            <p><strong>Data Recebida:</strong> {{ $entrega->received }}</p>
            <p><strong>Data Agendada:</strong> {{ $entrega->scheduled }}</p>
            <p><strong>Entrega Estimada:</strong> {{ $entrega->estimated_delivery }}</p>
        </div>
        <div class="col-md-6">
            <p><strong>Parcela:</strong> {{ $entrega->parcel }}</p>
            <p><strong>Número da Fatura:</strong> {{ $entrega->invoice }}</p>
            <p><strong>Quantidade de Pacotes:</strong> {{ $entrega->quantity_of_packages }}</p>
            <p><strong>Chave da Fatura:</strong> {{ $entrega->invoice_key }}</p>
            <p><strong>Número do Pacote:</strong> {{ $entrega->package_number }}</p>
            <p><strong>Peso:</strong> {{ $entrega->weight }}</p>
            <p><strong>Peso Total:</strong> {{ $entrega->total_weight }}</p>
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col-md-6">
            <p><strong>Nome do Destinatário:</strong> {{ $entrega->destination_name }}</p>
            <p><strong>CPF/CNPJ do Destinatário:</strong> {{ $entrega->destination_tax_id }}</p>
            <p><strong>Telefone do Destinatário:</strong> {{ $entrega->destination_phone }}</p>
            <p><strong>E-mail do Destinatário:</strong> {{ $entrega->destination_email }}</p>
        </div>
        <div class="col-md-6">
            <p><strong>CEP do Destinatário:</strong> {{ $entrega->destination_zip_code }}</p>
            <p><strong>Endereço do Destinatário:</strong> {{ $entrega->destination_address }}</p>
            <p><strong>Número do Destinatário:</strong> {{ $entrega->destination_number }}</p>
            <p><strong>Bairro do Destinatário:</strong> {{ $entrega->destination_neighborhood }}</p>
            <p><strong>Cidade do Destinatário:</strong> {{ $entrega->destination_city }}</p>
            <p><strong>Estado do Destinatário:</strong> {{ $entrega->destination_state }}</p>
        </div>
    </div>
    <hr>
    <p><strong>Código Externo:</strong> {{ $entrega->external_code }}</p>
</div>
