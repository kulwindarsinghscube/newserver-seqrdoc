@extends('admin.layout.layout')
@section('content')
<div class="container">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header"><i class="fa fa-chain-broken" aria-hidden="true"></i> Damaged Stock
                <ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;margin-top:10px;">{{ Breadcrumbs::render('damagedstock') }}</ol>
                <i class="fa fa-info-circle iconModalCss" title="User Manual" id="damagedStockClick"></i>
            </h1>
        </div>
    </div>



    <div class="">

        <div class="col-xs-12">
            <div>
                <form id="frm_filter" method="post" action="<?= route('damagedstock.excelreport')?>">
                    <div class="row" style="margin-bottom: 10px;">
                    <!-- <div class="col-lg-12" style="text-align: right;margin-bottom: 10px;"> -->
                        <div class="col-lg-8 col-md-8 col-sm-12 col-xs-12">
                        </div>
                        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <button type="submit" class="btn btn-sm btn-block form-btn" style="border:1px solid #4CAF50;background-color: #4CAF50  !important;color:#fff;max-width: 250px;  margin: auto; "><i class="fa fa-file-excel-o"></i> Download Report</button>
                        </div>
                        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <button type="button" class="btn btn-sm btn-block form-btn" onclick="AddCard()" style="border:1px solid #0052cc;background-color: #0052cc  !important;color:#fff;max-width: 250px;  margin: auto; "><i class="fa fa-plus"></i> Add Card</button>
                        </div>
                    <!-- </div> -->
                    </div>

                    <div class="row">
                            <input type="hidden" value="{{csrf_token()}}" name="_token">
                            <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                                <select class="form-control" name="type_damaged_filter" id="type_damaged_filter"  title="Select Damaged Reason" data-toggle="tooltip">
                                    <option value="All" selected>Select Type</option>
                                    <option value="Cancel">Cancel</option>
                                    <option value="Corrections">Corrections</option>
                                    <option value="Damaged">Damaged</option>
                                    <option value="Duplicate">Duplicate</option>
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                                <select class="form-control" id="card_category_filter" name="card_category_filter" title="Select Card Category" data-toggle="tooltip">
                                    <option value="All" selected>ALL</option>
                                    <option value="Grade Cards">Grade Cards</option>
                                    <option value="Certificates">Certificates</option>
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                                <div class="input-group date" data-provide="datepicker">
                                    <input type="text" class="form-control datetimepicker" name="fromDate" id="fromDate" placeholder="Entry Date From" title="Entry Date From" data-toggle="tooltip">
                                    <div class="input-group-addon">
                                        <span class="glyphicon glyphicon-th"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                                <div class="input-group date" data-provide="datepicker" value="">
                                    <input type="text" class="form-control datetimepicker" name="toDate" id="toDate" placeholder="Entry Date To" title="Entry Date To" data-toggle="tooltip">
                                    <div class="input-group-addon">
                                        <span class="glyphicon glyphicon-th"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                                <button type="button" class="btn btn-sm btn-block form-btn" style="border:1px solid #2196f3;background-color: #03a9f4 !important;color:#fff;max-width: 250px;    margin: auto; " id="submitFilter"><i class="fa fa-filter"></i> Filter</button>
                            </div>
                            <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                                <button type="button" class="btn btn-sm btn-block form-btn" style="border:1px solid #FF9800 ;background-color: #FF9800  !important;color:#fff;max-width: 250px;    margin: auto; " id="clearFilter">Clear Filter</button>
                            </div>
                            <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">


                            </div>
                    </div>
                </form>
            </div>
            <hr>
            <table id="example" class="table table-hover" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Serial No.</th>
                        <th>Type</th>
                        <th>Remark</th>
                        <th>Registration No. To</th>
                        <th>Entry Date Time</th>
                        <th>Card Category</th>
                        <th>Added By</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tfoot>
                </tfoot>
            </table>
        </div>
    </div>
</div>
 <!--   // End User information model --> 

