@extends('admin.layout.layout')
 
@section('content')
 
    <style>
        .centered-card {
            display: flex;
            justify-content: center;
            /* Horizontally center the card */
            align-items: center;
            /* Vertically center the card */
            height: auto;
            /* Full viewport height to center vertically */
        }

        .dataTables_wrapper {
            border-top: 1px solid #eee;
        }
    </style>
    <div class="container">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header"><i class="fa fa-user-circle"></i>Students
                        {{-- <ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;">{{ Breadcrumbs::render('processexcel') }}</ol>
					<i class="fa fa-info-circle iconModalCss" title="User Manual" id="processExcelClick"></i> --}}
                    </h1>

                </div>
            </div>


            <form method="post" id="processExcelForm" class="form-horizontal" style="border-bottom: 1px solid #eee;" enctype="multipart/form-data"
                action="<?= route('convocation.admin.upload_student') ?>">
                <input type="hidden" name="func" id="func" value="processExcel">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <div class="form-group">
                    <label class="control-label col-sm-2" for="excel">Upload Excel:</label>
                    <div class="col-sm-10">
                        <input type="file" class="form-control" id="excel" name="excel_data">
                        <span id="excel_data_error"
                            class="help-inline text-danger"><?= $errors->first('excel_data') ?></span>
                    </div>

                </div>

                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                        <?php
                        $domain = \Request::getHost();
                        $subdomain = explode('.', $domain);
                        ?>
                        <div align="center"><b>Please Click <a href="{{ asset('backend/convocation/sampleExcel.xls') }}"
                                    download>HERE</a> To Download Sample Excel</b></div>
                        <button type="submit" class="btn btn-primary">Submit</button>

                    </div>
                </div>
                <div class="row centered-card" style="text-align: center;">
                    <div id="response" class="card col-md-4" style="display: none;">

                        <div class="card_body response_card" style="padding-top: 1%;padding-bottom: 3%;">

                        </div>

                        <div class="card_body"
                            style="padding-top: 3%;padding-bottom: 3%; text-align: right;     border-top: 1px solid #d3d3d3;">
                            <button type="button" id="close_reponse_card" style="margin-right: 3%;"
                                class="btn btn-secondary"> Close</button>
                        </div>
                    </div>

                </div>

            </form>


            <div id="download_link">

            </div>
            <div id="divLoading"
                style="margin: 0px; padding: 0px; position: fixed; right: 0px; top: 0px; width: 100%; height: 100%; background-color: rgb(102, 102, 102); z-index: 30001; opacity: 0.8;display: none;">
                <p style="color:white;position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
                    Excel is processing... Please wait ..... <img src="/backend/images/loading.gif"> </p>
            </div>
            <div class="row" style="margin-top:1%;margin-bottom:1%">
                <div class="col-md-3">
                    <label for="prn_filter">PRN:</label>
                    <input type="text" id="prn_filter" class="form-control" placeholder="Filter by PRN">
                </div>
                <div class="col-md-3">
                    <label for="name_filter">Full Name:</label>
                    <input type="text" id="name_filter" class="form-control" placeholder="Filter by Full Name">
                </div>
                <div class="col-md-3">
                    <label for="status_filter">Status:</label>
                    <select id="status_filter" name="status_filter" class="form-control">
                        <option value="">All</option>
                        <option value="have not yet signed up">Have not yet signed up</option>
                        <option value="have completed 1st time sign up">Have completed 1st time sign up</option>
                        <option value="student acknowledge all data as correct but preview pdf approval is pending">Student acknowledges all data as correct but preview PDF approval is pending</option>
                        <option value="student acknowledge all data as correct, preview pdf is approved but payment is pending">Student acknowledges all data as correct, preview PDF is approved but payment is pending</option>
                        <option value="student acknowledge all data as correct, preview pdf is approved and payment completed">Student acknowledges all data as correct, preview PDF is approved and payment completed</option>
                        <option value="student marked few data as incorrect and admin’s action pending">Student marked a few data as incorrect and admin’s action pending</option>
                        <option value="admin performed correction but student’s re-acknowledgement pending">Admin performed correction but student’s re-acknowledgement pending</option>
                        <option value="student re-acknowledged new data as correct but preview pdf approval is pending">Student re-acknowledged new data as correct but preview PDF approval is pending</option>
                        <option value="student re-acknowledged new data as correct, preview pdf is approved but payment is pending">Student re-acknowledged new data as correct, preview PDF is approved but payment is pending</option>
                        <option value="student re-acknowledged new data as correct, preview pdf is approved and payment completed">Student re-acknowledged new data as correct, preview PDF is approved and payment completed</option>
                               
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label for="course_filter">Course Name:</label>
                    <input type="text" id="course_filter" class="form-control" placeholder="Filter by Course Name">
                </div>
                <div class="col-md-12" style="text-align: right;margin-top:1%">
                <a id="export-btn"  class="btn btn-primary"><i class="fa fa-file-excel-o" style="margin-right: 3%"></i>Export Data</a>     
                </div>
            </div>
            
            <div class="">
                <table id="example" class="table table-hover display" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>PRN</th>
                            <th>FULL NAME</th>
                            {{-- <th>FULL NAME(Hindi)</th> --}}
                            <th>EMAIL ID</th>
                            <th>DATE OF BIRTH</th>
                            <th>COURSE NAME</th>
                            <th>Collection Mode</th> 
                            {{-- <th>COURSE NAME(Hindi)</th> --}}
                            <th>CGPA</th>
                            <th>Status</th>
                            <th>Created date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tfoot>
                    </tfoot>
                </table>
            </div>

            <!-- Modal Structure -->
            <div class="modal fade" id="errorModal" tabindex="-1" role="dialog" aria-labelledby="errorModalLabel"
                aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="errorModalLabel">Error Details</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div id="errorContent"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@stop
 
