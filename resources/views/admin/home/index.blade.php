@extends('admin.layout.app')


@section('css')
    <link href="{{ asset('/assets/admin/libs/daterangepicker-master/daterangepicker.css') }}" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <style>
        .card-home {
            border: none;
            margin: auto;
            /* Centralizar horizontalmente */
            text-align: center;
            background-color: #0aeaae45;
            /* Centralizar o texto */
        }

        .linha-bottom {
            border-bottom: 1px dotted #ccc;
            /* Linha pontilhada abaixo do título */
            margin-bottom: 10px;
            /* Espaçamento abaixo do título */
        }

        .card-text {
            font-size: 24px;
            /* Ajuste de tamanho de fonte */
        }

        .linha-vertical {
            border-left: 1px solid #ccc;
            height: 100%;
            /* Ajusta a altura da linha */
        }

        .on-time {
            background-color: #28a745;
            /* Verde */
            color: #fff;
            /* Texto branco */
            border-radius: 10px;
            /* Borda arredondada */
            padding: 5px 10px;
            /* Espaçamento interno */
            font-size: 10px;
        }

        .overdue {
            background-color: #dc3545;
            /* Vermelho */
            color: #fff;
            /* Texto branco */
            border-radius: 10px;
            /* Borda arredondada */
            padding: 5px 10px;
            /* Espaçamento interno */
            font-size: 10px;
        }

        .progress {
            background-color: #dccfcf !important;
        }



        .color-progress {
            position: absolute;
            right: 0;
            top: 0;
            padding: 0.1rem 0.5rem;
            border-radius: 15px;
            background-color: blue;
            color: white;
            font-size: 10px;
        }

        .color-danger {
            background-color: rgb(150, 53, 53);
        }

        .progress-bar-in-progress {
            background-color: #1ea834;
            ;
        }

        .text-number {
            font-family: fantasy;
            color: black
        }
    </style>
@endsection

