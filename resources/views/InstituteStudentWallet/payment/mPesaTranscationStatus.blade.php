@extends('webapp.layouts.layout')
@section('content')
<style>
/* .payment-loader {
  width : 150px;
  position: absolute;
  top: 50%;
  left : 50%;
  -webkit-transform: translateY(-50%) translateX(-50%);
  -moz-transform: translateY(-50%) translateX(-50%);
  -o-transform: translateY(-50%) translateX(-50%);
  transform: translateY(-50%) translateX(-50%);
} */
body {
      background-color: rgba(0, 0, 0, 0.3);  
}
.card {
      padding: 25px;
      padding-bottom: 80px;
}

.payment-loader .binding {
  content : '';
  width : 60px;
  height : 4px;
  border : 2px solid #00c4bd;
  margin : 0 auto;
}

.payment-loader .pad {
  width : 70px;
  height : 48px;
  border-radius : 8px;
  border : 2px solid #00c4bd;
  padding : 6px;
  margin : 0 auto;
}

.payment-loader .chip {
  width : 12px;
  height: 8px;
  background: #00c4bd;
  border-radius: 3px;
  margin-top: 4px;
  margin-left: 3px;
}

.payment-loader .line {
  width : 52px;
  margin-top : 6px;
  margin-left : 3px;
  height : 4px;
  background: #00c4bd;
  border-radius: 100px;
  opacity : 0;
  -webkit-animation : writeline 3s infinite ease-in;
  -moz-animation : writeline 3s infinite ease-in;
  -o-animation : writeline 3s infinite ease-in;
  animation : writeline 3s infinite ease-in;
}

.payment-loader .line2 {
  width : 32px;
  margin-top : 6px;
  margin-left : 3px;
  height : 4px;
  background: #00c4bd;
  border-radius: 100px;
  opacity : 0;
  -webkit-animation : writeline2 3s infinite ease-in;
  -moz-animation : writeline2 3s infinite ease-in;
  -o-animation : writeline2 3s infinite ease-in;
  animation : writeline2 3s infinite ease-in;
}

.payment-loader .line:first-child {
  margin-top : 0;
}

.payment-loader .line.line1 {
  -webkit-animation-delay: 0s;
  -moz-animation-delay: 0s;
  -o-animation-delay: 0s;
  animation-delay: 0s;
}

.payment-loader .line.line2 {
  -webkit-animation-delay: 0.5s;
  -moz-animation-delay: 0.5s;
  -o-animation-delay: 0.5s;
  animation-delay: 0.5s;
}

.payment-loader .loader-text {
      text-align: center;
    margin-top: 0px;
    font-size: 17px;
    line-height: 16px;
    color: #5f6571;
    font-weight: bold;
    margin-bottom: 22px;
}


@keyframes writeline {
  0% { width : 0px; opacity: 0; }
  33% { width : 52px; opacity : 1; }
  70% { opacity : 1; }
  100% {opacity : 0; }
}

@keyframes writeline2 {
  0% { width : 0px; opacity: 0; }
  33% { width : 32px; opacity : 1; }
  70% { opacity : 1; }
  100% {opacity : 0; }
}
/* counter css */
.countdown-label {
  font: thin 15px Arial, sans-serif;
	color: #65584c;
	text-align: center;
	text-transform: uppercase;
	display: inline-block;
      letter-spacing: 2px;
      /* margin-top: 9px */
}
#countdown{
      /* box-shadow: 0 1px 2px 0 rgba(1, 1, 1, 0.4); */
      /* width: 240px; */
      /* height: 96px; */
      text-align: center;
      /* background: #f1f1f1; */
      /* border-radius: 5px; */
      margin: auto;
      margin-top: 0px;
}



#countdown #tiles{
      color: #fff;
      position: relative;
      z-index: 1;
      /* text-shadow: 1px 1px 0px #ccc; */
      display: inline-block;
      text-align: center;
      padding: 0 7px;
      /* border-radius: 5px 5px 0 0; */
      font-size: 40px;
      font-weight: thin;
    
}

