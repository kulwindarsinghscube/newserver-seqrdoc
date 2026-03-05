@extends('admin.layout.layout')
@section('content')

<div class="container">
    <div class="col-xs-12">
        <div class="clearfix">
            <div id="">
                <div class="">
                    <br/><br/>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="col-md-3">
                                <div class="dashboard-card">
                                    <div class="row">
                                        <div class="col-xs-6">
                                            <div class="huge"><span id="admins"></span></div>
                                            <div>Active</div>
                                        </div>
                                        <div class="col-xs-6 text-right">
                                            <div class="huge"><span id="inactive_admins"></span></div>
                                            <div>Inactive</div>
                                        </div>
                                        <div class="col-xs-12">
                                            <div class="link-url">
                                                <a href="{{ URL::route('adminmaster.index') }}" class="clearfix">
                                                <span class="pull-left">Admin Users</span>
                                                <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="dashboard-card">
                                    <div class="row">
                                        <div class="col-xs-6">
                                            <div class="huge"><span id="institute"></span></div>
                                            <div>Active</div>
                                        </div>
                                        <div class="col-xs-6 text-right">
                                            <div class="huge"><span id="inactive_institute"></span></div>
                                            <div>Inactive</div>
                                        </div>
                                        <div class="col-xs-12">
                                            <div class="link-url">
                                                <a href="{{ URL::route('institutemaster.index') }}" class="clearfix">
                                                <span class="pull-left">Institute Users</span>
                                                <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="dashboard-card">
                                    <div class="row">
                                        <div class="col-xs-6">
                                            <div class="huge"><span id="document"></span></div>
                                            <div>Active</div>
                                        </div>
                                        <div class="col-xs-6 text-right">
                                            <div class="huge"><span id="inactive_document"></span></div>
                                            <div>Inactive</div>
                                        </div>
                                        <div class="col-xs-12">
                                            <div class="link-url">
                                                <a href="{{ URL::route('template-master.index') }}" class="clearfix">
                                                <span class="pull-left">Templates</span>
                                                <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="dashboard-card">
                                    <div class="row">
                                        <div class="col-xs-6">
                                            <div class="huge"><span id="users"></span></div>
                                            <div>Active</div>
                                        </div>
                                        <div class="col-xs-6 text-right">
                                            <div class="huge"><span id="inactive_users"></span></div>
                                            <div>Inactive</div>
                                        </div>
                                        <div class="col-xs-12">
                                            <div class="link-url">
                                                <a href="{{ URL::route('usermaster.index') }}" class="clearfix">
                                                <span class="pull-left">View Users</span>
                                                <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="dashboard-card">
                                    <div class="row">
                                        <div class="col-xs-6">
                                            <div class="huge" style="cursor: pointer;font-size: 30px;"><span id="certificate" title=""></span></div>
                                            <div>Active</div>
                                        </div>
                                        <div class="col-xs-6 text-right">
                                            <div class="huge" style="cursor: pointer;font-size: 30px;"><span id="inactive_certificate" title=""></span></div>
                                            <div>Inactive</div>
                                        </div>
                                        <div class="col-xs-12">
                                            <div class="link-url">
                                                <a href="{{ URL::route('certificateManagement.index') }}" class="clearfix">
                                                <span class="pull-left">Certificates</span>
                                                <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-9">
                                <div class="dashboard-card">
                                    <div class="row">
                                        <div class="col-xs-12">
                                            <table class="table table-striped" border="1">
                                                <thead style="font-weight: bold;" >
                                                    <tr>
                                                        <th></th>
                                                        <th colspan="2" style="text-align: center;">By Verifier</th>
                                                        <th colspan="2" style="text-align: center;">By Institute</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td></td>
                                                        <td>Active Documents</td>
                                                        <td>Inative Documents</td>
                                                        <td>Active Documents</td>
                                                        <td>Inative Documents</td>
                                                    </tr>
                                                    <tr id="andHtml">
                                                        <td>Android App</td>
                                                        <td><span id="active_unique_android_scanned"></span> (<span id="active_total_android_scanned"></span>)</td>
                                                        <td><span id="inactive_unique_android_scanned"></span>(<span id="inactive_total_android_scanned"></span>)</td>


                                                        <td><span id="institute_active_unique_android_scanned"></span> (<span id="institute_active_total_android_scanned"></span>)</td>
                                                        <td><span id="institute_inactive_unique_android_scanned"></span> (<span id="institute_inactive_total_android_scanned"></span>)</td>
                                                    </tr>
                                                    <tr id="andIos">
                                                        <td>IOS App</td>
                                                        <td><span id="active_unique_ios_scanned"></span>(<span id="active_total_ios_scanned"></span>)</td>
                                                        <td><span id="inactive_unique_ios_scanned"></span>(<span id="inactive_total_ios_scanned"></span>)</td>

                                                        <td><span id="institute_active_unique_ios_scanned"></span>(<span id="institute_active_total_ios_scanned"></span>)</td>
                                                        <td><span id="institute_inactive_unique_ios_scanned"></span>(<span id="institute_inactive_total_ios_scanned"></span>)</td>
                                                    </tr>
                                                    <tr id="andWebapp">
                                                        <td>Web App</td>
                                                        <td><span id="active_unique_webapp_scanned"></span>(<span id="active_total_webapp_scanned"></span>)</td>
                                                        <td><span id="inactive_unique_webapp_scanned"></span>(<span id="inactive_total_webapp_scanned"></span>)</td>

                                                        <td><span id="institute_active_unique_webapp_scanned"></span>(<span id="institute_active_total_webapp_scanned"></span>)</td>
                                                        <td><span id="institute_inactive_total_webapp_scanned"></span>(<span id="institute_inactive_unique_webapp_scanned"></span>)</td>
                                                    </tr>
                                                    <tr id="andTotal">
                                                        <td>Total</td>
                                                        <td><span id="active_grandtotal_unique_scannd"></span>(<span id="active_grandtotal_total_scanned"></span>)</td>
                                                        <td><span id="inactive_grandtotal_unique_scanned"></span>(<span id="inactive_grandtotal_total_scanned"></span>)</td>
                                                    
                                                        <td><span id="institute_active_grandtotal_unique_scanned"></span>(<span id="institute_active_grandtotal_total_scanned"></span>)</td>
                                                        <td><span id="institute_inactive_grandtotal_unique_scanned"></span>(<span id="institute_inactive_grandtotal_total_scanned"></span>)</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="col-xs-12">
                                            <div class="link-url">
                                                <a href="{{ URL::route('scanHistory.index') }}" class="clearfix">
                                                <span class="pull-left">View Scan History</span>
                                                <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="col-md-7">
                                <div class="dashboard-card">
                                    <div class="row">
                                        <div class="col-xs-12">
                                            <h3 style="color:#172B4D" class="alert alert-info">Last 5 Transactions</h3>
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th>Transaction ID</th>
                                                        <th class="text-center">Amount</th>
                                                        <th>User</th>
                                                        <th>Student</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="trans_response">
                                                    {{-- @foreach($transaction as $value)
                                                    <tr>
                                                        <td>{{ $value['trans_id_ref'] }}</td>
                                                        <td class="text-center">{{ $value['amount']}}</td>
                                                        <td>{{ $value['fullname'] }}</td>
                                                        <td>{{ $value['student_name'] }}</td>
                                                    </tr>
                                                    @endforeach --}}
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="col-xs-12">
                                            <div class="link-url">
                                                <a href="{{ URL::route('transaction.index') }}" class="clearfix">
                                                <span class="pull-left">View All Transactions</span>
                                                <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="dashboard-card">
                                    <div class="row">
                                        <div class="col-xs-12">
                                            <h3 style="color:#172B4D" class="alert alert-info">Last 3 Scans</h3>
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th class="text-center">QR</th>
                                                        <th class="text-center">Scanned by</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="scan_response">
                                                    
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="col-xs-12">
                                            <div class="link-url">
                                                <a href="{{ URL::route('scanHistory.index') }}" class="clearfix">
                                                <span class="pull-left">View Scan History</span>
                                                <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



