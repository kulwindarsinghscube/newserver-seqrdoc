@extends('admin.layout.layout')
@section('content')
  <div class="container">
    <div class="row">
      <div class="col-lg-12">
        <h1 class="page-header"><i class="fa fa-list-ul" aria-hidden="true"></i> Stock
        <ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;margin-top:10px;">{{ Breadcrumbs::render('stationarystock') }}</ol>
        <i class="fa fa-info-circle iconModalCss" title="User Manual" id="stationaryStockClick"></i>
        </h1>

      </div>
    </div>

    <div class="">

      <div class="col-xs-12">
          <div class="row">
          <div class="col-lg-12" style="text-align: right;">
              <button class="btn btn-theme" onclick="AddStock()"><i class="fa fa-plus"></i> Add Stationary</button>
          </div>
        </div>
        <br/>
        <div class="row">
            <form id="frm_filter" method="post" action="<?= route('stationarystock.excelreport')?>">
                
              <input type="hidden" value="{{csrf_token()}}" name="_token">
                <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                    <select class="form-control" id="card_category_filter" name="card_category_filter" title="Select Card Category" data-toggle="tooltip">
                            <option value="All" selected>ALL</option>
                            <option value="Grade Cards">Grade Cards</option>
                            <option value="Certificates">Certificates</option>
                        </select>
                </div>
                <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                    <div class="input-group date" data-provide="datepicker">
                        <input type="text" class="form-control datetimepicker" name="fromDate" id="fromDate" placeholder="Date Of Received From"  title="Date Of Received From" data-toggle="tooltip">
                        <div class="input-group-addon">
                            <span class="glyphicon glyphicon-th"></span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                    <div class="input-group date" data-provide="datepicker" value="">
                        <input type="text" class="form-control datetimepicker" name="toDate" id="toDate" placeholder="Date Of Received To"  title="Date Of Received To" data-toggle="tooltip">
                        <div class="input-group-addon">
                            <span class="glyphicon glyphicon-th"></span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                     <button type="button" name="submit" class="btn btn-sm btn-block form-btn" style="border:1px solid #2196f3;background-color: #03a9f4 !important;color:#fff;max-width: 250px;    margin: auto; " id="submitFilter"><i class="fa fa-filter"></i>Filter</button>
                </div>
                <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                     <button type="button" class="btn btn-sm btn-block form-btn" style="border:1px solid #FF9800 ;background-color: #FF9800  !important;color:#fff;max-width: 250px;    margin: auto; " id="clearFilter">Clear Filter</button>
                </div>
                <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                     <!-- <input type="submit" class="btn btn-sm btn-block form-btn" style="border:1px solid #4CAF50;background-color: #4CAF50  !important;color:#fff;max-width: 250px;    margin: auto; " value="Download Report"><i class="fa fa-file-excel-o"></i>  -->
                     <button type="submit" class="btn btn-sm btn-block form-btn" style="border:1px solid #4CAF50;background-color: #4CAF50  !important;color:#fff;max-width: 250px;    margin: auto; "><i class="fa fa-file-excel-o"></i>Download Report</button>
                </div>
            </form>
        </div>
        <hr>
        <table id="example" class="table table-hover" cellspacing="0" width="100%">
          <thead>
            <tr>
              <th>#</th>
              <th>Academic Year</th>
              <th>Date Of Received</th>
              <th>Serial No. From</th>
              <th>Serial No. To</th>
              <th>Quantity</th>
              <th>Card Category</th>
              <th>Added By</th>
              <th>Submitted Date Time</th>
            </tr>
          </thead>
          <tfoot>
          </tfoot>
        </table>
      </div>
    </div>
  </div>
 <!--   // End User information model --> 

<!--  // User Create Model -->  
@include('admin.raisoni.stationarystock.model')
<!--  // End User Create Model -->  

@stop
@section('script')

<script type="text/javascript">

