<?php $__env->startSection('content'); ?>
    <div class="container">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header"><i class="fa fa-fw fa-users"></i> Functional Users
                        <ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;"> </ol>
                        <i class="fa fa-info-circle iconModalCss" title="User Manual" id="userMasterClick"></i>
                    </h1>

                </div>
            </div>
            
            
            <div class="col-xs-12">
              
                <table id="example" class="table table-hover" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User Name</th>
                            <th>Mobile</th>
                            <th>Email</th>
                            <th>Registraion Date</th>
                            <th>Action</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tfoot>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    <!--   // show User information model -->
    <div id="myModal" class="modal fade clear_user_data" role="dialog" tabindex="-1">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">User Information</h4>
                </div>
                <div class="modal-body cleardiv" id="ajaxResponse">
                    <div class="list-group">
                        <div class="list-group-item"><b>Student Name:</b> <span id="data-sname"> </span></div>
                        <div class="list-group-item"><b>Email:</b> <span id="data-email"> </span></div>
                        <div class="list-group-item"><b>Mobile:</b> <span id="data-mobile"> </span></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-theme" data-dismiss="modal">Close</button>
                </div>
            </div>

        </div>
    </div>
    <!--   // End User information model -->

    <!--  // User Create Model -->
    <?php echo $__env->make('admin.functionalusers.model', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <!--  // End User Create Model -->

<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>

    <script type="text/javascript">
      
        // send Ajax Request to featch User data and show model 
        function user_edit(user_id) {

            $(".clear_model").find('span').text('').end();
            $('.update_user').show();
            var edit_path = "<?= URL::route('functionalusers.edit', [':id']) ?>";
            edit_path = edit_path.replace(':id', user_id);
            var method_type = "GET";
            var token = "<?php echo e(csrf_token()); ?>";
            $.ajax({

                url: edit_path,
                type: method_type,
                data: {
                    '_token': token
                },

                success: function(data) {
                    $('#addUsr').modal('show');
                    $("#user_id").val(data.id);
                    $("#sname").val(data.Student_Name);
                    $("#email_id").val(data.Email_ID);
                    $("#mobile_no").val(data.Mobile_Number);
                },
            });
        }


        // send Ajax Request to update User data 
        $(".update_user").click(function(event) {

            $(".update_save").attr('disabled');
            var user_id = $("#user_id").val();
            event.preventDefault();
            var update_path = "<?= URL::route('functionalusers.update', [':id']) ?>";
            update_path = update_path.replace(':id', user_id);
            var token = "<?php echo e(csrf_token()); ?>";
            var type = "post";

            $("#UserData").ajaxSubmit({
                url: update_path,
                type: type,
                data: {
                    '_token': token
                },
                beforeSubmit: function() {
                    $(".clear_model").find('span').text('').end();
                    $(".loadupdate").addClass('fa fa-spinner fa-spin');
                    $(".update_user").attr('disabled', 'disabled');
                },
                success: function(data) {
                    if (data.success == true) {
                        $('#addUsr').modal('hide');
                        $('#fontMatser_data').trigger("reset");
                        toastr.success('User Updated successfully');
                        oTable.ajax.reload();
                        $(".update_user").removeAttr('disabled');
                        $(".loadupdate").removeClass('fa fa-spinner fa-spin');
                    }
                    if (data.success == 2) {
                        // $('#addUsr').modal('hide');
                        // $('#fontMatser_data').trigger("reset");
                        toastr.error('Duplicate entry found');
                        // oTable.ajax.reload();
                        $(".update_user").removeAttr('disabled');
                        $(".loadupdate").removeClass('fa fa-spinner fa-spin');
                    }
                     if (data.success == false) {
                        // $('#addUsr').modal('hide');
                        // $('#fontMatser_data').trigger("reset");
                        toastr.error('Nothing is updated');
                        // oTable.ajax.reload();
                        $(".update_user").removeAttr('disabled');
                        $(".loadupdate").removeClass('fa fa-spinner fa-spin');
                    }
                },
                error: function(resobj) {
                    $.each(resobj.responseJSON.errors, function(k, v) {
                        $('#' + k + '_error').text(v);
                    });
                    $(".update_user").removeAttr('disabled');
                    $(".loadupdate").removeClass('fa fa-spinner fa-spin');
                },
            });
        });

        // datatable
        var oTable = $('#example').DataTable({
            'dom': "<'row'<'col-sm-5' p><'col-sm-3' ><'col-sm-4'f>>",
            "bProcessing": false,
            "bServerSide": true,
            "autoWidth": true,

            // "aaSorting": [
            // [7, "desc"]
            // ],
            "sAjaxSource": "<?= URL::route('functionalusers.index') ?>",
            "aoColumns": [{
                    mData: "rownum",
                    bSortable: false
                },
                {
                    mData: "Student_Name",
                    bSortable: true,
                    "sClass": "text-center"
                },
                {
                    mData: "Mobile_Number",
                    bSortable: true,
                    "sClass": "text-center"
                },
                {
                    mData: "Email_ID",
                    bSortable: true,
                    "sClass": "text-center"
                },
                {
                    mData: "created_at",
                    bSortable: true,
                    "sClass": "text-center"
                },
                {
                    mData: "id",
                    bSortable: false,

                    mRender: function(v, t, o) {
                        var act_html;


                        act_html =
                            '<?php if(App\Helpers\SitePermissionCheck::isPermitted('usermaster.edit')): ?> <?php if(App\Helpers\RolePermissionCheck::isPermitted('usermaster.edit')): ?> &nbsp;&nbsp;<a onclick="user_edit(' +
                            o['id'] +
                            ')"><i class="fa fa-edit fa-lg green"></i></a>&nbsp <?php endif; ?> <?php endif; ?>';


                        return act_html;
                    },
                },
            ],
        });

        oTable.on('draw', function() {

            $(".loader").addClass('hidden');
        });

        
        // clear model data
        $('.clear_model').on('hidden.bs.modal', function() {
            $(this).find("input,textarea,select").val('').end();
            $(this).find('span').text('').end();
        });

        // allow only number
        $(".allow_number").keypress(function(h) {
            var keyCode = h.which ? h.which : h.keyCode
            if (!(keyCode >= 48 && keyCode <= 57)) {
                return !1;
            }
        });

        // allow only character
        $(".allow_character").keypress(function(h) {

            var keyCode = h.which ? h.which : h.keyCode;
            if (!(keyCode >= 97 && keyCode <= 122) && !(keyCode >= 65 && keyCode <= 90) && !(keyCode >= 32 &&
                    keyCode <= 32)) {
                return !1;
            }

        });

    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/admin/functionalusers/index.blade.php ENDPATH**/ ?>