@extends('admin.layout.layout')
@section('content')
<div class="container">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Consumption Report
                <ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;margin-top:10px;">{{ Breadcrumbs::render('consumptionreportexport') }}</ol>
                <i class="fa fa-info-circle iconModalCss" title="User Manual" id="consumptionReportExportClick"></i>
            </h1>
        </div>
    </div>



    <div class="">

        <div class="col-xs-12">
            @if(App\Helpers\SitePermissionCheck::isPermitted('consumptionreportexport.generateReportConsumption'))
            @if(App\Helpers\RolePermissionCheck::isPermitted('consumptionreportexport.generateReportConsumption'))
                <h4>Report 1 : List of students with grade card serial number for exam</h4>
                <form method="post" action="<?= route('consumptionreportexport.generateReportConsumption')?>">
                    <input type="hidden" value="{{csrf_token()}}" name="_token">
                    <div class="row">
                        <div class="col-lg-1 col-md-1 col-sm-12 col-xs-12">
                            <label>Session*</label>
                        </div>
                        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <select class="form-control" name="exam" id="exam" data-rule-required="true">
                                <option value="{{old('exam')}}" selected>Select Session</option>
                            </select>
                            <span id="exam_error" class="help-inline text-danger"><?=$errors->first('exam')?></span>
                        </div>
                        <div class="col-lg-1 col-md-1 col-sm-12 col-xs-12">
                            <label>Degree*</label>
                        </div>
                        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <select class="form-control" name="degree" id="degree" data-rule-required="true">
                                <option value="" selected>Select Degree</option>
                            </select>
                            <span id="degree_error" class="help-inline text-danger"><?=$errors->first('degree')?></span>
                        </div>
                        <div class="col-lg-1 col-md-1 col-sm-12 col-xs-12">
                            <label>Branch*</label>
                        </div>
                        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <select class="form-control" name="branch" id="branch" data-rule-required="true">
                                <option value="" selected>Select Branch</option>
                            </select>
                            <span id="branch_error" class="help-inline text-danger"><?=$errors->first('branch')?></span>
                        </div>
                        <div class="col-lg-1 col-md-1 col-sm-12 col-xs-12">
                            <label>Scheme*</label>
                        </div>
                        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <select class="form-control" name="scheme" id="scheme" data-rule-required="true">
                                <option value="" selected>Select Scheme</option>
                            </select>
                            <span id="scheme_error" class="help-inline text-danger"><?=$errors->first('scheme')?></span>
                        </div>
                    </div>
                    <div class="row" style="margin-top: 10px;">
                        <div class="col-lg-1 col-md-1 col-sm-12 col-xs-12">
                            <label>Term*</label>
                        </div>
                        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <select class="form-control" name="term" id="term" data-rule-required="true">
                                <option value="" selected>Select Term</option>
                            </select>
                            <span id="term_error" class="help-inline text-danger"><?=$errors->first('term')?></span>
                        </div>
                        <div class="col-lg-1 col-md-1 col-sm-12 col-xs-12">
                            <label>Section*</label>
                        </div>
                        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <select class="form-control" name="section" id="section" data-rule-required="true">
                                <option value="" selected>Select Section</option>
                            </select>
                            <span id="section_error" class="help-inline text-danger"><?=$errors->first('section')?></span>
                        </div>
                        <div class="col-lg-1 col-md-1 col-sm-12 col-xs-12">
                            <label>From Date</label>
                        </div>
                        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <div class="input-group date" data-provide="datepicker">
                                <input type="text" class="form-control datetimepicker" name="fromDate" id="fromDate" placeholder="Date From"  title="Date From" data-toggle="tooltip" value="{{old('fromDate')}}">
                                <div class="input-group-addon">
                                    <span class="glyphicon glyphicon-th"></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-1 col-md-1 col-sm-12 col-xs-12">
                            <label>To Date</label>
                        </div>
                        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <div class="input-group date" data-provide="datepicker" value="">
                                <input type="text" class="form-control datetimepicker" name="toDate" id="toDate" placeholder="Date To"  title="Date To" data-toggle="tooltip">
                                <div class="input-group-addon">
                                    <span class="glyphicon glyphicon-th"></span>
                                </div>
                            </div>
                            <span id="toDate_error" class="help-inline text-danger"><?=$errors->first('toDate')?></span>
                        </div>
                    </div>
                    <div class="row" style="margin-top: 10px;">

                        <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                        <button type="submit" class="btn btn-sm btn-block form-btn" style="border:1px solid #4CAF50;background-color: #4CAF50 !important;color:#fff;max-width: 150px; " id="downloadReport1"> Download Excel</button>
                        </div>
                    </div>
                </form>
            @endif
            @endif
            <hr>
            @if(App\Helpers\SitePermissionCheck::isPermitted('consumptionreportexport.generateReportConsumptionExam'))
            @if(App\Helpers\RolePermissionCheck::isPermitted('consumptionreportexport.generateReportConsumptionExam'))
                <h4>Report 2 : Summury of exam wise consumption</h4>
                <form method="post" action="<?= route('consumptionreportexport.generateReportConsumptionExam')?>">
                    <input type="hidden" value="{{csrf_token()}}" name="_token">
                    <div class="row">
                        <div class="col-lg-1 col-md-1 col-sm-12 col-xs-12">
                            <label>Session*</label>
                        </div>
                        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <select class="form-control" name="exam_filter2" id="exam_filter2" data-rule-required="true">
                                <option value="" selected>Select Session</option>
                            </select>
                            <span id="exam_filter2_error" class="help-inline text-danger"><?=$errors->first('exam_filter2')?></span>
                        </div>
                        <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                            <button type="submit" class="btn btn-sm btn-block form-btn" style="border:1px solid #4CAF50;background-color: #4CAF50 !important;color:#fff;max-width: 150px; " id="downloadReport2"> Download Excel</button>
                        </div>
                    </div>
                </form>
            @endif
            @endif
            <hr>
            @if(App\Helpers\SitePermissionCheck::isPermitted('consumptionreportexport.generateReportConsumptionBranch'))
            @if(App\Helpers\RolePermissionCheck::isPermitted('consumptionreportexport.generateReportConsumptionBranch'))
                <h4>Report 3 : Branch wise consumption</h4>
                <form method="post" action="<?= route('consumptionreportexport.generateReportConsumptionBranch')?>">
                    <input type="hidden" value="{{csrf_token()}}" name="_token">
                    <div class="row">
                        <div class="col-lg-1 col-md-1 col-sm-12 col-xs-12">
                            <label>Branch*</label>
                        </div>
                        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <select class="form-control" name="branch_filter3" id="branch_filter3" data-rule-required="true">
                                <option value="" selected>Select Branch</option>
                            </select>
                            <span id="branch_filter3_error" class="help-inline text-danger"><?=$errors->first('branch_filter3')?></span>
                        </div>
                        <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                            <button type="submit" class="btn btn-sm btn-block form-btn" style="border:1px solid #4CAF50;background-color: #4CAF50 !important;color:#fff;max-width: 150px; " id="downloadReport3"> Download Excel</button>
                        </div>
                    </div>
                </form>
            @endif
            @endif
            <hr>
            @if(App\Helpers\SitePermissionCheck::isPermitted('consumptionreportexport.generateReportConsumptionSemester'))
            @if(App\Helpers\RolePermissionCheck::isPermitted('consumptionreportexport.generateReportConsumptionSemester'))
                <h4>Report 4 : Semester wise consumption</h4>
                <form method="post" action="<?= route('consumptionreportexport.generateReportConsumptionSemester')?>">
                    <input type="hidden" value="{{csrf_token()}}" name="_token">
                    <div class="row">
                        <div class="col-lg-1 col-md-1 col-sm-12 col-xs-12">
                            <label>Semester*</label>
                        </div>
                        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <select class="form-control" name="term_filter4" id="term_filter4" data-rule-required="true">
                                <option value="" selected>Select Semester</option>
                            </select>
                            <span id="term_filter4_error" class="help-inline text-danger"><?=$errors->first('term_filter4')?></span>
                        </div>
                        <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                            <button type="submit" class="btn btn-sm btn-block form-btn" style="border:1px solid #4CAF50;background-color: #4CAF50 !important;color:#fff;max-width: 150px; " id="downloadReport4"> Download Excel</button>
                        </div>
                    </div>
                </form>
            @endif
            @endif
            <hr>
            @if(App\Helpers\SitePermissionCheck::isPermitted('consumptionreportexport.generateReportConsumptionAllCount'))
            @if(App\Helpers\RolePermissionCheck::isPermitted('consumptionreportexport.generateReportConsumptionAllCount'))
                <h4>Report 5 : Balance Grade Cards</h4>
                <form method="post" action="<?= route('consumptionreportexport.generateReportConsumptionAllCount')?>">
                    <input type="hidden" value="{{csrf_token()}}" name="_token">
                    <div class="row" style="margin-bottom: 40px;">
                        <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                            <button type="submit" class="btn btn-sm btn-block form-btn" style="border:1px solid #4CAF50;background-color: #4CAF50 !important;color:#fff;max-width: 150px; " id="downloadReport5"> Download Excel</button>
                        </div>
                    </div>
                </form>
            @endif
            @endif
        </div>
    </div>
