<?php $__env->startSection('content'); ?>

<?php
    $domain = \Request::getHost();
    $subdomain = explode('.', $domain);
    // $root_path = public_path() . '\\' . $subdomain[0];
    // $profilePath = $root_path . '/ttd_images/photos/';

    $protocol = isset($_SERVER["HTTPS"]) ? 'https' : 'http';
    $path = $protocol.'://'.$subdomain[0].'.'.$subdomain[1].'.com/';
    $profilePath=$path.$subdomain[0]."/ttd_images/photos/";

?>

    <div class="container">
        <div class="col-xs-12">
            <div class="clearfix">
                <div id="">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-lg-12">
                                <h1 class="page-header"><i class="fa fa-file-text-o"></i>Temporary Travel Document
                                    <ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;"><?php echo e(Breadcrumbs::render('ttdcertificate')); ?></ol>
                                </h1>
                            </div>
                        </div>
                        <ul class="nav nav-pills" id="pills-filter">
                            <li class="active"><a id="open-pill" data-toggle="pill" href="#open" class="open"><i class="fa fa-fw fa-lg fa-circle"></i> Open </a></li>
    			            <li><a id="approve-pill"data-toggle="pill" href="#approve" class="approve"><i class="fa fa-fw fa-lg fa-check"></i> Approved</a></li>
    			            <li><a id="reject-pill"data-toggle="pill" href="#reject" class="reject"><i class="fa fa-fw fa-lg fa-times"></i> Reject</a></li>

                            <li><a id="correction-pill"data-toggle="pill" href="#correction" class="correction"><i class="fa fa-fw fa-lg fa-edit"></i> Correction</a></li>

                            <li style="float: right;">
                                <a class="btn btn-theme" href="<?php echo e(route('ttd.create')); ?>" style="background-color: #0052cc;color:white;"><i class="fa fa-plus"></i> Create New Certificate</a>
                            </li>
                        </ul>
                        <div class="tab-content">

                            <div class="tab-pane active" id="open">
                                <table id="ttd-open-table" class="table table-hover display" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>TD Number</th>
                                            <th>Full Name</th>
                                            <th>Date of Birth</th>
                                            <th>Place of Birth </th>
                                            <th>Sex</th>
                                            <th>Nationality</th>
                                            <th>Address Country Residance</th>
                                            <th>Name of Mother</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tfoot>
                                    </tfoot>
                                </table>
                            </div>
                            
                            <div class="tab-pane" id="approve">
                                <table id="ttd-approve-table" class="table table-hover display" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>TD Number</th>
                                            <th>Full Name</th>
                                            <th>Date of Birth</th>
                                            <th>Place of Birth </th>
                                            <th>Sex</th>
                                            <th>Nationality</th>
                                            <th>Address Country Residance</th>
                                            <th>Name of Mother</th>
                                            <th>Document File</th>
                                        </tr>
                                    </thead>
                                    <tfoot>
                                    </tfoot>
                                </table>
                            </div>

                            <div class="tab-pane" id="reject">
                                <table id="ttd-reject-table" class="table table-hover display" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>TD Number</th>
                                            <th>Full Name</th>
                                            <th>Date of Birth</th>
                                            <th>Place of Birth </th>
                                            <th>Sex</th>
                                            <th>Nationality</th>
                                            <th>Address Country Residance</th>
                                            <th>Name of Mother</th>
                                        </tr>
                                    </thead>
                                    <tfoot>
                                    </tfoot>
                                </table>
                            </div>

                            <div class="tab-pane" id="correction">
                                <table id="ttd-correction-table" class="table table-hover display" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>TD Number</th>
                                            <th>Full Name</th>
                                            <th>Date of Birth</th>
                                            <th>Place of Birth </th>
                                            <th>Sex</th>
                                            <th>Nationality</th>
                                            <th>Address Country Residance</th>
                                            <th>Name of Mother</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tfoot>
                                    </tfoot>
                                </table>
                            </div>

                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>



    <!-- Popup Modal -->
    <div class="modal fade clear_model" id="ttdPopup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel">TTD Details</h4>

                    
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p>TD Number: <span id="td_number"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p>Date: <span id="date"></span></p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <p>Full Name: <span id="full_name"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p>Mother Name: <span id="mother_name"></span></p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <p>Sex: <span id="sex"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p>Date of Birth: <span id="dob"></span></p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <p>Place Of Birth: <span id="pob"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p>Nationality: <span id="nationality"></span></p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <p>Face Marks: <span id="face_marks"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p>Address of Country Residence: <span id="address"></span></p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <p>Date of Issue: <span id="doi"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p>Issuing Authority: <span id="issuing_authority"></span></p>
                        </div>
                    </div>


                    <div class="row">
                        <div class="col-md-6">
                            <p>Valid Unit: <span id="valid_unit"></span></p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <p>Photo: <span id="photo"></span></p>
                        </div>
                    </div>


                    <br>
                    <form method="post" id="ttdData">
                        <input type="hidden" name="ttd_id" id="ttd_id" value="">
                        <div class="form-group radioButtonDiv">
                            <label class="radio-inline">
                                <input type="radio" name="status" class="status" value="reject"> Reject
                            </label>

                            <label class="radio-inline">
                                <input type="radio" name="status" class="status" value="approve"> Approve
                            </label>

                            <label class="radio-inline">
                                <input type="radio" name="status" class="status" value="correction"> Correction
                            </label>
                        </div>

                        <div class="form-group commentDiv" style="display: none;">
                            <textarea class="form-control" name="comment" id="comment" placeholder="Enter Comment here"> </textarea>
                            <span id="comment_error" class="help-inline text-danger"></span>
                        </div>

                        <div class="form-group clearfix">
                            <button type="submit" class="btn btn-theme col-lg-3 col-md-2 col-sm-12 col-xs-12 col-md-offset-4" id="TtdUpdate"><i class="loadsave"></i> <span id="TtdUpdateText">Update Status </span> </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!--  -->

    <!-- Edit Popup -->
    <div class="modal fade clear_model" id="ttdEditPopup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel">TTD Details</h4>
                    <p style="margin-bottom: 0;margin-top:5px;">Correction : <span id="correctionMsg"></span> </p>
                </div>
                <div class="modal-body">
                    <form method="post" id="ttdEditData">
                        <input type="hidden" name="edit_ttd_id" id="edit_ttd_id" value="">

                        <div class="row">
                            <div class="col-md-6">
                                <label>TD Number:</label>
                                <input class="form-control" name="edit_td_number" id="edit_td_number" placeholder="Enter td_number here" value="">
                                
                                <span id="edit_td_number_error" class="help-inline text-danger"></span>

                            </div>
                            <div class="col-md-6">
                                <label>Date:</label>
                                <input type="date" name="edit_date" class="form-control" id="edit_date" placeholder="Enter Date" value="">
                                <span id="edit_date_error" class="help-inline text-danger"></span>

                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <label>Full Name:</label>
                                <input type="text" name="edit_full_name" class="form-control" id="edit_full_name" placeholder="Enter Full Name" value="">
                                <span id="edit_full_name_error" class="help-inline text-danger"></span>

                            </div>
                            <div class="col-md-6">
                                <label>Mother Name:</label>
                                <input type="text" name="edit_mother_name" class="form-control" id="edit_mother_name" placeholder="Enter Mother Name" value="">
                                <span id="edit_mother_name_error" class="help-inline text-danger"></span>


                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="sex" class="form-label">Sex</label>
                                <select class="form-control" id="edit_sex" name="edit_sex">
                                    <option value="">Select Gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                                <span id="edit_sex_error" class="help-inline text-danger"></span>

                            </div>
                            <div class="col-md-6 form-group">
                                <label>Date of Birth:</label>
                                <input type="date" name="edit_dob" class="form-control" id="edit_dob" placeholder="Enter Date of Birth" value="">
                                <span id="edit_dob_error" class="help-inline text-danger"></span>

                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Place Of Birth:</label>
                                <input type="text" name="edit_pob" class="form-control" id="edit_pob" placeholder="Enter Place Of Birth" value="">
                                <span id="edit_pob_error" class="help-inline text-danger"></span>

                            </div>
                            <div class="col-md-6 form-group">
                                <label>Nationality:</label>
                                <input type="text" name="edit_nationality" class="form-control" id="edit_nationality" placeholder="Enter Nationality" value="">
                                <span id="edit_nationality_error" class="help-inline text-danger"></span>


                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Face Mark:</label>
                                <input type="text" name="edit_face_marks" class="form-control" id="edit_face_marks" placeholder="Enter Face Mark" value="">
                                <span id="edit_face_marks_error" class="help-inline text-danger"></span>

                            </div>
                            <div class="col-md-6 form-group">
                                <label>Address of Country Residence:</label>
                                <textarea class="form-control" name="edit_address" id="edit_address" placeholder="Enter Address here"> </textarea>
                                <span id="edit_address_error" class="help-inline text-danger"></span>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Date of Issue:</label>
                                <input type="date" name="edit_doi" class="form-control" id="edit_doi" placeholder="Enter >Date of Issue" value="">
                                <span id="edit_doi_error" class="help-inline text-danger"></span>

                            </div>
                            <div class="col-md-6 form-group">
                                <label>Issuing Authority:</label>
                                <input type="text" name="edit_issuing_authority" class="form-control" id="edit_issuing_authority" placeholder="Enter Issuing Authority:" value="">
                                <span id="edit_issuing_authority_error" class="help-inline text-danger"></span>

                            </div>
                        </div>


                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Valid Unit:</label>
                                <input type="text" name="edit_valid_unit" class="form-control" id="edit_valid_unit" placeholder="Enter Valid Unit:" value="">
                                <span id="edit_valid_unit_error" class="help-inline text-danger"></span>

                            </div>
                        </div>


                        <div class="form-group clearfix">
                            <button type="submit" class="btn btn-theme col-lg-3 col-md-3 col-sm-12 col-xs-12" id="TtdRecordUpdate"><i class="loadsave"></i> <span id="TtdRecordUpdateText">Update Record</span></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Popup -->

