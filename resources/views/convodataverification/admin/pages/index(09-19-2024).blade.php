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

        #collapsibleDiv .card {
            padding: 2%;

        }



        .custom_card {
            height: 130px;
            /* Adjust this value as needed */
            display: flex;
            flex-direction: column;
        }

        .custom_card_body {
            flex: 1;
            /* Makes sure the body takes up remaining space */
            display: flex;
            flex-direction: column;
            justify-content: center;
            /* Centers content vertically */
            align-items: center;
            /* Centers content horizontally */
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


            <form method="post" id="processExcelForm" class="form-horizontal" style="border-bottom: 1px solid #eee;"
                enctype="multipart/form-data" action="<?= route('convocation.admin.upload_student') ?>">
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
                        <div align="center"><b>Please Click <a href="{{ asset($subdomain[0].'/backend/convocation/sampleExcel.xls') }}"
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
            <div class="row">
                <div class="col-md-12" style="">
                    <a class="btn btn-primary" onclick="fetchStatusCounts()" data-toggle="collapse" href="#collapsibleDiv"
                        role="button" aria-expanded="false" aria-controls="collapsibleDiv">
                        <i class="fa fa-bar-chart" style="margin-right: 3%"></i>Dashboard
                    </a>
                </div>
                <div id="collapsibleDiv" class="collapse  col-md-12">
                    <div class="card card-body mt-2">

                        <canvas id="statusChart" width="400" height="200"></canvas>

                        {{-- <div class="row mt-2">
                            <div id="acknowledgmentDiv" class="col-md-12">

                                <div id="other" class="row">
                                    <!-- Status counts for acknowledgment will be dynamically inserted here -->
                                </div>

                            </div>
                        </div>
                        <div class="row mt-2">
                            <div id="acknowledgmentSection" class="col-md-12">

                                <h4>Acknowledgment</h4>
                                <div id="acknowledgmentContainer" class="row">
                                    <!-- Status counts for acknowledgment will be dynamically inserted here -->
                                </div>

                            </div>
                        </div>
                        <!-- Re-Acknowledgment Section -->
                        <div class="row mt-2">
                            <div id="reacknowledgmentDiv" class="col-md-12">

                                <h4>Re-Acknowledgment</h4>
                                <div id="reacknowledgmentContainer" class="row">
                                    <!-- Status counts for re-acknowledgment will be dynamically inserted here -->
                                </div>

                            </div>
                        </div> --}}
                    </div>
                </div>

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
                        <option
                            value="student acknowledge all data as correct but payment & preview pdf approval is pending">
                            Student acknowledges all data as correct but payment & preview PDF approval is pending</option>
                        <option
                            value="student acknowledge all data as correct, Payment is completed but preview pdf approval is pending">
                            Student acknowledges all data as correct, payment is completed but preview PDF approval is
                            pending</option>
                        <option
                            value="student acknowledge all data as correct, Payment is completed and preview pdf is approved">
                            Student acknowledges all data as correct, payment is completed and preview PDF is approved
                        </option>
                        <option value="student marked few data as incorrect and admin’s action pending">Student marked a
                            few
                            data as incorrect and admin’s action pending</option>
                        <option value="admin performed correction but student’s re-acknowledgement pending">Admin performed
                            correction but student’s re-acknowledgement pending</option>
                        <option
                            value="student re-acknowledged new data as correct but payment & preview pdf approval is pending">
                            Student re-acknowledged new data as correct but payment & preview PDF approval is pending
                        </option>
                        <option
                            value="student re-acknowledged new data as correct, Payment is completed but preview pdf approval is pending">
                            Student re-acknowledged new data as correct, payment is completed but preview PDF approval is
                            pending</option>
                        <option
                            value="student re-acknowledged new data as correct, Payment is completed and preview pdf is approved">
                            Student re-acknowledged new data as correct, payment is completed and preview PDF is approved
                        </option>
                    </select>


                </div>

                <div class="col-md-3">
                    <label for="course_filter">Course Name:</label>
                    <input type="text" id="course_filter" class="form-control" placeholder="Filter by Course Name">
                </div>

                <div class="col-md-12" style="text-align: right;margin-top:1%">
                    <a id="export-custom-btn" class="btn btn-success"><i class="fa fa-file-excel-o" style="margin-right: 3%"></i>Export Degree Data</a>


                    <a id="export-payment-btn" class="btn btn-success"><i class="fa fa-file-excel-o" style="margin-right: 3%"></i>Export Payments</a>
                    <a id="export-btn" class="btn btn-success"><i class="fa fa-file-excel-o" style="margin-right: 3%"></i>Export Data</a>



                </div>
            </div>

            <div class="">
                <table id="example" class="table table-hover display" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>PRN</th>
                            <th>Name as per TC</th>
                            <th>Student Email ID</th>
                            <th>DOB</th>
                            <th>Competency Level</th>
                            <th>Collection Mode</th>
                            <th>CGPA</th>
                            <th>Status</th>
                            <th>Created Date</th>
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
            buttons: [{
                extend: 'excelHtml5',
                text: 'Export Excel',
                title: 'Data Export',
                className: 'btn btn-success'
            }],
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
            'buttons': [{
                extend: 'excelHtml5',
                text: 'Export to Excel',
                titleAttr: 'Export to Excel',
                className: 'btn btn-success' // Optional: add custom classes
            }]

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
                            fetchStatusCounts();
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
                                if (error.errors) {
                                    error.errors.forEach(function(err) {
                                        errorContentHtml += '<li>' + err + '</li>';
                                    });
                                } else {
                                    if (error.message) {
                                        errorContentHtml += '<li>' + error.message +
                                            '</li>';
                                    }
                                }

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

        $('#export-payment-btn').on('click', function(e) {
            e.preventDefault(); // Prevent the default link behavior  
            var baseUrl = '{{ route('convo_student.export_convo_students_transaction') }}'; 
            // Redirect to the export URL
            window.location.href = baseUrl ;
        });

        
        $('#export-custom-btn').on('click', function(e) {
            e.preventDefault(); // Prevent the default link behavior

            // Collect filter values
            var prn = $('#prn_filter').val();
            var name = $('#name_filter').val();
            var status = $('#status_filter').val();
            var course = $('#course_filter').val();

            // Construct the URL with query parameters
            var baseUrl = '{{ route('convo_student.export_custom_convo_students') }}';
            var params = $.param({
                prn: prn,
                name: name,
                status: status,
                course: course
            });

            // Redirect to the export URL
            window.location.href = baseUrl + '?' + params;
        });


        // function fetchStatusCounts() {
        //     $.ajax({
        //         url: "<?= route('convo_student.status_counts') ?>",
        //         method: 'GET',
        //         success: function(data) {
        //             $('#reacknowledgmentContainer').html('');
        //             $('#acknowledgmentContainer').html('');
        //             $('#other').html('');
        //             const acknowledgmentContainer = $('#acknowledgmentContainer');
        //             const reacknowledgmentContainer = $('#reacknowledgmentContainer');
        //             const other = $('#other');

        //             acknowledgmentContainer.empty(); // Clear the container before adding new data
        //             reacknowledgmentContainer.empty(); // Clear the container before adding new data

        //             // Populate acknowledgment section
        //             $.each(data['acknowledgment'], function(index, value) {
        //                 acknowledgmentContainer.append(
        //                     `<div class="col-md-3">
    //                         <div class="card custom_card mb-2">
    //                             <div class="card-body custom_card_body">
    //                                  <h3><b>${value.count}</b></h3>      
    //                                 <h5 class="card-title">${value.short_label}</h5>

    //                             </div>
    //                         </div>
    //                     </div>`
        //                 );
        //             });

        //             // Populate re-acknowledgment section
        //             $.each(data['re_acknowledgment'], function(index, value) {
        //                 reacknowledgmentContainer.append(
        //                     `<div class="col-md-3">
    //                         <div class="card mb-2 custom_card">
    //                             <div class="card-body custom_card_body">
    //                                  <h3><b>${value.count}</b></h3>      
    //                                 <h5 class="card-title">${value.short_label}</h5>

    //                              </div>
    //                         </div>
    //                     </div>`
        //                 );
        //             });
        //             $.each(data['other'], function(index, value) {
        //                 other.append(
        //                     `<div class="col-md-3">
    //                         <div class="card mb-2 custom_card">
    //                             <div class="card-body custom_card_body">
    //                                   <h3><b>${value.count}</b></h3>      
    //                                 <h5 class="card-title">${value.short_label}</h5>

    //                             </div>
    //                         </div>
    //                     </div>`
        //                 );
        //             });

        //         },
        //         error: function(xhr, status, error) {
        //             console.error('An error occurred while fetching status counts:', error);
        //         }
        //     });
        // }
        let statusChart = null; // Global variable to hold the chart instance

        function fetchStatusCounts() {
            $.ajax({
                url: "<?= route('convo_student.status_counts') ?>",
                method: 'GET',
                success: function(data) {
                    // Extract data for the chart
                    const acknowledgmentLabels = [];
                    const acknowledgmentCounts = [];
                    const reacknowledgmentLabels = [];
                    const reacknowledgmentCounts = [];
                    const otherLabels = [];
                    const otherCounts = [];

                    $.each(data['other'], function(index, value) {
                        otherLabels.push(value.short_label);
                        otherCounts.push(value.count);
                    });
                    // Process acknowledgment data
                    $.each(data['acknowledgment'], function(index, value) {
                        acknowledgmentLabels.push(value.short_label);
                        acknowledgmentCounts.push(value.count);
                    });

                    // Process re-acknowledgment data
                    $.each(data['re_acknowledgment'], function(index, value) {
                        reacknowledgmentLabels.push(value.short_label);
                        reacknowledgmentCounts.push(value.count);
                    });

                    // Destroy existing chart if it exists
                    if (statusChart !== null) {
                        statusChart.destroy();
                    }

                    // Create the horizontal bar chart
                    const ctx = document.getElementById('statusChart').getContext('2d');
                    statusChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: otherLabels.concat(acknowledgmentLabels).concat(
                                reacknowledgmentLabels),
                            datasets: [{
                                label: 'Count',
                                data: otherCounts.concat(acknowledgmentCounts).concat(
                                    reacknowledgmentCounts),
                                backgroundColor: [
                                    'rgba(255, 159, 64, 0.2)',
                                    'rgba(75, 192, 192, 0.2)',
                                    'rgba(255, 206, 86, 0.2)',
                                    'rgba(153, 102, 255, 0.2)',
                                    'rgba(54, 162, 235, 0.2)',
                                    'rgba(75, 192, 192, 0.2)',
                                    'rgba(255, 206, 86, 0.2)',
                                    'rgba(153, 102, 255, 0.2)',
                                    'rgba(54, 162, 235, 0.2)',
                                    'rgba(75, 192, 192, 0.2)',
                                ],
                                borderColor: [
                                    'rgba(255, 159, 64, 1)',
                                    'rgba(75, 192, 192, 1)',
                                    'rgba(255, 206, 86, 1)',
                                    'rgba(153, 102, 255, 1)',
                                    'rgba(54, 162, 235, 1)',
                                    'rgba(75, 192, 192, 1)',
                                    'rgba(255, 206, 86, 1)',
                                    'rgba(153, 102, 255, 1)',
                                    'rgba(54, 162, 235, 1)',
                                    'rgba(75, 192, 192, 1)',
                                ],
                                borderWidth: 2
                            }]
                        },
                        options: {
                            indexAxis: 'y', // This makes the bar chart horizontal
                            plugins: {
                                legend: {
                                    display: false // This hides the legend
                                },
                                datalabels: {
                                    anchor: 'end',
                                    align: 'end',
                                    formatter: (value) => value,
                                    color: '#000', // Data labels color
                                    font: {
                                        weight: 'bold' // Data labels font weight
                                    },
                                    padding: 4
                                }
                            },
                            scales: {
                                x: {
                                    beginAtZero: true,
                                    ticks: {
                                        font: {
                                            weight: 'bold', // X-axis labels font weight
                                            size: 14 // Optional: adjust font size if needed
                                        },
                                        color: '#000' // X-axis labels color
                                    }
                                },
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        font: {
                                            weight: 'bold', // Y-axis labels font weight
                                            size: 14 // Optional: adjust font size if needed
                                        },
                                        color: '#000' // Y-axis labels color
                                    }
                                }
                            }
                        },
                        plugins: [ChartDataLabels] // Add this to register the plugin
                    });

                },
                error: function(xhr, status, error) {
                    console.error('An error occurred while fetching status counts:', error);
                }
            });
        }


        $(document).ready(function() {
            fetchStatusCounts();
        });



        $(document).ready(function() {
            fetchStatusCounts();
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

@stop