</div>
 <!--   // End User information model --> 

<style type="text/css">
    .iconModalCss {
        margin-top: 20px !important;
    }
</style>
@stop
@section('script')

<script type="text/javascript">
$('[data-toggle="tooltip"]').tooltip();

// get Degree Name 
var ajaxUrl="{{ URL::route('raisoniMaster.get.damagedStockDegree') }}";
var token="{{ csrf_token() }}";
$.get(ajaxUrl,{'_token':token},function(data) {

    $.each(data,function(index, el) {
        $("#degree").append('<option value="'+el.degree_name+'">'+el.degree_name+'</option>');
    });
}); 

$("#degree").change(function(){
    $("#branch").html('<option value="All" disabled selected>Select Branch</option>')
    console.log(this.value)
    getBranches(this.value,0);
});

function getBranches(degree_id,branch_id){
    // get Branch Name 
    var ajaxUrl="{{ URL::route('raisoniMaster.get.getDegreeBranchName') }}";
    var token="{{ csrf_token() }}";
    $.get(ajaxUrl,{'_token':token,'degree_id':degree_id},function(data) {

        $.each(data,function(index, el) {
            $("#branch").append('<option value="'+el.branch_name_long+'">'+el.branch_name_long+'</option>');
        });
    }); 
}

var ajaxUrl="{{ URL::route('raisoniMaster.get.consumptionReportExportBranch') }}";
var token="{{ csrf_token() }}";
$.get(ajaxUrl,{'_token':token},function(data) {

    $.each(data,function(index, el) {
        $("#branch_filter3").append('<option value="'+el.branch_name_long+'">'+el.branch_name_long+'</option>');
    });
}); 


