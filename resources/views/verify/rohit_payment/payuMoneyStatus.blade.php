@extends('webapp.layouts.layout')
@section('content')


@if($inputInfo->status == 'success')
<div class="row">
  <div class="col-xs-12 col-md-6 col-md-offset-3 text-center">
    <h2>Transaction Successful</h2>
    <h5>Your transaction has completed successfully and details are provided below.</h5>
    <table class='table table-bordered table-hover'>
      <tr>
        <th>Transaction ID</th>
        <td class="text-left">{{$inputInfo->txnid}}</td>
      </tr>
      <tr>
        <th>Gateway ID</th>
        <td class="text-left">{{$inputInfo->mihpayid}}</td>
      </tr>
      <tr>
        <th>Date</th>
        <td class="text-left">{{date('d-m-Y')}}</td>
      </tr>
      <tr>
        <th>Mode</th>
        <td class="text-left">{{$inputInfo->mode}}</td>
      </tr>
      <tr>
        <th>Card No.</th>
        <td class="text-left">{{$inputInfo->cardnum}}</td>
      </tr>
      <tr>
        <th>Email</th>
        <td class="text-left">{{$inputInfo->email}}</td>
      </tr>
      <tr>
        <th>Phone</th>
        <td class="text-left">{{$inputInfo->phone}}</td>
      </tr>
      <tr>
        <th>Amount</th>
        <td class="text-left">{{$inputInfo->amount}}<i class="fa fa-rupee"></i></td>
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
    var txnid = "{{$inputInfo->txnid}}";
    var mihpayid = "{{$inputInfo->mihpayid}}";
    var mode = "{{$inputInfo->mode}}";
    var amount = "{{$inputInfo->amount}}";
    var user_id = "{{$user_id}}";
    var productinfo = "{{$inputInfo->productinfo}}";
    
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
@stop
@endif  
@stop