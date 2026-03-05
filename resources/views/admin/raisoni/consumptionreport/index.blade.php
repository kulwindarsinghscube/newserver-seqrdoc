@extends('admin.layout.layout')
@section('content')
<div class="container">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header"><i class="fa fa-align-justify" aria-hidden="true"></i> Consumption Stock
                <ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;margin-top:10px;">{{ Breadcrumbs::render('consumptionreport') }}</ol>
		<i class="fa fa-info-circle iconModalCss" title="User Manual" id="consumptionReportClick"></i>            
</h1>
        </div>
    </div>



    <div class="">

        <div class="col-xs-12">
            <form id="frm_filter">
                <div class="row">
                    <div class="col-lg-1 col-md-1 col-sm-12 col-xs-12">
                        <label>Session</label>
                    </div>
                    <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                        <select class="form-control" name="exam" id="exam" data-rule-required="true">
                            <option value="" readonly selected>Select Session</option>
                        </select>
                    </div>
                    <div class="col-lg-1 col-md-1 col-sm-12 col-xs-12">
                        <label>Degree</label>
                    </div>
                    <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                        <select class="form-control" name="degree" id="degree" data-rule-required="true">
                            <option value="" readonly selected>Select Degree</option>
                        </select>
                    </div>
                    <div class="col-lg-1 col-md-1 col-sm-12 col-xs-12">
                        <label>Branch</label>
                    </div>
                    <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                        <select class="form-control" name="branch" id="branch" data-rule-required="true">
                            <option value="" readonly selected>Select Branch</option>
                        </select>
                    </div>
                    <div class="col-lg-1 col-md-1 col-sm-12 col-xs-12">
                        <label>Scheme</label>
                    </div>
                    <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                        <select class="form-control" name="scheme" id="scheme" data-rule-required="true">
                            <option value="" readonly selected>Select Scheme</option>
                        </select>
                    </div>
                </div>
                <div class="row" style="margin-top: 10px;">
                    <div class="col-lg-1 col-md-1 col-sm-12 col-xs-12">
                        <label>Term</label>
                    </div>
                    <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                        <select class="form-control" name="term" id="term" data-rule-required="true">
                            <option value="" readonly selected>Select Term</option>
                        </select>
                    </div>
                    <div class="col-lg-1 col-md-1 col-sm-12 col-xs-12">
                        <label>Student Type</label>
                    </div>
                    <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                        <select class="form-control" name="student_type" id="student_type" data-rule-required="true">
                            <option value="" readonly selected>Select Type</option>
                        </select>
                    </div>
                    <div class="col-lg-1 col-md-1 col-sm-12 col-xs-12">
                        <label>Section</label>
                    </div>
                    <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                        <select class="form-control" name="section" id="section" data-rule-required="true">
                            <option value="" readonly selected>Select Section</option>
                        </select>
                    </div>
                    <div class="col-lg-1 col-md-1 col-sm-12 col-xs-12">
                        <label>Card Type</label>
                    </div>
                    <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                        <select class="form-control" name="card_type" id="card_type" data-rule-required="true">
                            <option value="" readonly selected>Select Type</option>
                            <option value="Assigned">Assigned</option>
                            <option value="Non Assigned">Non Assigned</option>
                        </select>
                    </div>


                </div>
                <div class="row" style="margin-top: 10px;">
                    <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                         <button type="button" class="btn btn-sm btn-block form-btn" style="border:1px solid #2196f3;background-color: #03a9f4 !important;color:#fff;max-width: 150px; " id="submitFilter"> Show</button>
                    </div>
                </div>
                <hr>
            </form>

            <table id="example" class="table table-hover" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Roll No.</th>
                        <th>Section</th>
                        <th>Result No.</th>
                        <th>Enrollment No.</th>
                        <th>Registration No.</th>
                        <th>Name</th>
                        <th>Serial No.</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tfoot>
                </tfoot>
            </table>

            <div class="row" style="margin-top: 10px;margin-bottom: 20px;">


                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                         <button type="button" class="btn btn-sm btn-block form-btn update_serial_no" style="border:1px solid #4CAF50;background-color: #4CAF50 !important;color:#fff;max-width: 150px;margin: auto;display: none;"> Update Serial No</button>
                    </div>
                </div>
        </div>
    </div>
</div>
 <!--   // End User information model --> 

@stop
@section('script')

<script type="text/javascript">
$('[data-toggle="tooltip"]').tooltip();


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
        $("#term").append('<option value='+el.id+'>'+el.semester_name+'</option>');
    });
});  