// get Exam Name 
var ajaxUrl="{{ URL::route('raisoniMaster.get.damagedStockExam') }}";
var token="{{ csrf_token() }}";
$.get(ajaxUrl,{'_token':token},function(data) {
console.log(data)
    $.each(data,function(index, el) {
        $("#exam").append('<option value="'+el.session_name+'">'+el.session_name+'</option>');
        $("#exam_filter2").append('<option value="'+el.session_name+'">'+el.session_name+'</option>');
    });
});  


// get Semester Name 
var ajaxUrl="{{ URL::route('raisoniMaster.get.damagedStockSemester') }}";
var token="{{ csrf_token() }}";
$.get(ajaxUrl,{'_token':token},function(data) {

    $.each(data,function(index, el) {
        $("#term").append('<option value="'+el.semester_name+'">'+el.semester_name+'</option>');
        $("#term_filter4").append('<option value="'+el.semester_name+'">'+el.semester_name+'</option>');
    });
});  

// get Section Name 
var ajaxUrl="{{ URL::route('raisoniMaster.get.consumptionReportSection') }}";
var token="{{ csrf_token() }}";
$.get(ajaxUrl,{'_token':token},function(data) {

    $.each(data,function(index, el) {
        $("#section").append('<option value="'+el.section_name+'">'+el.section_name+'</option>');
    });
});

// get Scheme Name 
var ajaxUrl="{{ URL::route('raisoniMaster.get.consumptionReportScheme') }}";
var token="{{ csrf_token() }}";
$.get(ajaxUrl,{'_token':token},function(data) {

    $.each(data,function(index, el) {
        console.log(el)
        $("#scheme").append('<option value="'+el.scheme+'">'+el.scheme+'</option>');
    });
}); 

// get Student Type
var ajaxUrl="{{ URL::route('raisoniMaster.get.consumptionReportStudentType') }}";
var token="{{ csrf_token() }}";
$.get(ajaxUrl,{'_token':token},function(data) {

    $.each(data,function(index, el) {
        $("#student_type").append('<option value="'+el.student_type+'">'+el.student_type+'</option>');
    });
}); 


</script>
<script type="text/javascript">
    $(document).ready(function(){
        $('.datetimepicker').datetimepicker( {
        maxDate: moment(),
        allowInputToggle: true,
        enabledHours : false,
        locale: moment().local('en'),
        format: 'DD-MM-YYYY',
        defaultDate: ''
    }).val('');
});
</script>
@stop