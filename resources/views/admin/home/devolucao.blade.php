@extends('admin.layout.app')


@section('css')
    {{-- <link href="{{ asset('/assets/admin/css/device.css') }}" rel="stylesheet"> --}}
@endsection

@section('content')
    <section id="device">
        <!-- Page Heading -->
        <div class="page-header-content py-3">

            <div class="d-sm-flex align-items-center justify-content-between">
                <h1 class="h3 mb-0 text-gray-800">Devoluções</h1>
            </div>

            <ol class="breadcrumb mb-0 mt-4">
                <li class="breadcrumb-item"><a href="/home">Inicio</a></li>
                <li class="breadcrumb-item active" aria-current="page">Devoluções</li>
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
                            <table class="table table-bordered" id="tabela-devolution">

                                <thead>
                                    <tr>
                                        <th scope="col">Data</th>
                                        <th scope="col">Transp</th>
                                        <th scope="col">N° Encomenda</th>
                                        <th scope="col">N° NF</th>
                                        <th scope="col">UF Destinatario</th>
                                        <th scope="col">Ações</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Modal -->
    <div class="modal fade" id="modalDetalhes" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <!-- Aqui serão exibidas as informações detalhadas da entrega via AJAX -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal -->
    <div class="modal fade" id="modalDelete" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered " role="document">
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
   <script>
      var urlDevolution = window.location.origin;
        $('#tabela-devolution').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: urlDevolution + "/entregas/getEntregasDevolution",
                type: "GET"
            },
            columns: [{
                    data: "created_at"
                },
                {
                    data: "id"
                },
                {
                    data: "parcel"
                },
                {
                    data: "invoice"
                },
                {
                    data: "destination_state"
                },
                {
                    data: "id",
                    render: function(data, type, row) {
                        // Adiciona um botão de visualização (olho) para cada entrega
                        return '<button class="btn btn-sm btn-primary" onclick="openModal(' + data +
                            ')"><i class="fas fa-eye"></i></button>';
                    }
                }
            ],
            columnDefs: [{
                targets: [2],
                className: 'dt-body-center'
            }],
            rowCallback: function(row, data, index) {
                $('td:eq(0)', row).html(data['display_data']);
                $('td:eq(1)', row).html(data['carriers'].trade_name);
                // $('td:eq(5)', row).html(data['status'][0].status);
            }
        });

        function openModal(id) {
            // Requisição AJAX para buscar informações da entrega pelo ID
            $.ajax({
                url: '/entregas/getinfoEntrega/' + id, // Rota para buscar os detalhes da entrega
                type: 'GET',
                success: function(response) {
                    // Preencher os campos do modal com as informações retornadas
                    $('#modalDetalhes .modal-body').html(response);
                    // Abrir o modal
                    $('#modalDetalhes').modal('show');
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        }
   </script>
@endsection
