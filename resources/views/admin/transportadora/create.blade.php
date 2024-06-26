@extends('admin.layout.app')

@section('css')
<link href="{{ asset('assets/css/transp.css') }}" rel="stylesheet">
@endsection


@section('content')
    <div class="page-header-content py-3">

        <div class="d-sm-flex align-items-center justify-content-between">
            <h1 class="h3 mb-0 text-gray-800">Transportadoras</h1>
            <a href="{{ route('admin.transp.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                <i class="fas fa-plus text-white-50"></i> Nova Transportadoras
            </a>
        </div>

        <ol class="breadcrumb mb-0 mt-4">
            <li class="breadcrumb-item"><a href="/">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page"><a
                    href="{{ route('admin.transp.index') }}">Transportadoras</a></li>
            <li class="breadcrumb-item active" aria-current="page">Nova Transportadora</li>
        </ol>

    </div>
    <form action="{{ route('admin.transp.store') }}" enctype="multipart/form-data" method="post">
        @csrf <!-- Utilize isso se estiver trabalhando em um ambiente Laravel -->
        <div class="row">
            <div class="col-md-4 center-div">
                <div class="img-container">
                    <img id="image-preview" class="img-form" src="/{{ old('image_path') }}" alt="Imagem Preview">
                    <button type="button" class="btn btn-primary btn-ativa-input btn-img">Selecionar Imagem</button>
                    <input type="file" id="imagem-input" name="image" class="custom-file-input">
                </div>



            </div>

            @error('cnpj')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <div class="col-md-4">
                <div class="form-group">
                    <label for="legal_name">Razão Social</label>
                    <input type="text" class="form-control @error('legal_name') is-invalid @enderror" id="legal_name"
                        name="legal_name" value="{{ old('legal_name') }}">
                    @error('legal_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="phone">Telefone</label>
                    <input type="text" class="form-control @error('phone') is-invalid @enderror phone" id="phone"
                        name="phone" value="{{ old('phone') }}">

                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="optante_simples_nacional">Optante pelo Simples Nacional</label>
                    <select class="form-control" id="optante_simples_nacional" name="optante_simples_nacional">
                        <option value="1">Sim</option>
                        <option value="0">Não</option>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="trade_name">name Fantasia</label>
                    <input type="text" class="form-control @error('trade_name') is-invalid @enderror" id="trade_name"
                        name="trade_name" value="{{ old('trade_name') }}">
                    @error('trade_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="state_registration">Inscrição Estadual</label>
                    <input type="text" class="form-control @error('state_registration') is-invalid @enderror"
                        id="state_registration" name="state_registration" value="{{ old('state_registration') }}">

                    @error('state_registration')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="form-group col-md-6">
                        <label for="rntrc">RNTRC</label>
                        <input type="text" class="form-control @error('rntrc') is-invalid @enderror" id="rntrc"
                            maxlength="9" name="rntrc" value="{{ old('rntrc') }}">

                        @error('rntrc')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="form-group col-md-6">
                    <label for="validade_rntrc">Validade RNTRC</label>
                    <input type="date" class="form-control" id="validade_rntrc" name="validade_rntrc">
                </div>
            </div>

        </div>

        <div class="breadcrumb mb-0 ">
            <span class="text-header">Dados do Endereço</span>
        </div>


        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="zip_code">zip_code</label>
                    <input type="text" class="form-control @error('zip_code') is-invalid @enderror" id="zip_code"
                        maxlength="9" name="zip_code" value="{{ old('zip_code') }}">

                    @error('zip_code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="complemento">Complemento</label>
                    <input type="text" class="form-control" id="complemento" name="complemento"
                        value="{{ old('complemento') }}">
                </div>
                <div class="form-group">
                    <label for="state">state</label>
                    <input type="text" class="form-control @error('state') is-invalid @enderror" id="state"
                        name="state" value="{{ old('state') }}">

                    @error('state')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="address">Endereço</label>
                    <input type="text" class="form-control @error('address') is-invalid @enderror" id="address"
                        name="address" value="{{ old('address') }}">
                    @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="bairro">Bairro</label>
                    <input type="text" class="form-control @error('bairro') is-invalid @enderror" id="bairro"
                        name="bairro" value="{{ old('bairro') }}">

                    @error('bairro')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="ponto_referencia">Ponto de Referência</label>
                    <input type="text" class="form-control" id="ponto_referencia" name="ponto_referencia"
                        value="{{ old('ponto_referencia') }}">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="number">Número</label>
                    <input type="text" class="form-control" id="number" name="number"
                        value="{{ old('number') }}">
                    @error('number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="city">city</label>
                    <input type="text" class="form-control @error('city') is-invalid @enderror" id="city"
                        name="city" value="{{ old('city') }}">

                    @error('city')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>


        <div class="breadcrumb mb-0 ">
            <span class="text-header">Informações do Contato</span>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="name">name</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                        name="name" value="{{ old('name') }}">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="email">E-mail</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                        name="email" value="{{ old('email') }}">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="phone1">Telefone 1</label>
                    <input type="text" class="form-control telefone @error('phone1') is-invalid @enderror"
                        id="phone1" name="phone1" value="{{ old('phone1') }}">
                    @error('phone1')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="telefone2">Telefone 2</label>
                    <input type="text" class="form-control telefone @error('telefone2') is-invalid @enderror"
                        id="telefone2" name="telefone2" value="{{ old('telefone2') }}">
                    @error('telefone2')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="breadcrumb mb-0 ">
            <span class="text-header">Informações de Atendimento (Para tracking externo)</span>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="telefone_sac">Telefone SAC</label>
                    <input type="text" class="form-control @error('telefone_sac') is-invalid @enderror"
                        id="telefone_sac" name="telefone_sac" value="{{ old('telefone_sac') }}">
                    @error('telefone_sac')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="titulo">Título</label>
                    <input type="text" class="form-control @error('titulo') is-invalid @enderror" id="titulo"
                        name="titulo" value="{{ old('titulo') }}">
                    @error('titulo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="email_sac">E-mail SAC</label>
                    <input type="email" class="form-control @error('email_sac') is-invalid @enderror" id="email_sac"
                        name="email_sac" value="{{ old('email_sac') }}">
                    @error('email_sac')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="mensagem">Mensagem</label>
                    <textarea class="form-control @error('mensagem') is-invalid @enderror" id="mensagem" name="mensagem"
                        rows="4">{{ old('mensagem') }}</textarea>
                    @error('mensagem')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="site">Site</label>
                    <input type="text" class="form-control @error('site') is-invalid @enderror" id="site"
                        name="site" value="{{ old('site') }}">
                    @error('site')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
        <div class="row" style="direction:rtl">
            <button type="submit" class="btn btn-primary">Cadastrar Transportadora</button>
        </div>









    </form>
@endsection


@section('scripts')
    <script>
        $(document).ready(function() {

            $(".btn-ativa-input").click(function() {
                $("#imagem-input").click();
            });

            $("#imagem-input").change(function() {
                readURL(this);
            });

            function readURL(input) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        $("#image-preview").attr("src", e.target.result);
                        $("#image-preview").css("display", "block");
                    };
                    reader.readAsDataURL(input.files[0]);
                }
            }
        });
    </script>
@endsection
