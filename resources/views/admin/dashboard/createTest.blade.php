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
                                            <div class="huge"><span id="admins">{{$active_admin_user}}</span></div>
                                            <div>Active</div>
                                        </div>
                                        <div class="col-xs-6 text-right">
                                            <div class="huge"><span id="inactive_admins">{{ $inactive_admin_user }}</span></div>
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
                                            <div class="huge"><span id="institute">{{ $active_institute_user }}</span></div>
                                            <div>Active</div>
                                        </div>
                                        <div class="col-xs-6 text-right">
                                            <div class="huge"><span id="inactive_institute">{{ $inactive_institute_user }}</span></div>
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
                                            <div class="huge"><span id="document">{{ $active_template }}</span></div>
                                            <div>Active</div>
                                        </div>
                                        <div class="col-xs-6 text-right">
                                            <div class="huge"><span id="inactive_document">{{ $inactive_template }}</span></div>
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
                                            <div class="huge"><span id="users">{{ $active_user }}</span></div>
                                            <div>Active</div>
                                        </div>
                                        <div class="col-xs-6 text-right">
                                            <div class="huge"><span id="inactive_users">{{ $inactive_user }}</span></div>
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
                                            <div class="huge" style="cursor: pointer;font-size: 30px;"><span id="certificate" title="{{ $active_certificates }}">{{ $active_certificates_short }}</span></div>
                                            <div>Active</div>
                                        </div>
                                        <div class="col-xs-6 text-right">
                                            <div class="huge" style="cursor: pointer;font-size: 30px;"><span id="inactive_certificate" title="{{ $inactive_certificates }}">{{ $inactive_certificates_short }}</span></div>
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
                                                        <td>{{ $active_unique_android_scanned }}({{ $active_total_android_scanned }})</td>
                                                        <td>{{ $inactive_unique_android_scanned }}({{ $inactive_total_android_scanned }})</td>


                                                        <td>{{ $institute_active_unique_android_scanned }}({{ $institute_active_total_android_scanned }})</td>
                                                        <td>{{ $institute_inactive_unique_android_scanned }}({{ $institute_inactive_total_android_scanned }})</td>
                                                    </tr>
                                                    <tr id="andIos">
                                                        <td>IOS App</td>
                                                        <td>{{ $active_unique_ios_scanned }}({{ $active_total_ios_scanned }})</td>
                                                        <td>{{ $inactive_unique_ios_scanned }}({{ $inactive_total_ios_scanned }})</td>

                                                        <td>{{ $institute_active_unique_ios_scanned }}({{ $institute_active_total_ios_scanned }})</td>
                                                        <td>{{ $institute_inactive_unique_ios_scanned }}({{ $institute_inactive_total_ios_scanned }})</td>
                                                    </tr>
                                                    <tr id="andWebapp">
                                                        <td>Web App</td>
                                                        <td>{{ $active_unique_webapp_scanned }}({{ $active_total_webapp_scanned }})</td>
                                                        <td>{{ $inactive_unique_webapp_scanned }}({{ $inactive_total_webapp_scanned }})</td>

                                                        <td>{{ $institute_active_unique_webapp_scanned }}({{ $institute_active_total_webapp_scanned }})</td>
                                                        <td>{{ $institute_inactive_total_webapp_scanned }}({{ $institute_inactive_unique_webapp_scanned }})</td>
                                                    </tr>
                                                    <tr id="andTotal">
                                                        <td>Total</td>
                                                        <td>{{ $active_grandtotal_unique_scannd }}({{ $active_grandtotal_total_scanned }})</td>
                                                        <td>{{ $inactive_grandtotal_unique_scanned }}({{ $inactive_grandtotal_total_scanned }})</td>
                                                    
                                                        <td>{{ $institute_active_grandtotal_unique_scanned }}({{ $institute_active_grandtotal_total_scanned }})</td>
                                                        <td>{{ $institute_inactive_grandtotal_unique_scanned }}({{ $institute_inactive_grandtotal_total_scanned }})</td>
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
                                                    @foreach($transaction as $value)
                                                    <tr>
                                                        <td>{{ $value['trans_id_ref'] }}</td>
                                                        <td class="text-center">{{ $value['amount']}}</td>
                                                        <td>{{ $value['fullname'] }}</td>
                                                        <td>{{ $value['student_name'] }}</td>
                                                    </tr>
                                                    @endforeach
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
                                                    @foreach($scan_data as $value)
                                                        <?php $device_type = ""; ?>
                                                        <tr>
                                                            @if ($value['device_type']== 'android')
                                                                <?php  $device_type = '<i class="fa fa-fw fa-2x fa-android green"></i>'  ?>
                                                            @elseif($value['device_type'] == 'ios')
                                                                <?php $device_type = '<i class="fa fa-fw fa-2x fa-apple"></i>' ?>
                                                            @elseif($value['device_type'] == 'WebApp')
                                                                <?php $device_type = '<i class="fa fa-fw fa-2x fa-desktop"></i>'?>
                                                            @else
                                                                <?php $device_type = "No device" ?>
                                                            @endif
                                                            <?php  $date_time = date("d M Y h:i A", strtotime($value['date_time'])); 
                                                                    
                                                                 
                                                                    if($get_file_aws_local_flag == '1'){
                                                                        $qr_code_image_path = \Config::get('constant.aws_canvas_upload_path').'/'.$value['path'];
                                                                    }
                                                                    else{
                                                                        $qr_code_image_path = \Config::get('constant.server_canvas_upload_path').'/'.$value['path'];
                                                                    }
                                                            ?>

                                                                <td class='text-center'><img src="{{ $qr_code_image_path }}"  class='' style='width:50px;width:50px'></td>
                                                                <td class='text-center'>{{ $value['username']}} <br><?= $device_type ?><br><label style='font-size: 8px;'>{{ $date_time }}</label></td>
                                                        </tr>
                                                    @endforeach
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
@stop