.color-full {
  background: #53bb74;
}
.color-half {
  background: #ebc85d;
}
.color-empty {
  background: #e5554e;
}

#countdown #tiles > span{
	width: 70px;
	max-width: 70px;

	padding: 18px 0;
	position: relative;
}





#countdown .labels{
	width: 100%;
	height: 25px;
	text-align: center;
	position: absolute;
	bottom: 8px;
}

#countdown .labels li{
	width: 102px;
	font: bold 15px 'Droid Sans', Arial, sans-serif;
	color: #f47321;
	text-shadow: 1px 1px 0px #000;
	text-align: center;
	text-transform: uppercase;
	display: inline-block;
}
</style>

<div class="col-md-4 col-md-offset-4 col-sm-12 col-xs-12">
      
      <div class="card">

            <div class="text-center" >
                  <img style="width: 120px;" src="{{ \Config::get("constant.payment_image").'/mpesa_images.png' }}">
            </div>

            <div class="payment-loader">
                  <div class="loader-text">
                        Please wait untill we get update on payment  
                  </div>

                  <div class="pad">
                        <div class="chip"></div>
                        <div class="line line1"></div>
                        <div class="line line2"></div>
                  </div>
                  
            </div>

            
            <p class="text-center" style="color: #65584c;line-height:15px;margin-top:15px;margin-bottom: 40px;font-weight:bold;">Open Mpesa App to<br> Complete the payment of 1 KES</p>


            <input type="hidden" id="set-time" value="10"/>
            <p class="text-center" style="margin-bottom: 0px;color:#5f6571;font-weight:bold;">PAGE EXPIRES IN</p>
            <div id="countdown">
                  <div id='tiles' class="color-full"></div>
                  {{-- <div id ="left" class="countdown-label">Time Remaining</div> --}}
            </div>
      </div>

</div>

@stop
@section('script')
<script>
      var minutes = $( '#set-time' ).val();

      var target_date = new Date().getTime() + ((minutes * 60 ) * 1000); // set the countdown date
      var time_limit = ((minutes * 60 ) * 1000);

      //set actual timer
      setTimeout(function() {
            document.getElementById("left").innerHTML = "Timer Stopped";
      }, time_limit );

      var days, hours, minutes, seconds; // variables for time units

      var countdown = document.getElementById("tiles"); // get tag element

      getCountdown();

      setInterval(function () { getCountdown(); }, 1000);

      function getCountdown(){

            // find the amount of "seconds" between now and target
            var current_date = new Date().getTime();
            var seconds_left = (target_date - current_date) / 1000;
            
            if(seconds_left <= 120) {
                  console.log('last 2 miinutes');
                  $('#tiles').css({"background-color":"red"});
            }
            if ( seconds_left >= 0 ) {
                  
                  if ( (seconds_left * 1000 ) < ( time_limit / 2 ) )  {
                        $( '#tiles' ).removeClass('color-full');
                        $( '#tiles' ).addClass('color-half');

                  } 
                  if ( (seconds_left * 1000 ) < ( time_limit / 4 ) )  {
                        $( '#tiles' ).removeClass('color-half');
                        $( '#tiles' ).addClass('color-empty');
                  }
            
                  days = pad( parseInt(seconds_left / 86400) );
                  seconds_left = seconds_left % 86400;
                        
                  hours = pad( parseInt(seconds_left / 3600) );
                  seconds_left = seconds_left % 3600;
                        
                  minutes = pad( parseInt(seconds_left / 60) );
                  seconds = pad( parseInt( seconds_left % 60 ) );

                  // format countdown string + set tag value
                  countdown.innerHTML = "<span>" + minutes + ":</span><span>" + seconds + "</span>"; 
            }
      }

      function pad(n) {
            return (n < 10 ? '0' : '') + n;
      }

</script>


<script>


function AddMinutesToDate(date, minutes) {
     return new Date(date.getTime() + minutes*60000);
}
function DateFormat(date){
  var days = date.getDate();
  var year = date.getFullYear();
  var month = (date.getMonth()+1);
  var hours = date.getHours();
  var minutes = date.getMinutes();
  minutes = minutes < 10 ? '0' + minutes : minutes;
  var strTime = days + '/' + month + '/' + year + '/ '+hours + ':' + minutes;
  return strTime;
}

