@extends('admin.layout.app')


@section('css')
    <link href="{{ asset('/assets/css/transp.css') }}" rel="stylesheet">
@endsection

@section('content')
    <section id="device">
        <!-- Page Heading -->
        <div class="page-header-content py-3">

            <div class="d-sm-flex align-items-center justify-content-between">
                <h1 class="h3 mb-0 text-gray-800">Transportadoras</h1>
                <a href="{{ route('admin.transp.create') }}"
                    class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                    <i class="fas fa-plus text-white-50"></i> Nova Transportadoras
                </a>
            </div>

            <ol class="breadcrumb mb-0 mt-4">
                <li class="breadcrumb-item"><a href="/">Inicio</a></li>
                <li class="breadcrumb-item active" aria-current="page">Transportadoras</li>
            </ol>

        </div>
        <!-- Content Row -->
        <div class="row">
            <!-- Content Column -->
            <div class="col-lg-12 mb-4">
                <!-- Project Card Example -->
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <div class="table-device">
                            <table class="table table-bordered" id="table-device">

                                <thead>
                                    <tr>
                                        <th scope="col" class="text-center">IMG</th>
                                        <th scope="col">Nome</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Ações</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($transportadoras as $transportadora)
                                        <tr>
                                            <td class="text-center"> {{-- Classe align-middle centraliza verticalmente o conteúdo --}}
                                                @if ($transportadora->image_path)
                                                    <img class="img-transp-index" src="{{ asset($transportadora->image_path) }}" alt="Imagem da Transportadora">
                                                @endif
                                            </td>
                                            <td>{{ $transportadora->trade_name }}</td>
                                            <td>{{ $transportadora->status }}</td>
                                            <td>
                                                {{-- Aqui você pode colocar botões de ação ou links, como editar ou excluir --}}
                                                {{-- Por exemplo, você pode adicionar botões para editar e excluir --}}
                                                <a href="{{ route('admin.transp.edit', ['transportadora' => $transportadora->id]) }}" class="btn btn-primary">Editar</a>
                                                <form action="{{ route('admin.transp.destroy', ['transportadora' => $transportadora->id]) }}" method="POST" style="display: inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger">Excluir</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                

                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- Modal -->
    <div class="modal fade" id="modalDelete" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <h5 class="py-3 m-0">Tem certeza que deseja excluir este Dispositivo?</h5>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Fechar</button>
                    <form action="" method="post" class="float-right">
                        @csrf
                        <input type="hidden" id="id_device" name="id_device">
                        <button type="submit" class="btn btn-danger btn-sm">Excluir</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

  
@endsection
@section('scripts')



@endsection
