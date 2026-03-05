@extends('verify.layout.layout')
@section('content')


@if($inputInfo['response_code'] == 0 || $inputInfo['response_code'] == '0')
<?php $status="success"; ?>
	<div class="row" style="background-color: #fff;padding: 20px;">
        <div class="col-xs-12 col-md-6 col-md-offset-3 text-center">
          <h2>Transaction Successful</h2>
          <h5>Your transaction has completed successfully and details are provided below.</h5>
            <table class="table table-bordered table-hover">
              <tr>
                <th>Transaction ID</th>
                <td class="text-left">{{$inputInfo['order_id']}}</td>
              </tr>
              <tr>
                <th>Gateway ID</th>
                <td class="text-left">{{$inputInfo['transaction_id']}}</td>
              </tr>
              <tr>
                <th>Date</th>
                <td class="text-left">{{date('d-m-Y')}}</td>
              </tr>
              <tr>
                <th>Mode</th>
                <td class="text-left">{{$inputInfo['payment_mode']}}</td>
              </tr>
              <tr>
                <th>Amount</th>
                <td class="text-left">{{$inputInfo['amount']}} <i class="fa fa-rupee"></i></td>
              </tr>
            </table>
             <br>
            <div style="text-align:center;">
                <p>Click the below close button to return to previous page.</p>
                <p><button onclick="doSomthing('<?php echo $status;?>');" class="button button2">Close</button></p>
            </div>
        </div>
    </div>
    <div id="pdfDiv" style="text-align: center;"></div>

@else
<?php $status="failed"; ?>
	<div class="row" style="background-color: #fff;padding: 20px;">
		<div class="col-xs-12 col-md-6 col-md-offset-3 text-center">
			<h2>Transaction Failed</h2>
			<h5>Your transaction has failed and details are provided below.</h5>
      <table class='table table-bordered table-hover'>
        <tr>
         
          <th>Transaction ID</th>
          <td class="text-left">{{$inputInfo['order_id']}}</td>
        </tr>
        <tr>
          <th>Gateway ID</th>
          <td class="text-left">{{$inputInfo['transaction_id']}}</td>
        </tr>
        <tr>
          <th>Date</th>
          <td class="text-left">{{date('d-m-Y')}}</td>
        </tr>
        <tr>
          <th>Mode</th>
          <td class="text-left">{{$inputInfo['payment_mode']}}</td>
        </tr>
        <tr>
          <th>Amount</th>
          <td class="text-left">{{$inputInfo['amount']}}<i class="fa fa-rupee"></i></td>
        </tr>
      </table>
      <br>
      <div style="text-align:center;">
          <p>Click the below close button to return to previous page.</p>
          <p><button onclick="doSomthing('<?php echo $status;?>');" class="button button2">Close</button></p>
      </div>
		</div>
  </div>
@endif




@stop
@section('style')
@stop
@section('script')

<script>
  function doSomthing(status) {
    window.ReactNativeWebView.postMessage(status);
  }
  function update(){
   var ajaxURL = '{{URL::route("request.verification.add.transaction")}}';
    var token = '{{csrf_token()}}';
    <?php
      $domain = \Request::getHost();
      $subdomain = explode('.', $domain); 
    ?>
    $.post(ajaxURL,{
      'action':'create',
      '_token':token,
      'trans_id_ref':'<?php echo $inputInfo['order_id'] ?>',
      'trans_id_gateway':'<?php echo $inputInfo['transaction_id'] ?>',
      'payment_mode':'<?php echo $inputInfo['payment_mode'] ?>',
      'amount':'<?php echo $inputInfo['amount'] ?>',
      'additional':'0',
      'user_id':'<?php echo $user_id; ?>',
      'student_key':'<?php echo $request_number; ?>',
      'trans_status': '<?php echo $inputInfo["response_code"] == "0" ? 1 : 0;  ?>'
    },function(){
      if(data.status==true){
            if(data.showPdf==true){
            var length=data.dataPdf.length;
              for (var i=0; i<length; i++){
                $('#pdfDiv').append('<iframe src="<?php  Config::get('constant.local_base_path').$subdomain[0]; ?>/backend/pdf_file/'+data.dataPdf[i]['certificate_filename']+'?page=hsn#toolbar=0" width="810" height="780"></iframe><hr>');
              }
          }
      }
    },'JSON');
  }
$(document).ready(function(){
  update();
});
</script>
@stop