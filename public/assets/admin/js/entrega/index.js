var url = window.location.origin;
$('#tabela-entrega').DataTable({
    processing: true,
    serverSide: true,
    "ajax": {
        "url": url + "/entregas/getEntregas",
        "type": "GET"
    },
    "columns": [{
        "data": "created_at"
    },{
        "data": "id"
    },{
        "data": "parcel"
    },{
        "data": "invoice"
    },{
        "data": "destination_state"
    },{
        "data": "id"
    },
    ],
    'columnDefs': [
        {
            targets: [2],
            className: 'dt-body-center'
        }
    ],
    'rowCallback': function (row, data, index) {

        console.log(data);
        // let btn = 'success';
        // if(data['display_status'] == "Desconectado"){
        //     btn = "danger";
        // }
         $('td:eq(0)', row).html(data['display_data']);
         $('td:eq(1)', row).html(data['carriers'].trade_name);
         $('td:eq(5)', row).html(data['status'][0].status);
        // $('td:eq(2)', row).html('<button class="btn btn-'+btn+'">'+data['display_status']+'</button>');
        // $('td:eq(3)', row).html( '<a href="javascript:;" data-toggle="modal" onClick="configModalDelete(' + data["id"] + ')" data-target="#modalDelete" class="btn btn-sm btn-danger delete"><i class="far fa-trash-alt"></i></a>');


    },
});

