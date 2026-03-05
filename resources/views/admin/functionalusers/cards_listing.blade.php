@extends('admin.layout.layout')
@section('content')
    <div class="container">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header"><i class="fa fa-fw fa fa-file-o"></i>ID Cards
                        <ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;"> </ol>
                        <i class="fa fa-info-circle iconModalCss" title="User Manual" id="userMasterClick"></i>
                    </h1>

                </div>
            </div>
            
            <div class="row" style="margin-bottom: 1%;">
               
                
                <div class="col-md-3">
                    <label for="start_date" style="margin-bottom: 5px; display: block;">Generation Start Date:</label>
                    <input type="date" id="start_date" class="form-control" value="" required>
                </div>
                
                <!-- End Date -->
                <div class="col-md-3">
                    <label for="end_date" style="margin-bottom: 5px; display: block;">Generation End Date:</label>
                    <input type="date" id="end_date" class="form-control" value="" required>
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
                <div class="col-md-3">
                    <label for="card_hub" style="margin-bottom: 5px; display: block;">Hub Name:</label>
                    <select id="card_hub" class="form-control">
                        <option value="" selected>All</option> 
                        @foreach($Hub_Name as $hub)
                            <option value="{{ $hub }}">{{ $hub }}</option>
                        @endforeach
                    </select>
                </div>   
            </div>
            
            
            <!-- Hidden Form for Submitting Data -->
            <form id="exportForm" action="" method="POST" style="display: none;">
                @csrf <!-- CSRF token for Laravel -->
                <input type="hidden" name="start_date" id="hidden_start_date" value={{ $endDate }}>
                <input type="hidden" name="end_date" id="hidden_end_date" value={{ $startDate }}>
                <input type="hidden" name="card_category" id="hidden_card_category">
                <input type="hidden" name="card_hub" id="hidden_card_hub">
            </form>
            
            <div class="col-xs-12">
              
                <table id="example" class="table table-hover" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>Sr.No.</th>
                            <th>Candidate Name</th>
                            <th>Enrollment No.</th>
                            <th>Course</th>
                            <th>To work under</th>
                            <th>Valid Upto</th>
                            <th>Batch No</th>
                            <th>Category</th>
                            <th>Hub_Name</th>
                            <th>Genration Date </th>
                            <!-- <th>Actions</th> -->
                        </tr>
                    </thead>
                    <tfoot>
                    </tfoot>
                </table>
            </div>
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
            

        // datatable
        var oTable = $('#example').DataTable({
            'dom': "<'row'<'col-sm-5' p><'col-sm-3' ><'col-sm-4'f>>",
            "bProcessing": true,
            "bServerSide": true,
            "autoWidth": true,

            // "aaSorting": [
            // [7, "desc"]
            // ],
            "sAjaxSource": "<?= URL::route('functionalusers.cards_listing') ?>",
            "fnServerParams": function (aoData) {
                aoData.push(
                    { "name": "card_category", "value": $('#card_category').val() },
                    { "name": "start_date", "value": $('#start_date').val() },
                    { "name": "end_date", "value": $('#end_date').val() },
                    { "name": "card_hub", "value": $('#card_hub').val() }
                );
            },
            "aoColumns": [{
                    mData: "rownum",
                    bSortable: false
                },
                {
                    mData: "Candidate_name",
                    bSortable: true,
                    "sClass": "text-center"
                },
                {
                    mData: "enrollment_no",
                    bSortable: true,
                    "sClass": "text-center"
                },
                {
                    mData: "course",
                    bSortable: true,
                    "sClass": "text-center"
                },{
                    mData: "to_work_under",
                    bSortable: true,
                    "sClass": "text-center"
                },{
                    mData: "valid_upto",
                    bSortable: true,
                    "sClass": "text-center"
                },{
                    mData: "batch_no",
                    bSortable: true,
                    "sClass": "text-center"
                },{
                    mData: "Card_Category",
                    bSortable: true,
                    "sClass": "text-center"
                },{
                    mData: "Hub_Name",
                    bSortable: true,
                    "sClass": "text-center"
                },{
                    mData: "created_at",
                    bSortable: true,
                    "sClass": "text-center"
                },
                // {
                //     mData: "id",
                //     bSortable: false,

                //     mRender: function(v, t, o) {
                //         var act_html;


                //         act_html =
                //             '@if (App\Helpers\SitePermissionCheck::isPermitted('usermaster.edit')) @if (App\Helpers\RolePermissionCheck::isPermitted('usermaster.edit')) &nbsp;&nbsp;<a onclick="user_edit(' +
                //             o['id'] +
                //             ')"><i class="fa fa-edit fa-lg green"></i></a>&nbsp @endif @endif';


                //         return act_html;
                //     },
                // },
            ],
        });

        oTable.on('draw', function() {

            $(".loader").addClass('hidden');
        });

        $('#card_category,#card_hub').on('change', function () {
            oTable.draw();
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
        $('#hidden_card_hub').val(card_hub);

        // Set form action dynamically based on button clicked
        $('#exportForm').attr('action', action);
        
        // Submit the form
        $('#exportForm').submit();
    }

    

});


    </script>
@stop
