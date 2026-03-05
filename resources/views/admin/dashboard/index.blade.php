@extends('admin.layout.layout')
@section('style')
<style type="text/css">
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
@section('content')
<div id="">
	<div class=""><br/><br/>
		<div class="row">
			<div class="col-lg-12">
       
				<div class="col-md-3"> 
					<div class="dashboard-card">
						<div class="row">
							<div class="col-xs-6">
								<div class="huge"><span id="active_admins">0</span></div>
								<div>Active</div>
							</div>
							<div class="col-xs-6 text-right">
								<div class="huge"><span id="inactive_admins">0</span></div>
								<div>Inactive</div>
							</div>
                            <div class="col-xs-12">
							<div class="link-url">
								<a href="adminmaster.php" class="clearfix">
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
								<div class="huge"><span id="active_institute">0</span></div>
								<div>Active</div>
							</div>
							<div class="col-xs-6 text-right">
								<div class="huge"><span id="inactive_institute">0</span></div>
								<div>Inactive</div>
							</div>
                            <div class="col-xs-12">
							<div class="link-url">
								<a href="institutemaster.php" class="clearfix">
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
								<div class="huge"><span id="active_document">0</span></div>
								<div>Active</div>
							</div>
							<div class="col-xs-6 text-right">
								<div class="huge"><span id="inactive_document">0</span></div>
								<div>Inactive</div>
							</div>
                            <div class="col-xs-12">
							<div class="link-url">
								<a href="template_master.php" class="clearfix">
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
								<div class="huge"><span id="active_users">0</span></div>
								<div>Active</div>
							</div>
							<div class="col-xs-6 text-right">
								<div class="huge"><span id="inactive_users">0</span></div>
								<div>Inactive</div>
							</div>
                            <div class="col-xs-12">
							<div class="link-url">
								<a href="usermaster.php" class="clearfix">
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
								<div class="huge"><span id="active_certificate">0</span></div>
								<div>Active</div>
							</div>
							<div class="col-xs-6 text-right">
								<div class="huge"><span id="inactive_certificate">0</span></div>
								<div>Inactive</div>
							</div>
                            <div class="col-xs-12">
							<div class="link-url">
								<a href="studentmaster.php" class="clearfix">
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
										<tr>
											<td>Android App</td>
											<td id="varifier_android_active_unique"></td>
											<td id="varifier_android_inactive_unique"></td>
											<td id="institute_android_active_unique"></td>
											<td id="institute_android_inactive_unique"></td>
										</tr>
										<tr>
											<td>IOS App</td>
											<td id="varifier_ios_active_unique"></td>
											<td id="varifier_ios_inactive_unique"></td>
											<td id="institute_ios_active_unique"></td>
											<td id="institute_ios_inactive_unique"></td>
										</tr>
										<tr>
											<td>Web App</td>
											<td id="varifier_webapp_active_unique"></td>
											<td id="varifier_webapp_inactive_unique"></td>
											<td id="institute_webapp_active_unique"></td>
											<td id="institute_webapp_inactive_unique"></td>

										</tr>
										<tr>
											
											<td>Total</td>
											<td id="varifier_total_active"></td>
											<td id="varifier_total_inactive"></td>
											<td id="institute_total_active"></td>
											<td id="institute_total_inactive"></td>

										</tr>
										
									</tbody>
									
								</table>
							</div>
							
                            <div class="col-xs-12">
							<div class="link-url">
									<a href="mis_report.php" class="clearfix">
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
									<tbody >
										@foreach($last_transactions as $key => $transaction)
											<?php $class = "danger"; ?>
											@if($transaction['trans_status'] == 1)

												<?php $class = "success"; ?>
											
											@endif
											<tr class='{{$class}}'>
												<td>{{$transaction['trans_id_ref']}}</td>
												<td class='text-center'>{{$transaction['amount']}}<i class='fa fa-inr' aria-hidden='true'></i></td>
												<td>{{$transaction['username']}}</td>
												<td>{{$transaction['student_name']}}</td>
											</tr>
										@endforeach

									</tbody>
								</table>
							</div>
							
                            <div class="col-xs-12">
							<div class="link-url">
								<a href="transactions.php" class="clearfix">
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
									<tbody>
										@foreach($last_scan as $key => $item)

											@switch($item['device_type'])
												@case("Android")

													<?php $device_type = "fa-android green";  ?>
													@break;
												
												@case("iOS")

													<?php $device_type = "fa-apple";  ?>
													@break;
												@case("WebApp")
													<?php $device_type = "fa-desktop";  ?>
													@break;
												@default:
													<?php $device_type = 'No device';  ?>
											@endswitch

											<?php $date_time = date("d M Y h:i A", strtotime($item['date_time'])); ?>

											<tr>
												<td class='text-center'><img src='../{{$item['path']}}' class='' style='width:50px;width:50px'></td>
												<td class='text-center'>{{$item['scan_by']}}<br><i class='fa fa-fw fa-2x {{$device_type}}'></i><br><label style='font-size: 8px;'>{{$date_time}}</label></td>
											</tr>

										@endforeach
									</tbody>
								</table>
							</div>
							
                            <div class="col-xs-12">
							<div class="link-url">
								<a href="mis_report.php" class="clearfix">
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
@stop
@section('script')
<script type="text/javascript">

		var activeAdmin = '<?= $activeAdmin ?>';
		var inActiveAdmin = '<?= $inActiveAdmin ?>';
		var activeInstitute = '<?= $activeInstitute ?>';
		var inActiveInstitute = '<?= $inActiveInstitute ?>';
		var activeDocuments = '<?= $activeDocuments ?>';
		var inActiveDocuments = '<?= $inActiveDocuments ?>';
		var activeUsers = '<?= $activeUsers ?>';
		var inActiveUsers = '<?= $inActiveUsers ?>';
		var activeCertificates = '<?= $activeCertificates ?>';
		var inActiveCertificates = '<?= $inActiveCertificates ?>';
		// android scanning counts
		var varifier_android_active_total = parseInt('<?= $varifier_android_active_total ?>');
		var varifier_android_active_unique = parseInt('<?= $varifier_android_active_unique ?>');
		var varifier_android_inactive_total = parseInt('<?= $varifier_android_inactive_total ?>');
		var varifier_android_inactive_unique = parseInt('<?= $varifier_android_inactive_unique ?>');
		var institute_android_active_total = parseInt('<?=$institute_android_active_total?>');
		var institute_android_active_unique = parseInt('<?=$institute_android_active_unique?>');
		var institute_android_inactive_total = parseInt('<?=$institute_android_inactive_total?>');
		var institute_android_inactive_unique = parseInt('<?=$institute_android_inactive_unique?>');

		// ios scanning counts
		var varifier_ios_active_total = parseInt('<?= $varifier_ios_active_total ?>');
		var varifier_ios_active_unique = parseInt('<?= $varifier_ios_active_unique ?>');
		var varifier_ios_inactive_total = parseInt('<?= $varifier_ios_inactive_total ?>');
		var varifier_ios_inactive_unique = parseInt('<?= $varifier_ios_inactive_unique ?>');
		var institute_ios_active_total = parseInt('<?=$institute_ios_active_total?>');
		var institute_ios_active_unique = parseInt('<?=$institute_ios_active_unique?>');
		var institute_ios_inactive_total = parseInt('<?=$institute_ios_inactive_total?>');
		var institute_ios_inactive_unique = parseInt('<?=$institute_ios_inactive_unique?>');

		// webapp scanning counts

		var varifier_webapp_active_total = parseInt('<?= $varifier_webapp_active_total ?>');
		var varifier_webapp_active_unique = parseInt('<?= $varifier_webapp_active_unique ?>');
		var varifier_webapp_inactive_total = parseInt('<?= $varifier_webapp_inactive_total ?>');
		var varifier_webapp_inactive_unique = parseInt('<?= $varifier_webapp_inactive_unique ?>');
		var institute_webapp_active_total = parseInt('<?=$institute_webapp_active_total?>');
		var institute_webapp_active_unique = parseInt('<?=$institute_webapp_active_unique?>');
		var institute_webapp_inactive_total = parseInt('<?=$institute_webapp_inactive_total?>');
		var institute_webapp_inactive_unique = parseInt('<?=$institute_webapp_inactive_unique?>');

		// total scanning counts

		var varifier_total_active = varifier_android_active_total + varifier_ios_active_total + varifier_webapp_active_total;

		var varifier_total_active_unique = varifier_android_active_unique + varifier_ios_active_unique + varifier_webapp_active_unique;

		var varifier_total_inactive = varifier_android_inactive_total + varifier_ios_inactive_total + varifier_webapp_inactive_total;

		var varifier_total_inactive_unique = varifier_android_inactive_unique +varifier_ios_inactive_unique + varifier_webapp_inactive_unique;
		
		var institute_total_active = institute_android_active_total+ institute_ios_active_total+ institute_webapp_active_total;

		var institute_total_active_unique = institute_android_active_unique + institute_ios_active_unique + institute_webapp_active_unique;

		var institute_total_inactive = institute_android_inactive_total + institute_ios_inactive_total + institute_webapp_inactive_total;

		var institute_total_inactive_unique =institute_android_inactive_unique + institute_ios_inactive_unique+ institute_webapp_inactive_unique;


		$('#active_admins').animateNumber({ number: activeAdmin },1000);
		$('#inactive_admins').animateNumber({ number: inActiveAdmin },1000);
		$('#active_institute').animateNumber({ number: activeInstitute },1000);
		$('#inactive_institute').animateNumber({ number: inActiveInstitute },1000);
		$('#active_document').animateNumber({ number: activeDocuments },1000);
		$('#inactive_document').animateNumber({ number: inActiveDocuments },1000);
		$('#active_users').animateNumber({ number: activeUsers },1000);
		$('#inactive_users').animateNumber({ number: inActiveUsers },1000);
		$('#active_certificate').animateNumber({ number: activeCertificates },1000);
		$('#inactive_certificate').animateNumber({ number: inActiveCertificates },1000);

		
		// dispay andoid scanning counts
		$('#varifier_android_active_unique').html(varifier_android_active_unique+'('+varifier_android_active_total+')');
		$('#varifier_android_inactive_unique').html(varifier_android_inactive_unique+'('+varifier_android_inactive_total+')');
		$('#institute_android_active_unique').html(institute_android_active_unique+'('+institute_android_active_total+')')
		$('#institute_android_inactive_unique').html(institute_android_inactive_unique+'('+institute_android_inactive_total+')')

		// display ios scanning counts

		$('#varifier_ios_active_unique').html(varifier_ios_active_unique+'('+varifier_ios_active_total+')');
		$('#varifier_ios_inactive_unique').html(varifier_ios_inactive_unique+'('+varifier_ios_inactive_total+')');
		$('#institute_ios_active_unique').html(institute_ios_active_unique+'('+institute_ios_active_total+')')
		$('#institute_ios_inactive_unique').html(institute_ios_inactive_unique+'('+institute_ios_inactive_total+')')

		// display webapp scanning counts

		$('#varifier_webapp_active_unique').html(varifier_webapp_active_unique+'('+varifier_webapp_active_total+')');
		$('#varifier_webapp_inactive_unique').html(varifier_webapp_inactive_unique+'('+varifier_webapp_inactive_total+')');
		$('#institute_webapp_active_unique').html(institute_webapp_active_unique+'('+institute_webapp_active_total+')')
		$('#institute_webapp_inactive_unique').html(institute_webapp_inactive_unique+'('+institute_webapp_inactive_total+')')
		
		// display total scanning counts

		$('#varifier_total_active').html(varifier_total_active_unique+'('+varifier_total_active+')');
		$('#varifier_total_inactive').html(varifier_total_inactive_unique+'('+varifier_total_inactive+')');
		$('#institute_total_active').html(institute_total_active_unique+'('+institute_total_active+')')
		$('#institute_total_inactive').html(institute_total_inactive+'('+institute_total_inactive_unique+')')
		

</script>

@stop