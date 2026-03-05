@extends('admin.layout.layout')
@section('style')
<style type="text/css">
.switch {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 34px;
  margin-left: 20px;
}

.switch input { 
  opacity: 0;
  width: 0;
  height: 0;
}
input:checked + .slider {
  background-color: #2196F3;
}

input:focus + .slider {
  box-shadow: 0 0 1px #2196F3;
}
.slider.round {
  border-radius: 34px;
}

.slider.round:before {
  border-radius: 50%;
}
input:checked + .slider {
  background-color: #2196F3;
}

input:focus + .slider {
  box-shadow: 0 0 1px #2196F3;
}

input:checked + .slider:before {
  -webkit-transform: translateX(26px);
  -ms-transform: translateX(26px);
  transform: translateX(26px);
}
.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  -webkit-transition: .4s;
  transition: .4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 26px;
  width: 26px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  -webkit-transition: .4s;
  transition: .4s;
}
 /*range css*/
$range-width: 100% !default;

.range-slider {
  width: $range-width;
}
.range-slider__range {
  -webkit-appearance: none;
  width: calc(100% - (#{$range-label-width + 13px}));
  height: $range-track-height;
  border-radius: 5px;
  background: $range-track-color;
  outline: none;
  padding: 0;
  margin: 0;

  &::-webkit-slider-thumb {
    appearance: none;
    width: $range-handle-size;
    height: $range-handle-size;
    border-radius: 50%;
    background: $range-handle-color;
    cursor: pointer;
    transition: background .15s ease-in-out;

    &:hover {
      background: $range-handle-color-hover;
    }
  }

  &:active::-webkit-slider-thumb {
    background: $range-handle-color-hover;
  }

  &::-moz-range-thumb {
    width: $range-handle-size;
    height: $range-handle-size;
    border: 0;
    border-radius: 50%;
    background: $range-handle-color;
    cursor: pointer;
    transition: background .15s ease-in-out;

    &:hover {
      background: $range-handle-color-hover;
    }
  }

  &:active::-moz-range-thumb {
    background: $range-handle-color-hover;
  }

  &:focus {
    
    &::-webkit-slider-thumb {
      box-shadow: 0 0 0 3px $shade-0,
                  0 0 0 6px $teal;
    }
  }
}
.range-slider__value {
  display: inline-block;
  position: relative;
  width: $range-label-width;
  color: $shade-0;
  line-height: 20px;
  text-align: center;
  border-radius: 3px;
  background: $range-label-color;
  padding: 5px 10px;
  margin-left: 8px;

  &:after {
    position: absolute;
    top: 8px;
    left: -7px;
    width: 0;
    height: 0;
    border-top: 7px solid transparent;
    border-right: 7px solid $range-label-color;
    border-bottom: 7px solid transparent;
    content: '';
  }
}
</style>
@stop
@section('content')
 @php
  	
  	function tz_list() {
	  $zones_array = array();
	  $timestamp = time();
	  foreach(timezone_identifiers_list() as $key => $zone) {
	    date_default_timezone_set($zone);
	    $zones_array[$key]['zone'] = $zone;
	    $zones_array[$key]['diff_from_GMT'] = 'UTC/GMT ' . date('P', $timestamp);
	  }
	  return $zones_array;
	}

 @endphp

<div class="container">
	<div class="container-fluid">
		<div class="row">
			<div class="col-lg-12">
				<h1 class="page-header"><i class="fa fa-file-o"></i>Settings
				<ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;">{{ Breadcrumbs::render('setting') }}</ol>
        <i class="fa fa-info-circle iconModalCss" title="User Manual" id="settingMasterClick"></i>
				</h1>
			</div>
		</div>

		<div class="clearfix">  </div>	
		<form method="post" id="printing_template"  autocomplete="off">
			<input type="hidden" name="func" id="func" value="updatePrinterDetails"> 
			<input type="hidden" name="id" value="{{ $systemConfig['id'] }}"> 
			
			<div class="panel panel-primary" >
			<!-- <div class="panel panel-primary" style="margin: 0 auto; width: 800px;"> -->
				<div class="panel-heading"><i class="fa fa-file-o"></i> Settings </div>
				<div class="panel-body">
					<div id="response"></div>
					<div class="form-group">
						<label for="template_name">Printer Name</label>
						<input type="text" class="form-control" id="printer_name" name="printer_name" value="{{ $systemConfig['printer_name'] }}">
						 <span id="printer_name_error" class="help-inline text-danger"></span>
					</div>
					
					<div class="form-group">
						<label for="bg_template_id">Time Zone</label>
						<select name="timezone" id="time_zone" class="form-control" >

						  <?php foreach(tz_list() as $t) { ?>
						      <option value="<?php print $t['zone'] ?>">
						        <?php print $t['diff_from_GMT'] . ' - ' . $t['zone'] ?>
						      </option>
						    <?php } ?>

						</select>
					</div>
					<div class="form-group">
						<label for="bg_template_id">Print Color</label>
						<select name="print_color" id="print_color" class="form-control">
						      <option value="RGB">RGB</option>
						      <option value="CMYK" >CMYK</option>
						      <!-- <option value="CMYK">CMYK</option> -->
						</select>
					</div>
					<div class="form-group">
						<label for="auto_logout">Auto Logout</label>
						<input type="text" class="form-control allow_number" id="auto_logout" name="auto_logout" placeholder="Auto Logout(In minutes)" value="<?php
						if($systemConfig['auto_logout']=='') echo 15; else { echo $systemConfig['auto_logout']; } ?>">
					</div>
					<div class="form-group">
						<label for="SMTP">SMTP</label>
						<input type="text" class="form-control" id="smtp" name="smtp" placeholder="Please enter smtp" value="{{ $systemConfig['smtp'] }}">
						<span id="smtp_error" class="help-inline text-danger"></span>
					</div>
					<div class="form-group">
						<label for="Port">Port</label>
						<input type="text" class="form-control allow_number" id="port" name="port" placeholder="Please enter port" value="{{ $systemConfig['port'] }}" maxlength="6">
						<span id="port_error" class="help-inline text-danger"></span>
					</div>
					<div class="form-group">
						<label for="Sender Email Id">Sender Email Id</label>
						<input type="text" class="form-control" id="sender_email" name="sender_email" placeholder="Please enter sender email" value="{{$systemConfig['sender_email']}}">
						 <span id="sender_email_error" class="help-inline text-danger"></span>
					</div>
					<div class="form-group">
						<label for="text">Password</label>
						<input type="password" class="form-control" id="password" name="password" placeholder="Please enter password" value="{{ $systemConfig['password'] }}">
						<span id="password_error" class="help-inline text-danger"></span>
					</div>
					<div class="form-group">
						<label for="sandboxing">Sandboxing Environment</label>
						<label class="switch">  
							<input type="checkbox" class="form-control" id="sandboxing" name="sandboxing" value="<?php 
                                if(isset($systemConfig["sandboxing"]))
                                {
                                   echo $systemConfig["sandboxing"];

                                 }else{
                                   echo "0";
                                }?>"
                                <?php
                                    if(isset($systemConfig["sandboxing"])){
                                       if($systemConfig["sandboxing"] == 0){
                                       ?>
                                        unchecked
                                        <?php      
                                          }else{
                                        ?>
                                        checked
                                        <?php      
                                          }
                                        }else{
                                       ?>
                                       uncheched
                                       <?php
                                    }
                                ?>  
                            >
							<span class="slider round"></span>
						</label>
					</div>
					<input type="hidden" id="sandboxing_value" name="sandboxing" value="<?php 
	                  if(isset($systemConfig["sandboxing"]))
	                  {
	                    echo $systemConfig["sandboxing"];

	                  }else{
	                    echo "0";
	                  }?>">
          <div class="form-group">
            <label for="varification_sandboxing">Varification sandboxing</label>
            <label class="switch">  
              <input type="checkbox" class="form-control" id="varification_sandboxing" name="varification_sandboxing" value="<?php 
                                if(isset($systemConfig["varification_sandboxing"]))
                                {
                                   echo $systemConfig["varification_sandboxing"];

                                 }else{
                                   echo "0";
                                }?>"
                                <?php
                                    if(isset($systemConfig["varification_sandboxing"])){
                                       if($systemConfig["varification_sandboxing"] == 0){
                                       ?>
                                        unchecked
                                        <?php      
                                          }else{
                                        ?>
                                        checked
                                        <?php      
                                          }
                                        }else{
                                       ?>
                                       uncheched
                                       <?php
                                    }
                                ?>  
                            >
              <span class="slider round"></span>
            </label>
          </div>
          <input type="hidden" id="varification_sandboxing_value" name="varification_sandboxing" value="<?php 
                    if(isset($systemConfig["varification_sandboxing"]))
                    {
                      echo $systemConfig["varification_sandboxing"];

                    }else{
                      echo "0";
                    }?>">
          <div class="form-group">
            <label for="aws/local">Files On AWS</label>
            <label class="switch">  
              <input type="checkbox" class="form-control" id="file_aws_local" name="file_aws_local" value="<?php 
                                if(isset($systemConfig["file_aws_local"]))
                                {
                                   echo $systemConfig["file_aws_local"];

                                 }else{
                                   echo "0";
                                }?>"
                                <?php
                                    if(isset($systemConfig["file_aws_local"])){
                                       if($systemConfig["file_aws_local"] == 0){
                                       ?>
                                        unchecked
                                        <?php      
                                          }else{
                                        ?>
                                        checked
                                        <?php      
                                          }
                                        }else{
                                       ?>
                                       uncheched
                                       <?php
                                    }
                                ?>  
                            >
              <span class="slider round"></span>
            </label>
          </div>
          <input type="hidden" id="file_aws_local_value" name="file_aws_local" value="<?php 
                    if(isset($systemConfig["file_aws_local"]))
                    {
                      echo $systemConfig["file_aws_local"];

                    }else{
                      echo "0";
                    }?>">
					<div class="form-group clearfix tmpl">
						@if(App\Helpers\SitePermissionCheck::isPermitted('systemconfig.store'))
						@if(App\Helpers\RolePermissionCheck::isPermitted('systemconfig.store'))
						<button type="submit" class="btn m1 btn-success col-lg-1 col-md-2  col-sm-12 col-xs-12 save" id="btnSave"><i class="loadsave"></i> Save</button> 
						@endif
						@endif
					</div>
				</div>
			</div>
      <div class="panel panel-primary">
        <div class="panel-body">
          <div class="col-lg-6">
            <div class="form-group">
                <label>Start Date :</label>
                <span>{{ $site_data['start_date'] }}</span>
            </div>
            <div class="form-group">
                <label>Last Date :</label>
                <span>{{ $site_data['end_date'] }}</span>
            </div>
          </div>
          <div class="col-lg-6">
            <div class="form-group">
                <label>License key :</label>
                <span>{{ $site_data['license_key'] }}</span>
            </div>
            <div class="form-group">
                <label>Printed Document Left :</label>
                <span>{{ $printing_value['value'] - $printing_value['current_value'] }}</span>
            </div>
          </div>
        </div>
      </div>
    
		</form>
	</div>
</div>
@stop
@section('script')
   <script type="text/javascript">
  
 $(document).ready(function() {
      	 
       $('#time_zone').val("{{ $systemConfig['timezone']  }}");    	 
       	if ($('#time_zone').val() == undefined){
			
			$('#time_zone').val('Asia/Kolkata');
		}
	  $("#print_color").val("{{ $systemConfig['print_color'] }}");
	   	if ($('#print_color').val() == undefined){
			
			$('#print_color').val('RGB');
		}	

   	$(".save").click(function(event) {
		event.preventDefault();
       var url="{{ URL::route('systemconfig.store') }}";
       var token="{{ csrf_token() }}";
       var method_type="post";
       $("#printing_template").ajaxSubmit({
             url:url,
             type:method_type,
             data:{'_token':token},     
             beforeSubmit:function()
             {
               $("#printing_template").find('span').text('').end();
               $(".loadsave").addClass('fa fa-spinner fa-spin');
             },
             success:function(data){
               
               if(data.success==true)
               {
               	  // if(data.sandboxing == 1){

               	  // 	bootbox.alert("Under Sandboxing Environment");
               	  // }
               	  toastr.success('Printer successfully added');
               	  location.reload();
               	  $(".loadsave").removeClass('fa fa-spinner fa-spin');
               } 
             },
             error:function(resobj){
             
               $.each(resobj.responseJSON.errors,function(k,v) {
               	  $("#"+k+'_error').text(v);
               });
               $(".loadsave").removeClass('fa fa-spinner fa-spin');
             } 
       });
		
	});	
 });

 $(".allow_number").keypress(function(h){
    var keyCode =h.which ? h.which : h.keyCode
       if (!(keyCode >= 48 && keyCode <= 57)) {
             return !1;
           }
 });
 $('#sandboxing').on('change',function(){

  var value = $('#sandboxing_value').val();
  var token = "<?= csrf_token()?>";

  
  $.ajax({
    type:'post',
    url : "<?=route('sand-box.update-value')?>",
    data : {'value':value,'_token':token},
    dataType:'json',
    success:function(response){

      if(response.value == 1){
        
        $('#sandboxing_value').val(1);
        $('#sandboxing_value').attr('checked');
        bootbox.alert("Under Sandboxing Environment");
      
      }else{

        $('#sandboxing_value').val(0);
      }
    }
  });
});
 $('#varification_sandboxing').on('change',function(){

  var value = $('#varification_sandboxing_value').val();
  // console.log(value);
  var token = "<?= csrf_token()?>";

  
  $.ajax({
    type:'post',
    url : "<?=route('varification.sandboxing.update-value')?>",
    data : {'value':value,'_token':token},
    dataType:'json',
    success:function(response){

      if(response.value == 1){
        
        $('#varification_sandboxing_value').val(1);
        $('#varification_sandboxing_value').attr('checked');
        bootbox.alert("Under Sandboxing Environment");
      
      }else{

        $('#varification_sandboxing_value').val(0);
      }
    }
  });
});

//file aws/local change

$('#file_aws_local').on('change',function(){

  var value = $('#file_aws_local_value').val();
  console.log(value);
  var token = "<?= csrf_token()?>";

  
  $.ajax({
    type:'post',
    url : "<?=route('file-aws-local.update-value')?>",
    data : {'value':value,'_token':token},
    dataType:'json',
    success:function(response){

      if(response.value == 1){
        
        $('#file_aws_local_value').val(1);
        $('#file_aws_local_value').attr('checked');
        bootbox.alert("Under AWS Environment");
      
      }else{

        $('#file_aws_local_value').val(0);
      }
    }
  });
});
  
   </script>
@stop

