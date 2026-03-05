@extends('webapp.layouts.layout')
@section('content')
<div class="col-md-4 col-md-offset-4 col-sm-12 col-xs-12">
      <div class="panel panel-info">
            <div class="panel-heading">Mpesa</div>
            <div class="panel-body">

                  <form class="row" name="mPesaForm" id="mPesaForm" >
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="student_name" value="{{$student_name}}">
                        <input type="hidden" name="key" value="{{$key}}">
                        <input type="hidden" name="amount" value="{{$amount}}">
                        <input type="hidden" name="pg_id" value="{{$pg_id}}">
                        
                        <div class="col-md-12">
                              <label>Phone Number</label>
                              <input type="text" class="form-control" name="phone_number" value="{{$mobile_number}}">
                              <span style="color: red" id="phone_number_error"></span>
                        </div>
                        <div class="col-md-12" style="margin-top: 10px;">
                              <button type="submit" name="submit" id="submitForm" class="btn btn-primary"><i class="fa fa-lock"></i> Pay {{number_format($amount,2)}} KES</button>
                        </div>


                  </form>

            </div>
      </div>
</div>

@stop
@section('script')


<script type="text/javascript">
      $("#submitForm").click(function (e) {
            $.ajaxSetup({headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}});
   	      e.preventDefault();
            var my_url="{{ URL::route('payment.mpesaCall') }}";
            //var token="{{ csrf_token() }}";
            var method_type="POST";
            var formData = new FormData($('#mPesaForm')[0]);
            $.ajax({
                  type:method_type,
                  url:my_url,
                  data: formData,
                  contentType: false,
                  processData: false,
                  dataType: 'json',
                  success:function(data){
                        if(data.status==true)
                        {     
                              if(data.data.ResponseCode == 0 || data.data.ResponseCode == '0') {
                                    toastr.info(data.data.ResponseDescription);
                                    //var redirectUrl = route('payment.mPesaTransactionStatus');
                                    window.location.href = "{{ URL::route('payment.mPesaTransactionStatus') }}";
                              } else if(data.data.errorCode =='400.002.02') {
                                    $('#phone_number_error').text('Invalid PhoneNumber');
                              } else {
                                    
                                    toastr.info(data.data.errorMessage);   
                              }
                        }
                  },
                  error:function(resobj){
                        // $.each(resobj.responseJSON.errors,function(k,v){
                        //       $("#"+k+'_error').text(v); 
                        // });
                        // $(".loadsave").removeClass('fa fa-spinner fa-spin');
                  }
            });
      });
</script>
@stop