@section('script')  

<!-- Buttons HTML5 (for exporting options) -->
 
    <script type="text/javascript">
        var table = $('#example').DataTable({
            'dom': "<'row'<'col-sm-3'i><'col-sm-4'p><'col-sm-5'B>>", // Added 'B' for buttons
            buttons: [
            {
                extend: 'excelHtml5',
                text: 'Export Excel',
                title: 'Data Export',
                className: 'btn btn-success'
            }
        ],
            'bProcessing': true,
            'bServerSide': true,
            'autoWidth': true,
            'aaSorting': [
                [2, 'desc']
            ],
            'order': [
                [9, 'desc']
            ],
            // 'sAjaxSource': "{{ route('convo_students.index') }}",
            'ajax': {
                'url': "{{ route('convo_students.index') }}",
                'type': 'GET',
                'data': function(d) {
                    // Add custom filters to the data sent to the server
                    d.prn_filter = $('#prn_filter').val();
                    d.name_filter = $('#name_filter').val();
                    d.status_filter = $('#status_filter').val();
                    d.course_filter = $('#course_filter').val();
                }
            },
            'aoColumns': [{
                    mData: 'rownum',
                    bSortable: false
                },
                {
                    mData: 'prn',
                    bSortable: true
                },
                {
                    mData: 'full_name',
                    bSortable: true
                },
                // {
                //     mData: 'full_name_hindi',
                //     bSortable: true
                // },
                {
                    mData: 'wpu_email_id',
                    bSortable: true
                },
                {
                    mData: 'date_of_birth',
                    bSortable: true
                },
                {
                    mData: 'course_name',
                    bSortable: true
                },
                {
                    mData: 'collection_mode',
                    bSortable: true
                },
                {
                    mData: 'cgpa',
                    bSortable: true
                },
                {
                    mData: 'status',
                    bSortable: true
                },
                {
                    mData: 'created_at', // Use raw date for sorting
                    bSortable: true,
                    render: function(data, type, row) {
                        if (data) {
                            return moment(data).format('YYYY-MM-DD hh:mm A'); // Format as needed 

                        }
                        return '';
                    }
                },
                {
                    mData: null,
                    bSortable: false,
                    mRender: function(data, type, row) {
                        // Construct your route or URL dynamically
                        var url = '/admin/convocation/student_edit/' + row.id; // Example URL

                        return '<a href="' + url + '" class="btn btn-primary btn-edit" data-id="' + row.id +
                            '">' +
                            '<span class="fa fa-edit"></span>' +
                            '</a>';
                    }
                }
            ],
            'buttons': [
                {
                    extend: 'excelHtml5',
                    text: 'Export to Excel',
                    titleAttr: 'Export to Excel',
                    className: 'btn btn-success' // Optional: add custom classes
                }
            ]

        });
        function applyFilters() {
            table.ajax.reload();
        }

        $('#prn_filter').on('keyup change', applyFilters);
        $('#name_filter').on('keyup change', applyFilters);
        $('#status_filter').on('keyup change', applyFilters);
        $('#course_filter').on('keyup change', applyFilters);
        
        $('#processExcelForm').validate({
            rules: {
                excel_data: {
                    'required': true,
                    extension: 'xls|xlsx'
                }
            },
            messages: {
                excel_data: {
                    required: 'Please choose a file',
                    extension: 'Please select only an Excel file',
                }
            },
            submitHandler: function(form) {
                $('#response').hide();
                $(form).ajaxSubmit({
                    beforeSubmit: function(formData, jqForm, options) {
                        $('#divLoading').show();
                    },
                    clearForm: false,
                    dataType: 'json',
                    success: function(resObj) {
                        $('#divLoading').hide();
                        if (resObj.status === 'success') {
                            let failCount = resObj.failed;
                            let responseHtml =
                                '<p class="mt-2" style="margin-top: 2%; font-size: 19px;">Total number of records: ' +
                                resObj.total_data + '</p>' +
                                '<p style="margin-top: 2%; color: green; font-size: 19px;">Total successfully created records: ' +
                                resObj.processed_data + '</p>' +
                                '<p style="margin-top: 2%; color: red; font-size: 19px;">Total failed records: ' +
                                failCount + '</p>';

                            // Display the response card
                            $('.response_card').html(responseHtml).show();
                            $('#response').show();
                            toastr.success(resObj.message);
                            $(form).resetForm();
                            table.ajax.reload();
                        } else if (resObj.status === 'failed' || resObj.status === 'error') {
                            // toastr.error(resObj.message);
                            let failCount = resObj.failed;
                            let responseHtml =
                                '<p class="mt-2" style="margin-top: 2%; font-size: 19px;">Total number of records: ' +
                                resObj.total_data + '</p>' +
                                '<p style="margin-top: 2%; color: green; font-size: 19px;">Total successfully created records: ' +
                                resObj.processed_data + '</p>' +
                                '';

                            responseHtml +=
                                '<p style="margin-top: 2%; color: red; font-size: 19px;">Total failed records: ' +
                                failCount + '</p>';
                            if (failCount > 0) {
                                responseHtml +=
                                    '<button type="button" id="showErrorsButton" class="btn btn-danger"> View Error Details</button>' // Show the button with id 'retry_button'
                            } else {
                                responseHtml += ''
                            }



                            // Display the response card
                            let errorContentHtml = '<ul>';
                            resObj.errors.forEach(function(error) {
                                errorContentHtml += '<li><strong>Row ' + error.row +
                                    ':</strong><br>';
                                errorContentHtml += '<ul>';
                                error.errors.forEach(function(err) {
                                    errorContentHtml += '<li>' + err + '</li>';
                                });
                                errorContentHtml += '</ul>';
                                errorContentHtml += '</li>';
                            });
                            errorContentHtml += '</ul>';
                            // $('#errorModal').modal('show');
                            $('#errorContent').html(errorContentHtml);
                            $('.response_card').html(responseHtml).show();
                            $('#response').show();
                            $(form).resetForm();
                            table.ajax.reload();

                        } else {
                            toastr.error(resObj.message);
                        }

                    },
                    error: function(xhr, status, error) {
                        $('#divLoading').hide();
                        toastr.error('An unexpected error occurred. Please try again.');
                    }
                });
            }
        });
        $(document).on('click', '#showErrorsButton', function() {
            $('#errorModal').modal('show');
        });
        $(document).on('click', '#close_reponse_card', function() {
            $('#response').hide();
        });


        $('#download_link').click(function() {
            setTimeout(function() {
                window.location.reload();
            }, 800);
        });

        $('#export-btn').on('click', function(e) {
        e.preventDefault(); // Prevent the default link behavior

        // Collect filter values
        var prn = $('#prn_filter').val();
        var name = $('#name_filter').val();
        var status = $('#status_filter').val();
        var course = $('#course_filter').val();

        // Construct the URL with query parameters
        var baseUrl = '{{ route('convo_student.export_convo_students') }}';
        var params = $.param({
            prn: prn,
            name: name,
            status: status,
            course: course
        });

        // Redirect to the export URL
        window.location.href = baseUrl + '?' + params;
    });
    </script>

@stop