@stop
@section('style')
<style>
    .dashboard-card{
    background:#fff;
    box-shadow:0px 3px 6px #ccc;
    padding:20px 25px 0 25px;
    border:1px solid #ccc;
    border-bottom:2px solid #0052CC;
    margin:15px 0;
    }
    .link-url{
    padding:10px 15px;
    border-top:1px solid #ccc;
    margin-top:15px;
    }
    .link-url a{
    color:#0052CC;
    }
</style>
@stop


@section('script')

<script type="text/javascript">
    $('[title]').tooltip(); 
</script>   

<!-- rohit code 21/07/2023-->
<script type="text/javascript">
    // admin count
    $(document).ready(function() {
        $.ajax({
            type: "POST",
            url: '{{route('AdminCount')}}',
            data:{ 
                _token:'{{ csrf_token() }}'
            },
            cache: false,
            dataType: 'json',
            success: function(data) {
                $('#admins').each(function () {
                    $(this).prop('Counter', 0).animate({
                            Counter: data.active_admin_user
                        }, {
                        duration: 3000,
                        easing: 'swing',
                        step: function (now) {                      
                            $(this).text(this.Counter.toFixed(0));
                        }
                    });
                });


                $('#inactive_admins').each(function () {
                    $(this).prop('Counter', 0).animate({
                            Counter: data.inactive_admin_user
                        }, {
                        duration: 3000,
                        easing: 'swing',
                        step: function (now) {                      
                            $(this).text(this.Counter.toFixed(0));
                        }
                    });
                });

                // $('#admins').html(data.active_admin_user);
                // console.log(data.active_admin_user);
            }
        });
    });


    // institude user count
    $(document).ready(function() {
        $.ajax({
            type: "POST",
            url: '{{route('instituteUserCount')}}',
            data:{ 
                _token:'{{ csrf_token() }}'
            },
            cache: false,
            dataType: 'json',
            success: function(data) {
                $('#institute').each(function () {
                    $(this).prop('Counter', 0).animate({
                            Counter: data.active_institute_user
                        }, {
                        duration: 3000,
                        easing: 'swing',
                        step: function (now) {                      
                            $(this).text(this.Counter.toFixed(0));
                        }
                    });
                });


                $('#inactive_institute').each(function () {
                    $(this).prop('Counter', 0).animate({
                            Counter: data.inactive_institute_user
                        }, {
                        duration: 3000,
                        easing: 'swing',
                        step: function (now) {                      
                            $(this).text(this.Counter.toFixed(0));
                        }
                    });
                });

                // $('#admins').html(data.active_admin_user);
                // console.log(data.active_admin_user);
            }
        });
    });


    // template count
    $(document).ready(function() {
        $.ajax({
            type: "POST",
            url: '{{route('templateCount')}}',
            data:{ 
                _token:'{{ csrf_token() }}'
            },
            cache: false,
            dataType: 'json',
            success: function(data) {
                $('#document').each(function () {
                    $(this).prop('Counter', 0).animate({
                            Counter: data.active_template
                        }, {
                        duration: 1000,
                        easing: 'swing',
                        step: function (now) {                      
                            $(this).text(this.Counter.toFixed(0));
                        }
                    });
                });


                $('#inactive_document').each(function () {
                    $(this).prop('Counter', 0).animate({
                            Counter: data.inactive_template
                        }, {
                        duration: 3000,
                        easing: 'swing',
                        step: function (now) {                      
                            $(this).text(this.Counter.toFixed(0));
                        }
                    });
                });

                // $('#admins').html(data.active_admin_user);
                // console.log(data.active_admin_user);
            }
        });
    });


    // user count
    $(document).ready(function() {
        $.ajax({
            type: "POST",
            url: '{{route('userCount')}}',
            data:{ 
                _token:'{{ csrf_token() }}'
            },
            cache: false,
            dataType: 'json',
            success: function(data) {
                $('#users').each(function () {
                    $(this).prop('Counter', 0).animate({
                            Counter: data.active_user
                        }, {
                        duration: 1000,
                        easing: 'swing',
                        step: function (now) {                      
                            $(this).text(this.Counter.toFixed(0));
                        }
                    });
                });


                $('#inactive_users').each(function () {
                    $(this).prop('Counter', 0).animate({
                            Counter: data.inactive_user
                        }, {
                        duration: 3000,
                        easing: 'swing',
                        step: function (now) {                      
                            $(this).text(this.Counter.toFixed(0));
                        }
                    });
                });

                // $('#admins').html(data.active_admin_user);
                // console.log(data.active_admin_user);
            }
        });
    });

    // certificate count
    $(document).ready(function() {
        $.ajax({
            type: "POST",
            url: '{{route('certificateCount')}}',
            data:{ 
                _token:'{{ csrf_token() }}'
            },
            cache: false,
            dataType: 'json',
            success: function(data) {
                $('#certificate').each(function () {
                    $(this).prop('Counter', 0).animate({
                            Counter: data.active_certificates_short
                        }, {
                        duration: 3000,
                        easing: 'swing',
                        step: function (now) {                      
                            $(this).text(this.Counter.toFixed(0));
                        }
                    });
                });
                $('#certificate').attr('data-original-title',data.active_certificates)


                $('#inactive_certificate').each(function () {
                    $(this).prop('Counter', 0).animate({
                            Counter: data.inactive_certificates_short
                        }, {
                        duration: 3000,
                        easing: 'swing',
                        step: function (now) {                      
                            $(this).text(this.Counter.toFixed(0));
                        }
                    });
                });
                $('#inactive_certificate').attr('data-original-title',data.inactive_certificates)

                // $('#admins').html(data.active_admin_user);
                // console.log(data.active_admin_user);
            }
        });
    });


    // android app count
    $(document).ready(function() {
        $.ajax({
            type: "POST",
            url: '{{route('appAndroidCount')}}',
            data:{ 
                _token:'{{ csrf_token() }}'
            },
            cache: false,
            dataType: 'json',
            success: function(data) {
                $('#active_unique_android_scanned').each(function () {
                    $(this).prop('Counter', 0).animate({
                            Counter: data.active_unique_android_scanned
                        }, {
                        duration: 3000,
                        easing: 'swing',
                        step: function (now) {                      
                            $(this).text(this.Counter.toFixed(0));
                        }
                    });
                });

                $('#active_total_android_scanned').each(function () {
                    $(this).prop('Counter', 0).animate({
                            Counter: data.active_total_android_scanned
                        }, {
                        duration: 3000,
                        easing: 'swing',
                        step: function (now) {                      
                            $(this).text(this.Counter.toFixed(0));
                        }
                    });
                });

                $('#inactive_unique_android_scanned').each(function () {
                    $(this).prop('Counter', 0).animate({
                            Counter: data.inactive_unique_android_scanned
                        }, {
                        duration: 3000,
                        easing: 'swing',
                        step: function (now) {                      
                            $(this).text(this.Counter.toFixed(0));
                        }
                    });
                });

                $('#inactive_total_android_scanned').each(function () {
                    $(this).prop('Counter', 0).animate({
                            Counter: data.inactive_total_android_scanned
                        }, {
                        duration: 3000,
                        easing: 'swing',
                        step: function (now) {                      
                            $(this).text(this.Counter.toFixed(0));
                        }
                    });
                });
                
                $('#institute_active_total_android_scanned').html(data.institute_active_total_android_scanned);
                $('#institute_active_unique_android_scanned').html(data.institute_active_unique_android_scanned);
                $('#institute_inactive_total_android_scanned').html(data.institute_inactive_total_android_scanned);
                $('#institute_inactive_unique_android_scanned').html(data.institute_inactive_unique_android_scanned);
            }
        });
    });

    // ios app count
    $(document).ready(function() {
        $.ajax({
            type: "POST",
            url: '{{route('appIosCount')}}',
            data:{ 
                _token:'{{ csrf_token() }}'
            },
            cache: false,
            dataType: 'json',
            success: function(data) {                
                $('#active_total_ios_scanned').html(data.active_total_ios_scanned);
                $('#active_unique_ios_scanned').html(data.active_unique_ios_scanned);
                $('#inactive_total_ios_scanned').html(data.inactive_total_ios_scanned);
                $('#inactive_unique_ios_scanned').html(data.inactive_unique_ios_scanned);
                $('#institute_active_total_ios_scanned').html(data.institute_active_total_ios_scanned);
                $('#institute_active_unique_ios_scanned').html(data.institute_active_unique_ios_scanned);
                $('#institute_inactive_total_ios_scanned').html(data.institute_inactive_total_ios_scanned);
                $('#institute_inactive_unique_ios_scanned').html(data.institute_inactive_unique_ios_scanned);
            }
        });
    });


    // web app count
    $(document).ready(function() {
        $.ajax({
            type: "POST",
            url: '{{route('appWebCount')}}',
            data:{ 
                _token:'{{ csrf_token() }}'
            },
            cache: false,
            dataType: 'json',
            success: function(data) {                
                $('#active_total_webapp_scanned').html(data.active_total_webapp_scanned);
                $('#active_unique_webapp_scanned').html(data.active_unique_webapp_scanned);
                $('#inactive_total_webapp_scanned').html(data.inactive_total_webapp_scanned);
                $('#inactive_unique_webapp_scanned').html(data.inactive_unique_webapp_scanned);
                $('#institute_active_total_webapp_scanned').html(data.institute_active_total_webapp_scanned);
                $('#institute_active_unique_webapp_scanned').html(data.institute_active_unique_webapp_scanned);
                $('#institute_inactive_total_webapp_scanned').html(data.institute_inactive_total_webapp_scanned);
                $('#institute_inactive_unique_webapp_scanned').html(data.institute_inactive_unique_webapp_scanned);
            }
        });
    });

    // web app count
    $(document).ready(function() {
        $.ajax({
            type: "POST",
            url: '{{route('appGrandCount')}}',
            data:{ 
                _token:'{{ csrf_token() }}'
            },
            cache: false,
            dataType: 'json',
            success: function(data) {
                $('#active_grandtotal_total_scanned').each(function () {
                    $(this).prop('Counter', 0).animate({
                            Counter: data.active_grandtotal_total_scanned
                        }, {
                        duration: 3000,
                        easing: 'swing',
                        step: function (now) {                      
                            $(this).text(this.Counter.toFixed(0));
                        }
                    });
                });
                $('#active_grandtotal_unique_scannd').each(function () {
                    $(this).prop('Counter', 0).animate({
                            Counter: data.active_grandtotal_unique_scannd
                        }, {
                        duration: 3000,
                        easing: 'swing',
                        step: function (now) {                      
                            $(this).text(this.Counter.toFixed(0));
                        }
                    });
                });     
                $('#inactive_grandtotal_total_scanned').each(function () {
                    $(this).prop('Counter', 0).animate({
                            Counter: data.inactive_grandtotal_total_scanned
                        }, {
                        duration: 3000,
                        easing: 'swing',
                        step: function (now) {                      
                            $(this).text(this.Counter.toFixed(0));
                        }
                    });
                });
                $('#inactive_grandtotal_unique_scanned').each(function () {
                    $(this).prop('Counter', 0).animate({
                            Counter: data.inactive_grandtotal_unique_scanned
                        }, {
                        duration: 3000,
                        easing: 'swing',
                        step: function (now) {                      
                            $(this).text(this.Counter.toFixed(0));
                        }
                    });
                }); 
                $('#institute_active_grandtotal_total_scanned').each(function () {
                    $(this).prop('Counter', 0).animate({
                            Counter: data.institute_active_grandtotal_total_scanned
                        }, {
                        duration: 3000,
                        easing: 'swing',
                        step: function (now) {                      
                            $(this).text(this.Counter.toFixed(0));
                        }
                    });
                });
                $('#institute_active_grandtotal_unique_scanned').each(function () {
                    $(this).prop('Counter', 0).animate({
                            Counter: data.institute_active_grandtotal_unique_scanned
                        }, {
                        duration: 3000,
                        easing: 'swing',
                        step: function (now) {                      
                            $(this).text(this.Counter.toFixed(0));
                        }
                    });
                }); 
                $('#institute_inactive_grandtotal_total_scanned').each(function () {
                    $(this).prop('Counter', 0).animate({
                            Counter: data.institute_inactive_grandtotal_total_scanned
                        }, {
                        duration: 3000,
                        easing: 'swing',
                        step: function (now) {                      
                            $(this).text(this.Counter.toFixed(0));
                        }
                    });
                });   
                $('#institute_inactive_grandtotal_unique_scanned').each(function () {
                    $(this).prop('Counter', 0).animate({
                            Counter: data.institute_inactive_grandtotal_unique_scanned
                        }, {
                        duration: 3000,
                        easing: 'swing',
                        step: function (now) {                      
                            $(this).text(this.Counter.toFixed(0));
                        }
                    });
                });    
                //$('#active_grandtotal_total_scanned').html(data.active_grandtotal_total_scanned);
                //$('#active_grandtotal_unique_scannd').html(data.active_grandtotal_unique_scannd);
                // $('#inactive_grandtotal_total_scanned').html(data.inactive_grandtotal_total_scanned);
                // $('#inactive_grandtotal_unique_scanned').html(data.inactive_grandtotal_unique_scanned);
                // $('#institute_active_grandtotal_total_scanned').html(data.institute_active_grandtotal_total_scanned);
                // $('#institute_active_grandtotal_unique_scanned').html(data.institute_active_grandtotal_unique_scanned);
                // $('#institute_inactive_grandtotal_total_scanned').html(data.institute_inactive_grandtotal_total_scanned);
                // $('#institute_inactive_grandtotal_unique_scanned').html(data.institute_inactive_grandtotal_unique_scanned);
            }
        });
    });
    

    // transacation data
    $(document).ready(function() {
        $.ajax({
            type: "POST",
            url: '{{route('transactionData')}}',
            data:{ 
                _token:'{{ csrf_token() }}'
            },
            cache: false,
            dataType: 'json',
            success: function(data) {
                $('#trans_response').html(data.html);             
                // console.log(data.html);
            }
        });
    });

    // scan data
    $(document).ready(function() {
        $.ajax({
            type: "POST",
            url: '{{route('scanData')}}',
            data:{ 
                _token:'{{ csrf_token() }}'
            },
            cache: false,
            dataType: 'json',
            success: function(data) {
                $('#scan_response').html(data.html);             
                // console.log(data.html);
            }
        });
    });

</script>   
<!-- rohit code 21/07/2023-->
@stop
