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
                            <h6 class="card-subtitle mb-2 text-muted linha-bottom">Entregas Em Aberto - {{ $in_progress }}
                            </h6>
                            <div class="row">
                                <div class="col-md-6">
                                    @php
                                        $onTimePercentage = (($in_progress - $overdue) / $in_progress) * 100;
                                    @endphp
                                    <h4><b>{{ $in_progress - $overdue }}</b></h4>
                                    <small>No Prazo <h4 class="on-time"><b>{{ number_format($onTimePercentage, 2) }}%</b>
                                        </h4></small>
                                </div>
                                <div class="col-md-6">
                                    @php
                                        $overduePercentage = ($overdue / $in_progress) * 100;
                                    @endphp
                                    <h4><b>{{ $overdue }}</b></h4>
                                    <small>Atrazado <h4 class="overdue"><b>{{ number_format($overduePercentage, 2) }}%</b>
                                        </h4></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                
            </div>
            <div class="row">
                {{-- <div class="col-md-6 p-0">
                        <div class="col-md-12 mb-4">
                            <div class="card">
                                <div class="card-body">
                                    <div id="chart">

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> --}}

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

                    // getconfigDaysDashborad(start, end);
                    tableError(start, end)
                }

                // function getconfigDaysDashborad(start, end) {
                //     let dateStart = start.format("YYYY-MM-DD");
                //     let dateEnd = end.format("YYYY-MM-DD");

                //     $.ajax({
                //         type: "GET",
                //         dataType: "JSON",
                //         data: {
                //             dateStart,
                //             dateEnd
                //         },
                //         url: "/dashboard/get-days-dashborad",
                //         beforeSend: () => {
                //             Utils.isLoading();
                //         },
                //         success: (data) => {
                //             $("#count-location").html(data['count-location'])
                //             $("#count-location2").html(data['count-location'])
                //             $("#amounts-opened").html(data['amounts-opened'])
                //             $("#amounts-settings").html(data['amounts-settings'])
                //             $("#amounts-cancel").html(data['amounts-cancel'])
                //             $("#amounts-awaiting-withdrawal").html(data['amounts-awaiting-withdrawal'])
                //             $("#amounts-received").html(data['amounts-received'])
                //             $("#amounts-late").html(data['amounts-late'])
                //             if (paymentMethodChart) {
                //                 paymentMethodChart.destroy();
                //             }
                //             configChart(data['amounts-pix'], data['amounts-credit'], data[
                //                 'amounts-debit'])
                //             // upChart(data['amounts-pix'], data['amounts-credit'], data['amounts-debit']);
                //         },
                //         error: (xhr) => {},
                //         complete: () => {},
                //     });
                //     initTable(dateStart, dateEnd);
                //     initTableLastLocation();
                // }

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

                function initTable(start, end) {
                    $.ajax({
                        type: "GET",
                        dataType: "JSON",
                        data: {
                            start,
                            end
                        },
                        url: "/dashboard/get-more-locations",
                        beforeSend: () => {
                            Utils.isLoading();
                        },
                        success: (data) => {
                            $("#tbody-more-location").empty();
                            data.products.data.map((products) => {
                                var valor = parseFloat(products.total_amount).toLocaleString(
                                    'pt-br', {
                                        minimumFractionDigits: 2
                                    });
                                $("#tbody-more-location").append(`
                        <tr>
                           <td>${products.variant.product.name+'-'+products.variant.size+'-'+products.variant.color}</td>
                           <td>${products.sold_qty}</td>
                           <td >${'R$ '+valor}</td>
                        </tr>
                        
                        `);

                            });
                            Utils.setupPagination({
                                data: data.products,
                                get: "Page.getCombos",
                            });
                        },
                        error: (xhr) => {

                        },
                        complete: () => {
                            Utils.isLoading(false);
                        },
                    });
                }

                // function upChart(data) {
                //     chart.updateSeries(
                //         [{
                //             data: data.data
                //         }]
                //     )
                // }

                // function configChart(pix, credit, debit) {
                //     var options = {
                //         series: [pix, credit, debit],
                //         chart: {
                //             height: 390,
                //             type: 'radialBar',
                //         },
                //         plotOptions: {
                //             radialBar: {
                //                 offsetY: 0,
                //                 startAngle: 0,
                //                 endAngle: 270,
                //                 hollow: {
                //                     margin: 5,
                //                     size: '30%',
                //                     background: 'transparent',
                //                     image: undefined,
                //                 },
                //                 dataLabels: {
                //                     name: {
                //                         show: false,
                //                     },
                //                     value: {
                //                         show: false,
                //                     }
                //                 }
                //             }
                //         },
                //         colors: ['#0084ff', '#39539E', '#0077B5'],
                //         labels: ['Pix', 'Cartão De Crédito', 'Cartão De Débito'],
                //         legend: {
                //             show: true,
                //             floating: true,
                //             fontSize: '16px',
                //             position: 'left',
                //             offsetX: 50,
                //             offsetY: 15,
                //             labels: {
                //                 useSeriesColors: true,
                //             },
                //             markers: {
                //                 size: 0
                //             },
                //             formatter: function(seriesName, opts) {
                //                 return seriesName + ":  " + opts.w.globals.series[opts.seriesIndex]
                //             },
                //             itemMargin: {
                //                 vertical: 3
                //             }
                //         },
                //         responsive: [{
                //             breakpoint: 480,
                //             options: {
                //                 legend: {
                //                     show: false
                //                 }
                //             }
                //         }]
                //     };

                //     paymentMethodChart = new ApexCharts(document.querySelector("#chart"), options);
                //     paymentMethodChart.render();
                // }
            },
        };
        Page.init();


        // var options = {
        //     series: [{

        //         data: []
        //     }],
        //     annotations: {
        //         points: [{
        //             x: 'Bananas',
        //             seriesIndex: 0,
        //             label: {
        //                 borderColor: '#775DD0',
        //                 offsetY: 0,
        //                 style: {
        //                     color: '#fff',
        //                     background: '#775DD0',
        //                 },
        //                 text: 'Bananas are good',
        //             }
        //         }]
        //     },
        //     chart: {
        //         height: 350,
        //         type: 'bar',
        //     },
        //     plotOptions: {
        //         bar: {
        //             borderRadius: 10,
        //             columnWidth: '50%',
        //             horizontal: true
        //         }
        //     },
        //     dataLabels: {
        //         enabled: false
        //     },
        //     stroke: {
        //         width: 2
        //     },

        //     grid: {
        //         row: {
        //             colors: ['#fff', '#f2f2f2']
        //         }
        //     },
        //     xaxis: {
        //         labels: {
        //             rotate: -45
        //         },
        //         categories: [],
        //         tickPlacement: 'on'
        //     },
        //     yaxis: {
        //         title: {
        //             text: 'Status Entrega',
        //         },
        //     },
        //     fill: {
        //         type: 'gradient',
        //         gradient: {
        //             shade: 'light',
        //             type: "horizontal",
        //             shadeIntensity: 0.25,
        //             gradientToColors: undefined,
        //             inverseColors: true,
        //             opacityFrom: 0.85,
        //             opacityTo: 0.85,
        //             stops: [50, 0, 100]
        //         },
        //     }
        // };

        $.ajax({
            type: "GET",
            dataType: "JSON",
            url: "/home/status/filter",
            beforeSend: () => {
                Utils.isLoading();
            },
            success: (data) => {
                var dadosDoBanco = data;

                // Extrair os dados do banco em um formato adequado para o gráfico
                var categorias = [];
                var series = [];

                dadosDoBanco.forEach(function(item) {
                    categorias.push(item.status);
                    series.push(item.amount);
                });

                // Atualizar os dados no objeto 'options'
                options.series[0].data = series;
                options.xaxis.categories = categorias;

                // Renderizar o gráfico
                var chart = new ApexCharts(document.querySelector("#chart"), options);
                chart.render();

            },
            error: (xhr) => {},
            complete: () => {
                Utils.isLoading(false);
            },
        });
    </script>
@endsection