<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>
<script src="<?php echo e(asset('backend/js/moment.min.js')); ?>"></script>
<script type="text/javascript">
	$token = '<?= csrf_token()?>';
	//datatable for index page 
	// Open Table
    var oTable = $('#ttd-open-table').DataTable({
		'dom':  "<'row'<'col-sm-3'i><'col-sm-4' p><'col-sm-1' ><'col-sm-4'f>>",
        "bProcessing": false,
        "bServerSide": true,
        "autoWidth": true,
        "aaSorting": [
            [0, "desc"]
        ],
        //index page url calls
        //"sAjaxSource":"<?= URL::route('ttd.index')?>",
        "sAjaxSource":"<?= URL::route('ttd.index',['status'=>'open'])?>",
        //columns that displaying
        "aoColumns":[

            {   mData: "rownum", 
                bSortable:false
            },
            {   mData: "td_no",
                sWidth: "20%",
                bSortable: true
            },
            {   mData: "full_name",
                sWidth: "30%",
                bSortable: true
            },
            {   mData: "dob",
                sWidth: "20%",
                bSortable: true,
                mRender: function(v, t, o) {
                    var date = moment(v).format('DD-MM-YYYY');
                    return date;
                },
            },
            {   mData: "place_of_birth",
                sWidth: "20%",
                bSortable: true
            },
            
            {   mData: "sex",
                sWidth: "20%",
                bSortable: true
            },
            {   mData: "nationality",
                sWidth: "20%",
                bSortable: true
            },
            {   mData: "address_country_residance",
                sWidth: "20%",
                bSortable: true
            },
            {   mData: "mother_name",
                sWidth: "20%",
                bSortable: true
            },
            {
                mData:"id",
                bSortable:false,

                mRender:function(v, t, o){
                    var act_html;
                    act_html ='<?php if(App\Helpers\SitePermissionCheck::isPermitted('ttd.statusUpdate')): ?> <?php if(App\Helpers\RolePermissionCheck::isPermitted('ttd.statusUpdate')): ?><a onclick="approved('+o['id']+')"><i class="fa fa-check-circle fa-lg green"></i></a><?php endif; ?> <?php endif; ?>';
                    return act_html;
                },
            },
	    ]
	});

    oTable.on('draw', function(event) {
        $(".loader").addClass('hidden');
    });

    // Approve Table
    var oTable1 = $('#ttd-approve-table').DataTable({
		'dom':  "<'row'<'col-sm-3'i><'col-sm-4' p><'col-sm-1' ><'col-sm-4'f>>",
        "bProcessing": false,
        "bServerSide": true,
        "autoWidth": true,
        "aaSorting": [
            [0, "desc"]
        ],
        //index page url calls
        //"sAjaxSource":"<?= URL::route('ttd.index')?>",
        "sAjaxSource":"<?= URL::route('ttd.index',['status'=>'approve'])?>",
        //columns that displaying
        "aoColumns":[

            {   mData: "rownum", 
                bSortable:false
            },
            {   mData: "td_no",
                sWidth: "20%",
                bSortable: true
            },
            {   mData: "full_name",
                sWidth: "30%",
                bSortable: true
            },
            {   mData: "dob",
                sWidth: "20%",
                bSortable: true,
                mRender: function(v, t, o) {
                    var date = moment(v).format('DD-MM-YYYY');
                    return date;
                },
            },
            {   mData: "place_of_birth",
                sWidth: "20%",
                bSortable: true
            },
            
            {   mData: "sex",
                sWidth: "20%",
                bSortable: true
            },
            {   mData: "nationality",
                sWidth: "20%",
                bSortable: true
            },
            {   mData: "address_country_residance",
                sWidth: "20%",
                bSortable: true
            },
            {   mData: "mother_name",
                sWidth: "20%",
                bSortable: true
            },
            {mData: "file",
                mRender: function(v, t, o) {
                    <?php

                        if(isset($_SERVER['HTTPS'])){
                            $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
                        }
                        else{
                            $protocol = 'http';
                        }
                        $domain = \Request::getHost();
                        $subdomain = explode('.', $domain);
                        $pdf_directory = $protocol.'://'.$domain.'/'.$subdomain[0].'/backend/pdf_file/';
                        $preview_directory = $protocol.'://'.$domain.'/'.$subdomain[0].'/backend/tcpdf/examples/preview/';

                    ?>
                    var pdf_path = "<?=$pdf_directory?>"+o.file;
                    var preview_path = "<?=$preview_directory?>"+o.file;
                    
                    var act_html;
        
                    act_html ="<a data-toggle='tooltip' target='_blank' data-placement='right' title='Please click to preview pdf file' href='"+preview_path+"'><i class='fa fa-file-pdf-o fa-lg'></i></a>";
                    act_html +="&nbsp;&nbsp;&nbsp;&nbsp;<a data-toggle='tooltip' target='_blank' data-placement='right' href='"+pdf_path+"' title='Please click to Print pdf file' ><i class='fa fa-file-pdf-o fa-lg'></i></a>";

                    return act_html;

                    // var link = "<a data-toggle='tooltip' target='_blank' data-placement='right' title='Please click to download pdf file' class='btn btn-success' href='"+pdf_path+"'>"+o.file+"</a>";

                    // return link;
                }
            },
	    ]
	});

    oTable1.on('draw', function(event) {
        $(".loader").addClass('hidden');
    });

    // Reject Table
    var oTable2 = $('#ttd-reject-table').DataTable({
		'dom':  "<'row'<'col-sm-3'i><'col-sm-4' p><'col-sm-1' ><'col-sm-4'f>>",
        "bProcessing": false,
        "bServerSide": true,
        "autoWidth": true,
        "aaSorting": [
            [0, "desc"]
        ],
        //index page url calls
        //"sAjaxSource":"<?= URL::route('ttd.index')?>",
        "sAjaxSource":"<?= URL::route('ttd.index',['status'=>'reject'])?>",
        //columns that displaying
        "aoColumns":[

            {   mData: "rownum", 
                bSortable:false
            },
            {   mData: "td_no",
                sWidth: "20%",
                bSortable: true
            },
            {   mData: "full_name",
                sWidth: "30%",
                bSortable: true
            },
            {   mData: "dob",
                sWidth: "20%",
                bSortable: true,
                mRender: function(v, t, o) {
                    var date = moment(v).format('DD-MM-YYYY');
                    return date;
                },
            },
            {   mData: "place_of_birth",
                sWidth: "20%",
                bSortable: true
            },
            
            {   mData: "sex",
                sWidth: "20%",
                bSortable: true
            },
            {   mData: "nationality",
                sWidth: "20%",
                bSortable: true
            },
            {   mData: "address_country_residance",
                sWidth: "20%",
                bSortable: true
            },
            {   mData: "mother_name",
                sWidth: "20%",
                bSortable: true
            },
	    ]
	});

    oTable2.on('draw', function(event) {
        $(".loader").addClass('hidden');
    });



    // Correction table
    var oTable3 = $('#ttd-correction-table').DataTable({
		'dom':  "<'row'<'col-sm-3'i><'col-sm-4' p><'col-sm-1' ><'col-sm-4'f>>",
        "bProcessing": false,
        "bServerSide": true,
        "autoWidth": true,
        "aaSorting": [
            [0, "desc"]
        ],
        //index page url calls
        //"sAjaxSource":"<?= URL::route('ttd.index')?>",
        "sAjaxSource":"<?= URL::route('ttd.index',['status'=>'correction'])?>",
        //columns that displaying
        "aoColumns":[

            {   mData: "rownum", 
                bSortable:false
            },
            {   mData: "td_no",
                sWidth: "20%",
                bSortable: true
            },
            {   mData: "full_name",
                sWidth: "30%",
                bSortable: true
            },
            {   mData: "dob",
                sWidth: "20%",
                bSortable: true,
                mRender: function(v, t, o) {
                    var date = moment(v).format('DD-MM-YYYY');
                    return date;
                },
            },
            {   mData: "place_of_birth",
                sWidth: "20%",
                bSortable: true
            },
            
            {   mData: "sex",
                sWidth: "20%",
                bSortable: true
            },
            {   mData: "nationality",
                sWidth: "20%",
                bSortable: true
            },
            {   mData: "address_country_residance",
                sWidth: "20%",
                bSortable: true
            },
            {   mData: "mother_name",
                sWidth: "20%",
                bSortable: true
            },
            {
                mData:"id",
                bSortable:false,

                mRender:function(v, t, o){
                    var act_html;
                    
                    act_html ='<?php if(App\Helpers\SitePermissionCheck::isPermitted('ttd.ajaxUpdate')): ?> <?php if(App\Helpers\RolePermissionCheck::isPermitted('ttd.ajaxUpdate')): ?>&nbsp;&nbsp;&nbsp;<a onclick="editForm('+o['id']+')"><i class="fa fa-edit fa-lg green"></i></a><?php endif; ?> <?php endif; ?>';
                    
                    return act_html;
                },
            },
	    ]
	});

    oTable3.on('draw', function(event) {
        $(".loader").addClass('hidden');
    });


    // get data Open record
    $('#open-pill').click(function(){
        var url="<?= URL::route('ttd.index',['status'=>'open'])?>";
        oTable.ajax.url(url);
        oTable.ajax.reload();
        $('.loader').removeClass('hidden');
        $('.commentDiv').hide();
    });
    // get data Approve record
    $('#approve-pill').click(function(){
        var url="<?= URL::route('ttd.index',['status'=>'approve'])?>";
        oTable1.ajax.url(url);
        oTable1.ajax.reload();
        $('.loader').removeClass('hidden');
        $('.commentDiv').hide();
    });


    // get data Reject record
    $('#reject-pill').click(function(){
        var url="<?= URL::route('ttd.index',['status'=>'reject'])?>";
        oTable2.ajax.url(url);
        oTable2.ajax.reload();
        $('.loader').removeClass('hidden');
        $('.commentDiv').hide();
    });


    // get data Correction record
    $('#correction-pill').click(function(){
        var url="<?= URL::route('ttd.index',['status'=>'correction'])?>";
        oTable3.ajax.url(url);
        oTable3.ajax.reload();
        $('.loader').removeClass('hidden');
        $('.commentDiv').hide();
    });


    // clear Admin model data
    $('.clear_model').on('hidden.bs.modal', function () {
        $(this).find("input,textarea,select").val('').end();
        $(this).find('span').text('').end();
        $(".save").removeAttr('disabled');
    });
    // clear Admin model data 

    function approved(id){
        var url_path="<?=URL::route('ttd.getDetailAjax') ?>"+ '/' + id;
        url_path=url_path.replace(':id',id);
        var token="<?php echo e(csrf_token()); ?>";
        var method_type="GET";
        $.ajax({
            url:url_path,
            type:method_type,
            success:function(data){  
                if(data.success==true)
                {
                    $('#ttdPopup').modal('show');

                    // $('.radioButtonDiv').html('<div class="form-group"><label class="radio-inline"><input type="radio" name="status" id="status" value="reject"> Reject</label><label class="radio-inline"><input type="radio" name="status" id="status" value="approve"> Approve</label><label class="radio-inline"><input type="radio" name="status" id="status" value="correction"> Correction</label></div>');
                    $('#ttd_id').val(data.data.id);
                    $('#td_number').html(data.data.td_no);
                    $('#full_name').html(data.data.full_name);
                    $('#mother_name').html(data.data.mother_name);
                    $('#date').html(data.data.date);
                    $('#sex').html(data.data.sex);
                    $('#dob').html(data.data.dob);
                    $('#pob').html(data.data.place_of_birth);
                    $('#nationality').html(data.data.nationality);
                    $('#face_marks').html(data.data.face_marks);
                    $('#address').html(data.data.address_country_residance);
                    $('#doi').html(data.data.date_of_issue);
                    $('#issuing_authority').html(data.data.issuing_authority);
                    $('#valid_unit').html(data.data.valid_unit);
                    
                    var photoImage = "<?php echo addslashes($profilePath); ?>";

                    //$('#photo').html(data.data.photo);

                    
                    if(data.data.photo) {
                        $('#photo').html('<img src="' + photoImage + data.data.photo + '" style="width:100%;" alt="Photo">');    
                    } else {
                        $('#photo').html('Not Uploaded');
                    }
                    //$('#photo').html('<img src="https://imt.seqrdoc.com/imt/ttd_images/photos/20250221121859_testSc12.jpg" style="width:100%;" alt="Photo">');
                    




                }
            },
        });

    }


    function editForm(id){
        var url_path="<?=URL::route('ttd.getDetailAjax') ?>"+ '/' + id;
        url_path=url_path.replace(':id',id);
        var token="<?php echo e(csrf_token()); ?>";
        var method_type="GET";
        $.ajax({
            url:url_path,
            type:method_type,
            success:function(data){  
                if(data.success==true)
                {
                    $('#ttdEditPopup').modal('show');
                    $("#TtdRecordUpdate").html('Update Record'); 
                    $('#edit_ttd_id').val(data.data.id);
                    $('#edit_td_number').val(data.data.td_no);
                    $('#edit_full_name').val(data.data.full_name);
                    $('#edit_mother_name').val(data.data.mother_name);
                    $('#edit_date').val(data.data.date);
                    $('#edit_sex').val(data.data.sex);
                    $('#edit_dob').val(data.data.dob);
                    $('#edit_pob').val(data.data.place_of_birth);
                    $('#edit_nationality').val(data.data.nationality);
                    $('#edit_face_marks').val(data.data.face_marks);
                    $('#edit_address').val(data.data.address_country_residance);
                    $('#edit_doi').val(data.data.date_of_issue);
                    $('#edit_issuing_authority').val(data.data.issuing_authority);
                    $('#edit_valid_unit').val(data.data.valid_unit);
                    $('#correctionMsg').html(data.ttdLog.comment);
                    
                    // 

                }
            },
        });

    }



    $('input[type="radio"]').click(function() {
        //$('#status').val($(this).val());
        //alert($('#status').val());
        if($(this).val() != 'approve') {
            $('.commentDiv').show();
        }else {
            $('.commentDiv').hide();
        }
    });



    
    $("#TtdUpdate").click(function(e){
		e.preventDefault();

        var status = $('input[name="status"]:checked').val();
        
        // var status = $('#status').val();
        var ttd_id = $('#ttd_id').val();
        var comment = $('#comment').val();
		var token = "<?php echo e(csrf_token()); ?>";
		var formData = new FormData();
		formData.append("id", ttd_id);
		formData.append("status", status);
        console.log("status "+ status);
		formData.append("comment", comment);
		$.ajax({
            url: "<?php echo e(URL::route('ttd.statusUpdate')); ?>",
            headers: {'X-CSRF-TOKEN': token},
            type: 'POST',
            data: formData,
            success: function (data) {
                if(data.status == true){
                	toastr.success(data.message);
                    oTable.ajax.reload();
                    oTable1.ajax.reload();
                    oTable2.ajax.reload();
                    oTable3.ajax.reload();

                    location.reload();
                    // $('#ttdData')[0].reset();
                    // $('#ttd_id').val();
                    // $('#comment').val();
                    // $('.radioButtonDiv').html('<div class="form-group"><label class="radio-inline"><input type="radio" name="status" id="status" value="reject"> Reject</label><label class="radio-inline"><input type="radio" name="status" id="status" value="approve"> Approve</label><label class="radio-inline"><input type="radio" name="status" id="status" value="correction"> Correction</label></div>');
                    $('#ttdPopup').modal('hide');
                    $('.commentDiv').hide();
                    $("#TtdUpdate").html('Update Record'); 
                }else{
                	
                    $.each(data.errors, function(k,v){
                        $('#'+k+'_error').css('display','block')
                        $('#'+k+'_error').text(v);
                    });

                }
            },
            beforeSend: function() {
                $("#TtdUpdate").html('<span> <img src="https://upload.wikimedia.org/wikipedia/commons/a/ad/YouTube_loading_symbol_3_%28transparent%29.gif" style="width: 5%;">  </span> Please Wait');
            },
            cache: false,
            contentType: false,
            processData: false,
            dataType:'JSON'
        });
    });



    $("#TtdRecordUpdate").click(function(e){
		e.preventDefault();

        var status = $('input[name="status"]:checked').val();
        
        // var status = $('#status').val();
        var ttd_id = $('#ttd_id').val();
        var comment = $('#comment').val();
		var token = "<?php echo e(csrf_token()); ?>";
        var formData = new FormData($('#ttdEditData')[0]);
		$.ajax({
            url: "<?php echo e(URL::route('ttd.ajaxUpdate')); ?>",
            headers: {'X-CSRF-TOKEN': token},
            type: 'POST',
            data: formData,
            success: function (data) {
                if(data.status == true){
                    toastr.success(data.message);
                    oTable.ajax.reload();
                    oTable1.ajax.reload();
                    oTable2.ajax.reload();
                    oTable3.ajax.reload();
                    $('#ttdEditData')[0].reset();
                    $('#ttdEditPopup').modal('hide');
                    $("#TtdRecordUpdate").html('Update Record'); 
                }else{
                	
                    $.each(data.errors, function(k,v){
                        $('#'+k+'_error').css('display','block')
                        $('#'+k+'_error').text(v);
                    });
                }
            },
            beforeSend: function() {
                $("#TtdRecordUpdate").html('<span> <img src="https://upload.wikimedia.org/wikipedia/commons/a/ad/YouTube_loading_symbol_3_%28transparent%29.gif" style="width: 5%;">  </span> Please Wait');
            },
            cache: false,
            contentType: false,
            processData: false,
            dataType:'JSON'
        });
    });




</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layout.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/admin/ttd/index.blade.php ENDPATH**/ ?>