@section('content')
    <div class="navtab-bg nav-pills d-flex">
        <ul class="nav nav-justified w-50">
            <li class="nav-item">
                <a href="#movement-tab" data-toggle="tab" aria-expanded="false"
                    class="nav-link active h-100 d-flex justify-content-center align-items-center">
                    <span class="d-block d-sm-none"><i class="uil-home-alt"></i></span>
                    <span class="d-none d-sm-block">Status Entregas</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#erro-tab" data-toggle="tab" aria-expanded="true"
                    class="nav-link h-100 d-flex justify-content-center align-items-center">
                    <span class="d-block d-sm-none"><i class="uil-user"></i></span>
                    <span class="d-none d-sm-block">Erros</span>
                    <button class="btn btn-erro" id="count-error"></button>
                </a>
            </li>
        </ul>

    </div>


    <div class="tab-content text-muted mt-2">
        <div class="tab-pane show active" id="movement-tab">

          

            <div class="row">
                <div class="col-md-2">
                    <div class="card card-home">
                        <div class="card-body">
                            <h6 class="card-subtitle mb-2 text-muted  linha-bottom">Recebidas Hoje</h6>
                            <p class="card-text"><b>{{ $countToday }}</b></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card card-home">
                        <div class="card-body">
                            <h6 class="card-subtitle mb-2 text-muted  linha-bottom">Devolução</h6>
                            <p class="card-text"><b>{{ $returned }}</b></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-home">
                        <div class="card-body">
                            <h6 class="card-subtitle mb-2 text-muted linha-bottom">Entregas Em Aberto -
                                {{ $in_progress }}
                            </h6>
                            <div class="row">
                                <div class="col-md-6">
                                    @php
                                        $onTimePercentage = (($in_progress - $overdue) / $in_progress) * 100;
                                    @endphp
                                    <h4><b>{{ $in_progress - $overdue }}</b></h4>
                                    <small class="d-flex">No Prazo <h4 class="on-time">
                                            <b>{{ number_format($onTimePercentage, 2) }}%</b>
                                        </h4></small>
                                </div>
                                <div class="col-md-6">
                                    @php
                                        $overduePercentage = ($overdue / $in_progress) * 100;
                                    @endphp
                                    <h4><b>{{ $overdue }}</b></h4>
                                    <small class="d-flex">Atrazado <h4 class="overdue">
                                            <b>{{ number_format($overduePercentage, 2) }}%</b>
                                        </h4></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card text-left">
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th width='25%'>tranportadora</th>
                                <th width='5%'>Total</th>
                                <th width='35%'>finalizados</th>
                                <th width='35%'>Em Aberto</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($carriesResult as $dataCarrie)
                                <tr>
                                    <td scope="row">{{ $dataCarrie['carrie']->trade_name }}</td>
                                    <td scope="row">
                                        <p class="text-number">{{ $dataCarrie['total'] }}</p>
                                    </td>
                                    <td scope="row">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="row">
                                                    <div class="col-6 text-left text-number">{{ $dataCarrie['finished'] }}
                                                    </div>
                                                    <div class="color-progress">
                                                        {{ $dataCarrie['percentage_finished'] }}%</div>
                                                </div>
                                                <div class="progress">
                                                    <div class="progress-bar bg-success" role="progressbar"
                                                        style="width: {{ $dataCarrie['percentage_finished'] }}%;"
                                                        aria-valuenow="{{ $dataCarrie['percentage_finished'] }}"
                                                        aria-valuemin="0" aria-valuemax="100">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="row">
                                                    <div class="col-4 text-left text-number">
                                                        {{ $dataCarrie['deliveriesOnTime'] }}</div>
                                                    <div class="text-progress"><small><b>No Prazo</b></small></div>
                                                    <div class="color-progress">
                                                        {{ $dataCarrie['percentage_ontime'] }}%</div>
                                                </div>
                                                <div class="progress">
                                                    <div class="progress-bar bg-success" role="progressbar"
                                                        style="width: {{ $dataCarrie['percentage_ontime'] }}%;"
                                                        aria-valuenow="{{ $dataCarrie['percentage_ontime'] }}"
                                                        aria-valuemin="0" aria-valuemax="100">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="row">
                                                    <div class="col-4 text-left text-number">
                                                        {{ $dataCarrie['deliveriesDelayed'] }}</div>
                                                    <div class="text-progress"><small><b>Fora do Prazo</b></small></div>
                                                    <div class="color-progress color-danger">
                                                        {{ $dataCarrie['percentage_delayed'] }}%</div>
                                                </div>
                                                <div class="progress">
                                                    <div class="progress-bar color-danger" role="progressbar"
                                                        style="width: {{ $dataCarrie['percentage_delayed'] }}%;"
                                                        aria-valuenow="{{ $dataCarrie['percentage_delayed'] }}"
                                                        aria-valuemin="0" aria-valuemax="100">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td scope="row">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="row">
                                                    <div class="col-6 text-left">{{ $dataCarrie['in_progress'] }}</div>
                                                    <div class="color-progress">
                                                        {{ $dataCarrie['percentage_in_progress'] }}%</div>
                                                </div>
                                                <div class="progress">
                                                    <div class="progress-bar progress-bar-in-progress" role="progressbar"
                                                        style="width: {{ $dataCarrie['percentage_in_progress'] }}%;"
                                                        aria-valuenow="{{ $dataCarrie['percentage_in_progress'] }}"
                                                        aria-valuemin="0" aria-valuemax="100">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="row">
                                                    <div class="col-4 text-left text-number">
                                                        {{ $dataCarrie['inProgressOnTime'] }}</div>
                                                    <div class="text-progress"><small><b>No Prazo</b></small></div>
                                                    <div class="color-progress">
                                                        {{ $dataCarrie['percentageInProgressOnTime'] }}%</div>
                                                </div>
                                                <div class="progress">
                                                    <div class="progress-bar bg-success" role="progressbar"
                                                        style="width: {{ $dataCarrie['percentageInProgressOnTime'] }}%;"
                                                        aria-valuenow="{{ $dataCarrie['percentageInProgressOnTime'] }}"
                                                        aria-valuemin="0" aria-valuemax="100">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="row">
                                                    <div class="col-4 text-left text-number">
                                                        {{ $dataCarrie['inProgressDelayed'] }}</div>
                                                    <div class="text-progress"><small><b>Fora do Prazo</b></small></div>
                                                    <div class="color-progress color-danger">
                                                        {{ $dataCarrie['percentageInProgressDelayed'] }}%</div>
                                                </div>
                                                <div class="progress">
                                                    <div class="progress-bar color-danger" role="progressbar"
                                                        style="width: {{ $dataCarrie['percentageInProgressDelayed'] }}%;"
                                                        aria-valuenow="{{ $dataCarrie['percentageInProgressDelayed'] }}"
                                                        aria-valuemin="0" aria-valuemax="100">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <canvas id="statusChart"></canvas>
                </div>

            </div>
        </div>
        <div class="tab-pane" id="erro-tab">
            <div class="row">
                <div class="col-sm-12">
                    <form class="form-inline float-sm-right mt-3 mt-sm-0">
                        <div class="form-group mb-sm-0">
                            <h4 for="filter-days">Filtro de dias: &nbsp;&nbsp;</h4>
                            <div id="filter-days" class="form-control">
                                <i class="fa fa-calendar"></i>&nbsp;
                                <span></span> <i class="fa fa-caret-down"></i>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-sm-12 col-xl-12 mb-4">
                    <div class="card">

                        <div class="card-body p-0">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Data</th>
                                        <th>Erro</th>
                                    </tr>
                                </thead>
                                <tbody id="errorList">
                                    <!-- Erros serão adicionados aqui -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

    <script>
        var paymentMethodChart = null;
        const Page = {
            init: () => {
                Page.setListeners();
            },

            setListeners: () => {
                // date picker

                moment.locale('pt-br');

                var start = moment();
                var end = moment();

                function cb(start, end) {
                    $("#filter-days span").html(
                        start.format("DD/MM/YYYY") +
                        " - " +
                        end.format("DD/MM/YYYY")

                    );

                    orderDash(start.format("YYYY-MM-DD"), end.format("YYYY-MM-DD"));
                }

                $("#filter-days").daterangepicker({
                    startDate: start,
                    endDate: end,
                    ranges: {
                        "Hoje": [moment(), moment()],
                        "Ontem": [
                            moment().subtract(1, "days"),
                            moment().subtract(1, "days"),
                        ],
                        "Últimos 7 dias": [moment().subtract(6, "days"), moment()],
                        "Últimos 30 dias": [moment().subtract(29, "days"), moment()],
                        "Esse mês": [
                            moment().startOf("month"),
                            moment().endOf("month"),
                        ],
                        "Mês passado": [
                            moment().subtract(1, "month").startOf("month"),
                            moment().subtract(1, "month").endOf("month"),
                        ],
                    },
                    locale: {
                        format: "DD/MM/YYYY",
                        separator: " - ",
                        applyLabel: "Aplicar",
                        cancelLabel: "Cancelar",
                        fromLabel: "De",
                        toLabel: "Até",
                        customRangeLabel: "Personalizado",
                        months: [
                            "Jan", "Fev", "Mar", "Abr", "Mai", "Jun",
                            "Jul", "Ago", "Set", "Out", "Nov", "Dez"
                        ],
                        monthsShort: [
                            "Jan", "Fev", "Mar", "Abr", "Mai", "Jun",
                            "Jul", "Ago", "Set", "Out", "Nov", "Dez"
                        ],
                        daysOfWeek: [
                            "Dom", "Seg", "Ter", "Qua", "Qui", "Sex", "Sáb",
                        ],
                        monthNames: [
                            "Janeiro",
                            "Fevereiro",
                            "Março",
                            "Abril",
                            "Maio",
                            "Junho",
                            "Julho",
                            "Agosto",
                            "Setembro",
                            "Outubro",
                            "Novembro",
                            "Dezembro",
                        ],
                        firstDay: 0,
                    },
                }, cb);

                cb(start, end);

                function orderDash(start, end) {
                    tableError(start, end)
                }



                function tableError(start, end) {
                    let dateStart = start;
                    let dateEnd = end;
                    $.ajax({
                        type: "GET",
                        dataType: "JSON",
                        data: {
                            dateStart,
                            dateEnd
                        },
                        url: "/home/errors/filter",
                        beforeSend: () => {
                            Utils.isLoading();
                        },
                        success: (data) => {

                            var btnError = document.getElementById("count-error");
                            btnError.textContent = data['errors'].length;
                            const errorList = document.getElementById('errorList');
                            errorList.innerHTML = ''; // Limpa a lista atual
                            data.errors.forEach(error => {
                                const row = document.createElement('tr');
                                row.innerHTML = `
                                    <td>${error.formatted_date}</td>
                                    <td>${error.erro}</td>
                                `;
                                errorList.appendChild(row);
                            });

                        },
                        error: (xhr) => {},
                        complete: () => {
                            Utils.isLoading(false);
                        },
                    });

                }

            },
        };
        Page.init();

        // Função para carregar os dados e criar o gráfico
        function fetchDataAndCreateChart() {
            // Faça uma solicitação AJAX para obter os dados
            $.ajax({
                url: '/home/status', // Substitua 'url_para_obter_dados' pela sua URL de backend
                method: 'GET',
                success: function(data) {
                    // Ordenar os dados pelo valor de 'count' (quantidade) em ordem decrescente
                    data.sort(function(a, b) {
                        return b.count - a.count;
                    });

                    // Extrair os labels (status) e os valores (quantidades) dos dados ordenados
                    var labels = data.map(function(item, index) {
                        return `${item.status} (${item.count})`; // Adiciona o número após o status
                    });

                    var values = data.map(function(item) {
                        return item.count;
                    });

                    // Configuração do gráfico
                    var ctx = document.getElementById('statusChart').getContext('2d');
                    var chart = new Chart(ctx, {
                        type: 'horizontalBar',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Quantidade',
                                data: values,
                                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                                borderColor: 'rgba(54, 162, 235, 1)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            scales: {
                                xAxes: [{
                                    ticks: {
                                        beginAtZero: true
                                    }
                                }]
                            }
                        }
                    });
                },
                error: function(xhr, status, error) {
                    console.error(error); // Lidar com erros de solicitação AJAX
                }
            });
        }

        // Chame a função para carregar os dados e criar o gráfico
        fetchDataAndCreateChart();
    </script>
@endsection