var now = new Date();
// console.log(DateFormat(now));
var next = AddMinutesToDate(now,10);
// console.log(DateFormat(next));
var interval = 2000;


function timeFunc() {
      console.log('jquery call');
      var student_key = '<?= $student_key?>';
      var CheckoutRequestID = '<?= $CheckoutRequestID?>';
      $.ajaxSetup({headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}});
      var my_url="{{ URL::route('payment.mPesaTransactionProcess') }}";
      //var token="{{ csrf_token() }}";
      var method_type="POST";

      fd = new FormData();
      fd.append('_token', '{{csrf_token()}}');
      fd.append( 'student_key', student_key );
      fd.append( 'CheckoutRequestID', CheckoutRequestID );

      $.ajax({
            type:method_type,
            url:my_url,
            data: fd,
            contentType: false,
            processData: false,
            dataType: 'json',
            success:function(data){
                  if(data.status==true)
                  {     
                        console.log(data);
                        if(data.data.ResultCode == 0 || data.data.ResultCode == '0') {
                              toastr.info(data.data.ResponseDescription);
                              //var redirectUrl = route('payment.payment.mPesaResponse');');
                              window.location.href = "{{ URL::route('payment.mPesaResponse') }}";
                        } else if(data.data.ResultCode == 1032 || data.data.ResultCode == '1032') {
                              toastr.info(data.data.ResponseDescription);
                              //var redirectUrl = route('payment.payment.mPesaResponse');');
                              window.location.href = "{{ URL::route('payment.mPesaResponse') }}";
                        } else if(data.data.errorCode == '500.001.1001') {
                              //toastr.info(data.data.ResponseDescription);
                              //var redirectUrl = route('payment.payment.mPesaResponse');');
                              //window.location.href = "{{ URL::route('payment.mPesaResponse') }}";
                        } else {
                              //toastr.info(data.data.errorMessage);   

                              
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
}
setInterval(timeFunc, interval);



</script>

<script>

var TENMINUTES = 10 * 60 * 1000;


function finalCall() {
      console.log('final call');
      var student_key = '<?= $student_key?>';
      var CheckoutRequestID = '<?= $CheckoutRequestID?>';
      $.ajaxSetup({headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}});
      var my_url="{{ URL::route('payment.mPesaTransactionProcess') }}";
      //var token="{{ csrf_token() }}";
      var method_type="POST";

      fd = new FormData();
      fd.append('_token', '{{csrf_token()}}');
      fd.append( 'student_key', student_key );
      fd.append( 'CheckoutRequestID', CheckoutRequestID );

      $.ajax({
            type:method_type,
            url:my_url,
            data: fd,
            contentType: false,
            processData: false,
            dataType: 'json',
            success:function(data){
                  if(data.status==true)
                  {     
                        console.log(data);
                        if(data.data.ResultCode == 0 || data.data.ResultCode == '0') {
                              toastr.info(data.data.ResponseDescription);
                              //var redirectUrl = route('payment.payment.mPesaResponse');');
                              window.location.href = "{{ URL::route('payment.mPesaResponse') }}";
                        } else if(data.data.ResultCode == 1032 || data.data.ResultCode == '1032') {
                              toastr.info(data.data.ResponseDescription);
                              //var redirectUrl = route('payment.payment.mPesaResponse');');
                              window.location.href = "{{ URL::route('payment.mPesaResponse') }}";
                        } else if(data.data.errorCode == '500.001.1001') {
                              //toastr.info(data.data.ResponseDescription);
                              //var redirectUrl = route('payment.payment.mPesaResponse');');
                              window.location.href = "{{ URL::route('payment.mPesaResponse') }}";
                        } else {
                              window.location.href = "{{ URL::route('payment.mPesaResponse') }}";
                              //toastr.info(data.data.errorMessage);   

                              
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
}

setInterval(finalCall, TENMINUTES);

</script>
@stop