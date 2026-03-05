@extends('verify.layout.layout')
@section('content')


@if($inputInfo['STATUS'] == 'TXN_SUCCESS')
<?php $status="success"; ?>
	<div class="row">
       	<div class="col-xs-12 col-md-6 col-md-offset-3 text-center">
       	<h2>Transaction Successful</h2>
          	<p style="text-align: justify;">Request for educational details verification of <b>{{$student_name}}</b> is received to us. Institute will convey you the verification details within 24 hours.<br><br>

            Your request number is <b>{{$request_number}}</b> Remember this number for our future communication references.
          </p>
      	</div>
   </div> 
@else
<?php $status="failed"; ?>
	<div class="row">
       <div class="col-xs-12 col-md-6 col-md-offset-3 text-center">
       <h2>Transaction Failed</h2>
          <p style="text-align: justify;">Request for educational details verification of <b>{{$student_name}}</b> is received to us. Institute will convey you the verification details within 24 hours.<br><br>

            Your request number is <b>{{$request_number}}</b> Remember this number for our future communication references.
            <span style="color:red;">Your transaction has been failed. You can do the payment by switching to <b>“Pending Payment”</b> tab.</span><br>
            </p>
            
        </div>
    </div>
@endif
      <br>
    <div class="row">
        <div style="text-align:center;">
            <p>Click the below close button to return to previous page."</p>
              <p><button onclick="doSomthing('<?php echo $status;?>');" class="button button2">Close</button></p>
        </div>
    </div>

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
      'trans_id_ref':'<?php echo $inputInfo['ORDERID'] ?>',
      'trans_id_gateway':'<?php echo $inputInfo['TXNID'] ?>',
      'payment_mode':'<?php echo $inputInfo['PAYMENTMODE'] ?>',
      'amount':'<?php echo $inputInfo['TXNAMOUNT'] ?>',
      'additional':'0',
      'user_id':'<?php echo $user_id; ?>',
      'student_key':'<?php echo $request_number; ?>',
      'trans_status': '<?php echo $inputInfo["STATUS"] == "TXN_SUCCESS" ? 1 : 0;  ?>'
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