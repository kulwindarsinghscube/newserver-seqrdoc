@extends('admin.layout.layout')

@section('style')
@stop

@section('content')
@stop

@section('script')
<script type="text/javascript">
	$(document).ready(function(){
		$.ajax({
			type:'GET',
			url:'<?=route('demo1')?>',
			dataType:'html',
			success:function(resp){
					$(document).find('body').append(resp)

			}
		})
	})

	console.log($(document).find('body').find('button').last())
	var count = 0;
	$(document).find('body').find('button').last().click(function(){
		console.log(111)
		count = count + 1;
		var list = $(document).find('li');
		$.each(list,function(k,v){
			if(count %2 != 0){
				if(k%2 == 0){
					$(this).text(count)
				}
			}
			else{
				if(k%2 != 0){
					$(this).text(count);

				}
			}
		})
	})
</script>
@stop