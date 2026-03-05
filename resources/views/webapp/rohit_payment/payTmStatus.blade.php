@extends('webapp.layouts.layout')
@section('content')


@if($inputInfo['STATUS'] == 'TXN_SUCCESS')
<div class="row">
  <div class="col-xs-12 col-md-6 col-md-offset-3 text-center">
    <h2>Transaction Successful</h2>
    <h5>Your transaction has completed successfully and details are provided below.</h5>
    <table class='table table-bordered table-hover'>
      <tr>
        <th>Transaction ID</th>
        <td class="text-left">{{$inputInfo['ORDERID']}}</td>
      </tr>
      <tr>
        <th>Gateway ID</th>
        <td class="text-left">{{$inputInfo['TXNID']}}</td>
      </tr>
      <tr>
        <th>Date</th>
        <td class="text-left">{{date('d-m-Y')}}</td>
      </tr>
      <tr>
        <th>Mode</th>
        <td class="text-left">{{$inputInfo['PAYMENTMODE']}}</td>
      </tr>
      <tr>
        <th>Amount</th>
        <td class="text-left">{{$inputInfo['TXNAMOUNT']}}<i class="fa fa-rupee"></i></td>
      </tr>
    </table>
    <p class="text-center">You can close this tab and return to previous page to try again.</p>
    <p class="text-center"><a href="#" onclick="pdfView()" class="btn btn-theme" style="color:#fff"> Close</a></p>
  </div>
</div>	

@section('script')
<script>
  function pdfView(){
    var session_key = '<?= $session_key?>';
    sessionStorage.setItem('qrCodeKey',session_key)
    var ajaxURL = "{{ URL::route('webapp.dashboard') }}";
    // ajaxURL = ajaxURL.replace(':id',session_key)
    window.location.href = ajaxURL;
  }


  function update(){
    
    var ajaxURL = "<?= route('rohitPayment.transaction')?>";
    var token = "{{csrf_token()}}";
    var txnid = "{{$inputInfo['ORDERID']}}";
    var mihpayid = "{{$inputInfo['TXNID']}}";
    var mode = "{{$inputInfo['PAYMENTMODE']}}";
    var amount = "{{$inputInfo['TXNAMOUNT']}}";
    var user_id = "{{$user_id}}";
    var productinfo = "{{$session_key}}";
    
    $.ajax({
        url : ajaxURL,
        dataType: "json",
        type: "POST",
        data:{
          
          '_token':token,
          'trans_id_ref' : txnid,
          'trans_id_gateway' : mihpayid,
          'payment_mode' : mode,
          'amount' : amount,
          'additional' : '0',
          'user_id' : user_id,
          'student_key' : productinfo,
          'trans_status' : '1'
        },
        success: function (data) {        
         
        }
      });
  }
$(document).ready(function(){
  update();
});
</script>
@endsection
@else
  
  <div class="row">
  <div class="col-xs-12 col-md-6 col-md-offset-3 text-center">
    <h2>Transaction Failed</h2>
    <h5>Your transaction has failed and details are provided below.</h5>
    <table class='table table-bordered table-hover'>
      <tr>
        <th>Transaction ID</th>
        <td class="text-left">{{$inputInfo['ORDERID']}}</td>
      </tr>
      <tr>
        <th>Gateway ID</th>
        <td class="text-left">{{$inputInfo['TXNID']}}</td>
      </tr>
      <tr>
        <th>Date</th>
        <td class="text-left">{{date('d-m-Y')}}</td>
      </tr>
      <tr>
        <th>Mode</th>
        <td class="text-left">{{$inputInfo['PAYMENTMODE']}}</td>
      </tr>
      <tr>
        <th>Amount</th>
        <td class="text-left">{{$inputInfo['TXNAMOUNT']}}<i class="fa fa-rupee"></i></td>
      </tr>
    </table>
    <p class="text-center">You can close this tab and return to previous page to try again.</p>
    <p class="text-center"><a href="#" onclick="window.close()" class="btn btn-theme" style="color:#fff"> Close</a></p>
  </div>
</div>  

@section('script')
<script>
  function update(){
    
    var ajaxURL = "<?= route('payubiz.transaction')?>";
    var token = "{{csrf_token()}}";
    var txnid = "{{$inputInfo['ORDERID']}}";
    var mihpayid = "{{$inputInfo['TXNID']}}";
    var mode = "{{$inputInfo['PAYMENTMODE']}}";
    var amount = "{{$inputInfo['TXNAMOUNT']}}";
    var user_id = "{{$user_id}}";
    var productinfo = "{{$session_key}}";
    
    $.ajax({
        url : ajaxURL,
        dataType: "json",
        type: "POST",
        data:{
          
          '_token':token,
          'trans_id_ref' : txnid,
          'trans_id_gateway' : mihpayid,
          'payment_mode' : mode,
          'amount' : amount,
          'additional' : '0',
          'user_id' : user_id,
          'student_key' : productinfo,
          'trans_status' : '0'
        },
        success: function (data) {        
         
        }
      });
  }
$(document).ready(function(){
  update();
});
</script>
@endsection
@endif  

@endsection