<div id="viewDetailsModel" class="modal fade" role="dialog">
  <div class="modal-dialog modal-lg">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Details</h4>
      </div>
      <div class="modal-body">
        <div class="row">
            <div class="col-lg-2" style="font-weight: 600;">Exam Name</div><div class="col-lg-10">:&nbsp;<span id="examNameView"></span></div>
        </div>
        <div class="row">
            <div class="col-lg-2" style="font-weight: 600;">Degree Name</div><div class="col-lg-10">:&nbsp;<span id="degreeNameView"></span></div>
        </div>
            <div class="row">
            <div class="col-lg-2" style="font-weight: 600;">Branch Name</div><div class="col-lg-10">:&nbsp;<span id="branchNameView"></span></div>
        </div>
            <div class="row" style="border: 0;">
            <div class="col-lg-2" style="font-weight: 600;">Semester Name</div><div class="col-lg-10">:&nbsp;<span id="semesterNameView"></span></div>
        </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-theme" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>

<!--  // User Create Model -->  
@include('admin.raisoni.damagedstock.model')
<!--  // End User Create Model -->  

@stop
@section('script')

<script type="text/javascript">
$('[data-toggle="tooltip"]').tooltip();
console.log(5)
// get Exam Name 
var ajaxUrl="{{ URL::route('raisoniMaster.get.damagedStockExam') }}";
var token="{{ csrf_token() }}";
$.get(ajaxUrl,{'_token':token},function(data) {
console.log(data)
    $.each(data,function(index, el) {
        $("#exam").append('<option value='+el.session_no+'>'+el.session_name+'</option>');
    });
});  

// get Degree Name 
var ajaxUrl="{{ URL::route('raisoniMaster.get.damagedStockDegree') }}";
var token="{{ csrf_token() }}";
$.get(ajaxUrl,{'_token':token},function(data) {

    $.each(data,function(index, el) {
        $("#degree").append('<option value='+el.id+'>'+el.degree_name+'</option>');
    });
}); 
$("#degree").change(function(){
    $("#branch").html('<option value="All" disabled selected>Select Branch</option>')
    getBranches(this.value,0);
});
function getBranches(degree_id,branch_id){
    // get Branch Name 
    var ajaxUrl="{{ URL::route('raisoniMaster.get.damagedStockBranch') }}";
    var token="{{ csrf_token() }}";
    $.get(ajaxUrl,{'_token':token,'degree_id':degree_id},function(data) {

        $.each(data,function(index, el) {
            $("#branch").append('<option value='+el.id+'>'+el.branch_name_long+'</option>');
        });
    }); 
}

// get Semester Name 
var ajaxUrl="{{ URL::route('raisoniMaster.get.damagedStockSemester') }}";
var token="{{ csrf_token() }}";
$.get(ajaxUrl,{'_token':token},function(data) {

    $.each(data,function(index, el) {
        $("#semester").append('<option value='+el.id+'>'+el.semester_name+'</option>');
    });
});  

$('.datetimepicker').datetimepicker( {
    maxDate: moment(),
    allowInputToggle: true,
    enabledHours : false,
    locale: moment().local('en'),
    format: 'DD-MM-YYYY',
    defaultDate: ''
}).val('');

   
// add Card
function AddCard()
{ 
    $('#addCard').modal('show');
    $('.add_stock').click(function(event) {

        $(".add_stock").attr('disabled','disabled'); 
        event.preventDefault();
        var create_path="<?= URL::route('damagedstock.store') ?>";
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
                  $('#addCard').modal('hide');
                  toastr.success(data.message);
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
        "sAjaxSource":"<?= URL::route('damagedstock.index') ?>",
        "fnServerParams": function(aoData) {
            var form_data = $('#frm_filter').serializeArray();
            console.log(form_data)
            $.each(form_data, function(i, val) {
              aoData.push(val);
            });
        },
        "aoColumns":[
            {mData: "rownum", bSortable:false},
            {
            mData: "serial_no",
            bSortable:true,
            "sClass": "text-center",
            },
            {mData: "type",bSortable:true,"sClass": "text-center"},
            {mData: "remark",bSortable:true,"sClass": "text-center"},
            {mData: "registration_no",bSortable:true,"sClass": "text-center"},
            {mData: "created_at",bSortable:true,"sClass": "text-center",
            },
            {mData: "card_category",bSortable:true,"sClass": "text-center"},
            {mData: "added_by",bSortable:true,"sClass": "text-center"},   
            {mData: "id",bSortable:true,"sClass": "text-center",

                    mRender:function(v, t, o){
                        var act_html;
          
                         act_html ='<a onclick="card_info('+o['id']+')"><i class="fa fa-info-circle fa-lg blue"></i></a>&nbsp';

                         act_html +='&nbsp;&nbsp;<a onclick="deletepath('+o['id']+')"><i class="fa fa-trash fa-lg red"></i></a>';

                        return act_html;
                    },
            }
        ]
    }); 
    // oTable.ajax.reload();
    oTable.on('draw',function(){
        
        $(".loader").addClass('hidden');
    });
}
loadDatatable();


 // get card information 
