@extends('verify.layout.layout')
@section('content')

<div class="row">
  <div class="col-xs-12 col-md-6 col-md-offset-3 text-center">
    <h2>Request Successful</h2>
    <p style="text-align: justify;">
      <?php

echo "Request for educational details verification of <b>" . $data['firstname'] . "</b> is received to us. <br><br>

Kindly complete the online payment from Pending Payment tab after which the Institute will convey you the verification details within 24 hours.<br><br>

Your request number is <b>" . $data['request_number'] . ".</b>. Remember this number for our future communication references.<br><br>";

?>

    </p>

    <p class="text-center"><a href="/verify/verification-status" class="btn btn-theme" style="color:#fff"> Check Request Status</a></p>
  </div>
</div>

@stop
