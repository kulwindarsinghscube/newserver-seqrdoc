@extends('admin.layout.layout')
@section('content')
    <div class="container">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header"><i class="fa fa-fw fa-users"></i> ID Cards Report
                        <ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;"> </ol>
                        <i class="fa fa-info-circle iconModalCss" title="User Manual" id="userMasterClick"></i>
                    </h1>

                </div>
            </div>
            <div class="col-xs-12" style="margin-bottom: 2%">
              <button class="btn btn-theme" id="importUser" data-toggle="modal" data-target="#uploadFile">
                <i class="fa fa-plus"></i> Import Users
            </button>
        
            <!-- Export Users Button (Excel Download) -->
            <form action="{{ route('functionalusers.exportLoginHistory') }}" method="GET" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-theme">
                    <i class="fa fa-download	"></i> Export Users Login History
                </button>
            </form>
            </div>
            <div class="row" style="margin-bottom: 1%;">
                <!-- Start Date -->
                <?php
                // Get the current date
                $today = new DateTime();
                
                // Get the first day of the current month
                $startDate = $today->modify('first day of this month')->format('Y-m-d');
                
                // Get the last day of the current month
                $endDate = $today->modify('last day of this month')->format('Y-m-d');
                ?>
                
                <div class="col-md-3">
                    <label for="start_date" style="margin-bottom: 5px; display: block;">Start Date:</label>
                    <input type="date" id="start_date" class="form-control" value="<?php echo $startDate; ?>" required>
                </div>
                
                <!-- End Date -->
                <div class="col-md-3">
                    <label for="end_date" style="margin-bottom: 5px; display: block;">End Date:</label>
                    <input type="date" id="end_date" class="form-control" value="<?php echo $endDate; ?>" required>
                </div>
               
                <!-- Card Category -->
                 <div class="col-md-3">
                    <label for="card_category" style="margin-bottom: 5px; display: block;">Card Category:</label>
                    <select id="card_category" class="form-control">
                        <option value="" selected>All</option> 
                        @foreach($categories as $category)
                            <option value="{{ $category }}">{{ $category }}</option>
                        @endforeach
                    </select>
                </div> 
                 
            </div>
            
            <div class="row" style="margin-bottom:2%">
                <div class="col-md-3">
                    <label style="visibility: hidden; display: block;">Export</label>
                    <button type="button" class="btn btn-theme" id="cardCreatedExportButton" style="display: inline-block; width: 100%;">
                        <i class="fa fa-download	"></i> Cards Created Report Export
                    </button>
                </div>
                <div class="col-md-3">
                    <label style="visibility: hidden; display: block;">Export</label>
                    <button type="button" class="btn btn-theme" id="expiringCardsExportButton" style="display: inline-block; width: 100%;">
                       <i class="fa fa-download	"></i> Cards Expiring Report Export
                    </button>
                </div>
                <div class="col-md-3">
                    <label style="visibility: hidden; display: block;">Export</label>
                    <button type="button" class="btn btn-theme" id="cardcycletimeExportButton" style="display: inline-block; width: 100%;">
                       <i class="fa fa-download	"></i> Cards Cycle-time Report Export
                    </button>
                </div>
                <div class="col-md-3">
                    <label style="visibility: hidden; display: block;">Export</label>
                    <button type="button" class="btn btn-theme" id="certificateCreateReport" style="display: inline-block; width: 100%;">
                       <i class="fa fa-download	"></i> Certificates Created Report Export
                    </button>
                </div>
                
            </div>
            
            <!-- Hidden Form for Submitting Data -->
            <form id="exportForm" action="" method="POST" style="display: none;">
                @csrf <!-- CSRF token for Laravel -->
                <input type="hidden" name="start_date" id="hidden_start_date" value={{ $endDate }}>
                <input type="hidden" name="end_date" id="hidden_end_date" value={{ $startDate }}>
                <input type="hidden" name="card_category" id="hidden_card_category">
            </form>
        </div>
    </div>
    

    <!--  // User Create Model -->
    @include('admin.functionalusers.model')
    <!--  // End User Create Model -->