function card_info(card_id)
{
    var show_info="{{ URL::route('damagedstock.show',array(':id')) }}";
    show_info=show_info.replace(':id',card_id);
    var token="{{ csrf_token() }}";
    $.get(show_info,{ '_token':token} ,function(data) {
        $('#viewDetailsModel').modal('show'); 
        var data = data[0]
       var exam_name =  data.session_name;
        var degree_name = data.degree_name;
        var branch_name = data.branch_name_long;
        var semester_name = data.semester_name;

        if(exam_name!=''&&exam_name!=null){
            $('#examNameView').html(exam_name)
        }else{
            $('#examNameView').html(' - ');
        }
        if(degree_name!=''&&degree_name!=null){
            $('#degreeNameView').html(degree_name)
        }else{
            $('#degreeNameView').html(' - ');
        }
        if(branch_name!=''&&branch_name!=null){
            $('#branchNameView').html(branch_name)
        }else{
            $('#branchNameView').html(' - ');
        }
        if(semester_name!=''&&semester_name!=null){
            $('#semesterNameView').html(semester_name)
        }else{
            $('#semesterNameView').html(' - ');
        }
    })
}

// Send Ajax Request to delete Record      
function deletepath(fm_id)
{
  var delete_path="<?=URL::route('damagedstock.delete',array(':id')) ?>";
  delete_path=delete_path.replace(':id',fm_id);
  var token="{{ csrf_token() }}";
  var method_type="get";

  bootbox.confirm("Are you sure you want to delete?",function(result){  
       if(result)
       {
            $.ajax({
               url:delete_path,
               type:method_type,
               data:{'_token':token},
               success:function(data){  
                 if(data.success==true)
                 {
                     toastr.success('Deleted successfully');
                     loadDatatable();
                 }
               },
            });
       }

  });
}


$("#submitFilter").click(function(){
    var type_damaged=$('#type_damaged_filter').val();
    var fromDate=$('#fromDate').val();
    var toDate=$('#toDate').val();
    var card_category_filter=$('#card_category_filter').val();
    if((fromDate!=''&&toDate!='')||(type_damaged!=''&&fromDate==''&&toDate=='')||(card_category_filter!=''&&fromDate==''&&toDate=='')){
        loadDatatable();
    }else{
        toastr["error"]("Please select from & to date!");
    }
})

$("#clearFilter").click(function(){
    $('#type_damaged_filter').val('All');
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
// allow only character
$(".allow_character_number").keypress(function(e) {
  
    document.all ? k = e.keyCode : k = e.which;
    return ((k > 64 && k < 91) || (k > 96 && k < 123) || k == 8 || k == 32 || (k >= 48 && k <= 57));
    
});
</script>
@stop
@section('style')
<style type="text/css">
#viewDetailsModel .modal-body .row{ border-bottom: 1px solid #e5e5e5;
    padding-bottom: 10px;
    padding-top: 10px;
}
</style>
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