$('.datetimepicker').datetimepicker( {
    maxDate: moment(),
    allowInputToggle: true,
    enabledHours : false,
    locale: moment().local('en'),
    format: 'DD-MM-YYYY',
    defaultDate: ''
}).val('');

   
// add Stock record
function AddStock()
{ 
    $('#addStationaryStock').modal('show');
    $('.add_stock').click(function(event) {

        $(".add_stock").attr('disabled','disabled'); 
        event.preventDefault();
        var create_path="<?= URL::route('stationarystock.store') ?>";
        var token="{{ csrf_token() }}";
        var method_type="post";

        $("#stockData").ajaxSubmit({
            url  : create_path,
            type : method_type,
            data : {'_token':token},
            beforeSubmit:function()
            {
                $(".clear_model").find('span').text('').end();
                $(".add_stocksave").addClass('fa fa-spinner fa-spin');
            },
            success:function(data)
            {
                if(data.success==true)
                {
                  $('#addStationaryStock').modal('hide');
                  toastr.success('Stock  successfully added');
                 loadDatatable();
                  $(".add_stock").removeAttr('disabled');
                  $(".add_stocksave").removeClass('fa fa-spinner fa-spin');  

                }
                if(data.success==false){
                    toastr.error(data.message);
                    $(".add_stock").removeAttr('disabled');
                    $(".add_stocksave").removeClass('fa fa-spinner fa-spin');
                }
            },
            error:function(resobj)
            {
                $.each(resobj.responseJSON.errors, function(k,v){
                   $('#'+k+'_error').text(v);
                });
                $(".add_stock").removeAttr('disabled'); 
                $(".add_stocksave").removeClass('fa fa-spinner fa-spin');  
            },
        });      
    });

} 

function toFixed(x) {
    if (Math.abs(x) < 1.0) {
        var e = parseInt(x.toString().split('e-')[1]);
        if (e) {
            x *= Math.pow(10,e-1);
            x = '0.' + (new Array(e)).join('0') + x.toString().substring(2);
        }
    } 
    else {
        var e = parseInt(x.toString().split('+')[1]);
        if (e > 20) {
            e -= 20;
            x /= Math.pow(10,e);
            x += (new Array(e+1)).join('0');
        }
    }
    return x;
}

function getSeriesCount()
{
    var series1 = $('#serial_no_from').val(),
        series2 = $('#serial_no_to').val();



    var numberPattern = /\d+/g;

    /*if(series1.trim() == "" || series2.trim() == "")
        return '';*/
    var num1 = series1.trim().match(/\d+/g);
    if(num1 == null || num1.length != 1)
        return '';


    var num2 = series2.trim().match(/\d+/g);
    if(num2 == null || num2.length != 1)
        return '';


    console.log(num1[0])
    console.log(num2[0])
    if(Number(num2[0]) > Number(num1[0]))
    {

        return num2[0] - num1[0] + 1;
    }else{
      toastr["error"]('From number should be less than to number.');
    }
    return '';
}

$('#serial_no_from').change(function() {
    $('#quantity').val(toFixed(getSeriesCount()));
});
$('#serial_no_to').change(function() {
    $('#quantity').val(toFixed(getSeriesCount()));
});     
// datatable
function loadDatatable(){
    $('#example').DataTable().destroy();
    var oTable = $('#example').DataTable( {
        'dom':  "<'row'<'col-sm-5' p><'col-sm-3' ><'col-sm-4'f>>",
        "bProcessing": false,
        "bServerSide": true,
        "autoWidth": true,
        "bDestroy": true,
        "bInfo":true,
        "aaSorting": [
        [8, "desc"]
        ],
        "sAjaxSource":"<?= URL::route('stationarystock.index') ?>",
        "fnServerParams": function(aoData) {
            var form_data = $('#frm_filter').serializeArray();
            $.each(form_data, function(i, val) {
              aoData.push(val);
            });
        },
        "aoColumns":[
            {mData: "rownum", bSortable:false},
            {
            mData: "academic_year",
            bSortable:true,
            "sClass": "text-center",
            },
            {mData: "date_of_received",bSortable:true,"sClass": "text-center"},
            {mData: "serial_no_from",bSortable:true,"sClass": "text-center"},
            {mData: "serial_no_to",bSortable:true,"sClass": "text-center"},
            {mData: "quantity",bSortable:true,"sClass": "text-center",

                mRender:function(v, t, o){
                    return "<span class='badge'>"+o['quantity']+"</span>";
                },
            },
            {mData: "card_category",bSortable:true,"sClass": "text-center"},
            {mData: "added_by",bSortable:true,"sClass": "text-center"},   
            {mData: "created_at",bSortable:true,"sClass": "text-center"}
        ]
    }); 
    // oTable.ajax.reload();
    oTable.on('draw',function(){
        
        $(".loader").addClass('hidden');
    });
}
loadDatatable();