@stop
@section('script')

    <script type="text/javascript">
      $('#card_category').select2({
            placeholder: "Select a category",
            allowClear: true
        });

        @if (App\Helpers\RolePermissionCheck::isPermitted('student.upload'))
            // Send ajax to Import student file
            $(".save").click(function(event) {
                event.preventDefault();
                var url = "{{ URL::route('functionalusers.upload') }}";
                var token = "{{ csrf_token() }}";
                var method_type = "post";
                $("#student_doc").ajaxSubmit({

                    url: url,
                    data: {
                        '_token': token
                    },
                    type: method_type,
                    beforeSubmit: function() {
                        $("#student_doc").find('span').text('').end();
                        $(".loadsave").addClass('fa fa-spinner fa-spin');
                        $(".save").attr('disabled', 'disabled');
                    },
                    success: function(data) {
                        if (data.success == false) {
                            if (data.NoLine) {
                                $("#field_file_error").text(data.NoLine);
                            }
                            if (data.InvalidData) {
                                $("#field_file_error").text(data.InvalidData);
                            }
                            if (data.ExcelInvalid) {
                                $("#field_file_error").text(data.ExcelInvalid);
                            }
                            $(".loadsave").removeClass('fa fa-spinner fa-spin');
                            $(".save").removeAttr('disabled');
                        }
                        if (data.success == true) {

                            $("#uploadFile").modal('hide');
                            toastr.success('Succeessfully Imported file');
                            oTable.ajax.reload();
                            $(".loadsave").removeClass('fa fa-spinner fa-spin');
                            $(".save").removeAttr('disabled');
                        }
                    },
                    error: function(resobj) {
                        $(".save").removeAttr('disabled');
                        $.each(resobj.responseJSON.errors, function(k, v) {
                            $("#" + k + '_error').text(v);
                        });
                        $(".loadsave").removeClass('fa fa-spinner fa-spin');
                    }
                });

            });
        @endif
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
        $(document).ready(function () {
    // Function to fill hidden fields and submit form
    function submitExportForm(action) {
        const startDate = $('#start_date').val();
        const endDate = $('#end_date').val();
        const cardCategory = $('#card_category').val();

        // Fill hidden fields with current form data
        $('#hidden_start_date').val(startDate);
        $('#hidden_end_date').val(endDate);
        $('#hidden_card_category').val(cardCategory);

        // Set form action dynamically based on button clicked
        $('#exportForm').attr('action', action);
        
        // Submit the form
        $('#exportForm').submit();
    }

    // Card Created Export Button
    $('#cardCreatedExportButton').on('click', function () {
        if($('#card_category').val()==''){
            alert('please select card category');
            return false;
        }else{
        submitExportForm('/export-card-created'); // Set the action to export card created data
        }
    });

    // Cards Expiring Export Button
    $('#expiringCardsExportButton').on('click', function () {
        if($('#card_category').val()==''){
            alert('please select card category');
            return false;
        }else{
        submitExportForm('/export-expiring-cards'); // Set the action to export expiring cards data
        }
    });

    // Cards Expiring Export Button
    $('#cardcycletimeExportButton').on('click', function () {
        if($('#card_category').val()==''){
            alert('please select card category');
            return false;
        }else{
        submitExportForm('/card-cycle-time-report'); // Set the action to export expiring cards data
        }
    });

    $('#certificateCreateReport').on('click', function () {
        if($('#card_category').val()==''){
            alert('please select card category');
            return false;
        }else{
        submitExportForm('/functional-users-created-certificate'); // Set the action to export expiring cards data
        }
    });

    

});


$(document).ready(function () {
    // Function to fill hidden fields and submit form
    function submitExportForm(action) {
        const startDate = $('#start_date').val();
        const endDate = $('#end_date').val();
        const cardCategory = $('#card_category').val();
        const card_hub = $('#card_hub').val();

        // Fill hidden fields with current form data
        $('#hidden_start_date').val(startDate);
        $('#hidden_end_date').val(endDate);
        $('#hidden_card_category').val(cardCategory);

        // Set form action dynamically based on button clicked
        $('#exportForm').attr('action', action);
        
        // Submit the form
        $('#exportForm').submit();
    }

    // Card Created Export Button
    $('#cardCreatedExportButton').on('click', function () {
        if($('#card_category').val()==''){
            // alert('please select card category');
            return false;
        }else{
        submitExportForm('/functional-users-created-cards'); // Set the action to export card created data
        }
    });

    // Cards Expiring Export Button
    $('#expiringCardsExportButton').on('click', function () {
        if($('#card_category').val()==''){
            // alert('please select card category');
            return false;
        }else{
        submitExportForm('/functional-users-expiring-cards'); // Set the action to export expiring cards data
        }
    });
});

    </script>
@stop
