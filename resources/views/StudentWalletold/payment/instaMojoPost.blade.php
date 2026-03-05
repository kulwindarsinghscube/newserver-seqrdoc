@extends('webapp.layouts.layout')
@section('content')
<form action="<?= route('instamojo.paymentGateway') ?>" method="post" name="instaMojoForm" >
      <input type="hidden" name="_token" value="{{ csrf_token() }}">
      <input type="hidden" name="student_name" value="{{$student_name}}">
      <input type="hidden" name="key" value="{{$key}}">
      <input type="hidden" name="amount" value="{{$amount}}">
</form>
@stop
@section('script')

<script type="text/javascript">
      var instaMojoForm = document.forms.instaMojoForm;
      instaMojoForm.submit();  
      // $(document).payuForm.submit();
</script>
@stop
