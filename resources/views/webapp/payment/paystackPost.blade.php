@extends('webapp.layouts.layout')
@section('content')
<form action="<?= route('payment.gateway.paystack') ?>" method="post" name="paystackForm" >
      <input type="hidden" name="_token" value="{{ csrf_token() }}">
      <input type="hidden" name="student_name" value="{{$student_name}}">
      <input type="hidden" name="key" value="{{$key}}">
      <input type="hidden" name="amount" value="{{$amount}}">
</form>
@stop
@section('script')

<script type="text/javascript">
      var paystackForm = document.forms.paystackForm;
      paystackForm.submit();  
      // $(document).payuForm.submit();
</script>
@stop