// get Section Name 
var ajaxUrl="{{ URL::route('raisoniMaster.get.consumptionReportSection') }}";
var token="{{ csrf_token() }}";
$.get(ajaxUrl,{'_token':token},function(data) {

    $.each(data,function(index, el) {
        $("#section").append('<option value='+el.id+'>'+el.section_name+'</option>');
    });
});

// get Scheme Name 
var ajaxUrl="{{ URL::route('raisoniMaster.get.consumptionReportScheme') }}";
var token="{{ csrf_token() }}";
$.get(ajaxUrl,{'_token':token},function(data) {

    $.each(data,function(index, el) {
        $("#scheme").append('<option value='+el.scheme+'>'+el.scheme+'</option>');
    });
}); 

// get Student Type
var ajaxUrl="{{ URL::route('raisoniMaster.get.consumptionReportStudentType') }}";
var token="{{ csrf_token() }}";
$.get(ajaxUrl,{'_token':token},function(data) {

    $.each(data,function(index, el) {
        $("#student_type").append('<option value='+el.student_type+'>'+el.student_type+'</option>');
    });
}); 
 
// datatable
var oTable =$('#example').DataTable();
function loadDatatable(){
    $('#example').DataTable().destroy();
    $('.datatable-input-custom').remove();
    var oTable = $('#example').DataTable( {
        'dom':  "<'row'<'col-sm-5' p><'col-sm-3' ><'col-sm-4'f>>",
        "pageLength": 50,
        "bProcessing": false,
        "bServerSide": true,
        "autoWidth": true,
        "bDestroy": true,
        "bInfo":true,
        "aaSorting": [
        [8, "asc"]
        ],
        "sAjaxSource":"<?= URL::route('consumptionreport.index') ?>",
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
            mData: "roll_no",
            bSortable:true,
            "sClass": "text-center",
            },
            {mData: "section",bSortable:true,"sClass": "text-center"},
            {mData: "result_no",bSortable:true,"sClass": "text-center"},
            {mData: "enrollment_no",bSortable:true,"sClass": "text-center"},
            {mData: "registration_no",bSortable:true,"sClass": "text-center",
            },
            {mData: "student_name",bSortable:true,"sClass": "text-center"},
            {mData: "serial_no",bSortable:true,"sClass": "text-center",
                mRender:function(v, t, o){
                    if(o['serial_no'] != null && o['serial_no'] != 'null'){
                        var val=o['serial_no'];
                    }else{
                        var val='';
                    }
                    return "<input type='text' class='form-control datatable-input-custom' id='datatable-input-custom-"+o['id']+"' name='"+o['id']+"' data-id='"+o['id']+"' value='"+val+"'>";

                },
            },   
            {mData: "id",bSortable:true,"sClass": "text-center",

                    mRender:function(v, t, o){
                        return "<span class='btn btn-sm btn-info datatable-btn-custom'  data-id='"+o['id']+"'>Auto Fill </span>";

                    },
            }
        ]
    }); 
    // oTable.ajax.reload();
    oTable.on('draw',function(){
        
        $(".loader").addClass('hidden');
        var count = oTable.rows().count();
        if (count != 0)
        {
            $('.update_serial_no').show();
        }else{
            $('.update_serial_no').hide();
        }
    });

    oTable.on('click','.datatable-btn-custom',function(e){

        var id=$(this).data('id');
        var currentTextValue=$('#datatable-input-custom-'+id).val();

        if(currentTextValue!=''&&currentTextValue!='0'){

            var isCurrentTextFound=false;
            $('.datatable-input-custom').each(
                function(index){
                    var input = $(this);

                    if(isCurrentTextFound){
                        currentTextValue=parseInt(currentTextValue)+1;
                        input.val(currentTextValue);

                    }

                    if(input.attr('data-id')==id){

                         isCurrentTextFound=true;
                    }

                }
            );

        }else{
            toastr["error"]('Please enter serial no in corresponding textbox.')
        }

    });
}
loadDatatable();


$('.update_serial_no').click( function() {

    var formData = new FormData();

    $(".datatable-input-custom").each(function() {

        formData.append('recoredId[]', $(this).data('id'));
        formData.append('serialNo[]', $(this).val());

    });
    var token = "{{csrf_token()}}"
    formData.append('_token', token);
    $.ajax({
    url: '<?= route('consumptionreport.updateSerialNo')?>',
    type: 'POST',
    data: formData,
    success: function (data) {
        console.log(data.message)
        if(data.type == 'success'){
            toastr.success(data.message);


        }else{
            toastr.error(data.message);
        }
    },
    cache: false,
    contentType: false,
    processData: false,
    dataType:'JSON'
    });

    return false;
} );


$("#submitFilter").click(function(){
    loadDatatable();
})
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