$("#submitFilter").click(function(){
    var fromDate=$('#fromDate').val();
    var toDate=$('#toDate').val();
    var card_category_filter=$('#card_category_filter').val();
    if((fromDate!=''&&toDate!='')){
        loadDatatable();
    }else{
         toastr.error("Please select from & to date!");
    }
})

$("#generateReport").click(function(){
    var fromDate=$('#fromDate').val();
    var toDate=$('#toDate').val();
    var card_category_filter=$('#card_category_filter').val();
    $.ajax({
        url:'<?= route('stationarystock.excelreport')?>',
        type:'GET',
        data:{'fromDate':fromDate,'toDate':toDate,'card_category_filter':card_category_filter},
        success:function(response){
        }
    })
})

$("#clearFilter").click(function(){
    $('#card_category_filter').val('All');
    $('.datetimepicker').datetimepicker( {
        maxDate: moment(),
        allowInputToggle: true,
        enabledHours : false,
        locale: moment().local('en'),
        format: 'DD-MM-YYYY',
        defaultDate: ''
    }).val('');
    loadDatatable();
});
// clear model data
$('.clear_model').on('hidden.bs.modal', function () {
    $(this).find("input,textarea,select").val('').end();
    $(this).find('span').text('').end();
});

// allow only number
$(".allow_number").keypress(function(h){
    var keyCode =h.which ? h.which : h.keyCode
       if (!(keyCode >= 48 && keyCode <= 57)) {
             return !1;
           }
 });
</script>
@stop
@section('style')
<style type="text/css">
.modal-body .row{ 
  border-bottom: 1px solid #e5e5e5;
  padding-bottom: 10px;
  padding-top: 10px;
}
.help-inline{
  color:red;
  font-weight:normal;
}

#example td{
  vertical-align:middle !important;
  padding:15px 10px;
}
#example tr.sys-admin{
  background:#b3e5fc !important;
    font-weight:bold;
}

#example tr.sys-admin .green, #example tr.sys-admin .red, #example tr.sys-admin .yellow, #example tr.sys-admin .blue, #example tr.sys-admin .grey{
  color:#283593;
}

#example .green, #example  .red, #example .yellow, #example .blue{
  cursor:pointer;
}

.grey{
  color:#444;
}

.active-student{
  border-left:3px solid #5CB85C !important;
}
.inactive-student{
  border-left:3px solid #D9534F !important;
}

.nav-pills>li.active>a, .nav-pills>li.active>a:focus{
  background:#0052CC;
  color:#fff;
  border:1px solid #0052CC;
}


.nav-pills>li.active>a:hover, .nav-pills>li>a:focus, .nav-pills>li>a:hover
{
  background:#fff;
  background:#ddd;
  border-radius:0;
  padding:10px 20px;
  color:#333;
  border-radius:2px;
  border:1px solid #ddd;
}

.nav-pills>li>a, .nav-pills>li>a
{
  background:#fff;
  color:#aaa;
  border-radius:0;
  padding:10px 20px;
  border-radius:2px;
  margin-bottom:20px;
  border:1px solid #ddd;
}

#example_length label{
  display:none;
}

.active .success{
  background:#5CB85C !important;
  border:1px solid #5CB85C !important;
  color:#fff !important;
}

.active .failed{
  background:#D9534F !important;
  border:1px solid #D9534F !important;
  color:#fff !important;
}
.iconModalCss {
    margin-top: 20px !important;
}
</style>

@stop