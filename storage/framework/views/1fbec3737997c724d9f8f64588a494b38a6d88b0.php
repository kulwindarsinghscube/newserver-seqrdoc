<!--css include-->
<?php $__env->startSection('style'); ?>
<link rel="stylesheet" href="<?php echo e(asset('backend/css/sweetalert.min.css')); ?>">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

	<div class="container">
		<div class="col-xs-12">
			<div class="clearfix">
				<div id="">
					<div class="container-fluid">
						<div class="row">
							<div class="col-lg-12">
								<h1 class="page-header"><i class="fa fa-file-image-o"></i> Dynamic Image Managemant
								<ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;"><?php echo e(Breadcrumbs::render('dynamicimage')); ?></ol>
								<i class="fa fa-info-circle iconModalCss" title="User Manual" id="dynamicImageManagementClick"></i>
								</h1>
							</div>
						</div>
						<div class="row">
							<div class="alert alert-warning">
							 	Please select one of the template folders, from list displayed on left side, to view images
							</div>

							
						</div>	
						<div class="col-md-12" style="margin: 10px;">
							<div class="col-md-3">
								<h3 id="selected_folder" style="word-break: break-word;"></h3>
							</div>
							<div class="col-md-9">
								<div id="image_div" style="display: none;">
									<?= Form::open(['id'=>'img_form','method'=>'POST','files'=>true])?>
										<div class="col-md-4">
											<label>Sory By</label>
											<select name="sort_by" id="sort_by" class="form-control" data-rule-required="true">
												<option value="atoz">Sort by A-Z</option>
												<option value="date">Sort by Date</option>
											</select>
										</div>
										
										<?php if(App\Helpers\SitePermissionCheck::isPermitted('dynamic-image-management.store')): ?>

                                        <?php if(App\Helpers\RolePermissionCheck::isPermitted('dynamic-image-management.store')): ?>
										<div class="col-md-5">
											<input type="file" name="image_upload[]" multiple="multiple" class="pull-right" id="image_upload" style="display: none;margin-top: 27px;" size="60" value="Image upload" >
											<p id="img_error"></p>
											<input type="hidden" name="folder_name" id="folder_name" value="" >
											<input type="hidden" name="sort_folder_name" id="sort_folder_name" value="" >
										</div>
										<div class="col-md-3">
											<input type="submit" name="save" id="img_save" style="margin-top: 20px;margin-left: -100px;" class="btn btn-primary">
											<span class="btn btn-primary" id="loadMoreImages" style="display: none;margin-top: 20px;">Load More Images</span>
											<input type="hidden" name="nextImageOffset" id="nextImageOffset" value="0">
											</div>

											<div class="col-md-8" style="margin-left: -33.3%;margin-top: 15px;height: 40px;">
												<input type="text" class="form-control" name="image_search" id="image_search" placeholder="Enter search keyword" style="max-width: 230px;"><span class="btn btn-primary" id="searchBtn" style="margin-top: -55px;position: relative;margin-left: 240px;">Search</span><span class="btn btn-danger" id="clearSearchBtn" style="margin-top: -95px;position: relative;margin-left: 315px;">Clear Search</span>
											</div>
										</div>
                                        <?php else: ?> 
										<div class="col-md-6">
											<input type="hidden" name="folder_name" id="folder_name" value="" >
											<input type="hidden" name="sort_folder_name" id="sort_folder_name" value="" >
										</div>
										<?php endif; ?>

									 <?php else: ?>
									    <div class="col-md-6">
											<input type="hidden" name="folder_name" id="folder_name" value="" >
											<input type="hidden" name="sort_folder_name" id="sort_folder_name" value="" >
										</div>
									 <?php endif; ?>	
								

									<?Form::close()?>
								</div>				
							</div>
						</div>				
						<div class="col-md-12" style="padding: 0px;">
							<div class="col-md-3" style="background-color: #f1f1f1; margin: 0px; padding: 0px;">
								<ul class="image_menu">
									<?php if(!empty($template_data)): ?> :					
										<?php $__currentLoopData = $template_data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $template_key => $template_value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
											<div class="col-md-12">
												<li class="sub_menu">
												<a href="JavaScript:void(0);" data-name="<?php echo e($template_value['template_id']); ?>" onclick="imageShow('<?php echo e($template_value['template_id']); ?>','atoz');"><?php echo e(ucfirst($template_value['template_name'])); ?>(<div style="display: contents;" ><?php echo e($template_value['count']); ?></div> )
												</a>

												</li>
											</div>
										<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
									<?php endif; ?>
								</ul>
							</div>
								<div class="col-md-9 custom_scroll" style="display: none;" id="all_images"></div> 
								<div class="col-md-9"  id="loadMore">
									<!-- <span class="btn btn-primary" id="loadMoreImages">Load More</span>
									<input type="hidden" name="nextImageOffset" value="0"> -->
								</div> 

								<div class="col-md-9" style="display: none;font-size: 25px;left: 50%;margin-top: 10%;position: absolute;color: gray;" id="imagesLoader">Please wait...<img src="<?= \Config::get('constant.local_base_path').'backend/images/'?>loading.gif"/></div> 

								<div class="col-md-9" style="display: none;font-size: 25px;left: 50%;margin-top: 10%;position: absolute;color: orangered;border: 1px solid red;max-width: 230px;" id="imageNotFound">Image not found!</div> 
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php $__env->stopSection(); ?>



<?php $__env->startSection('script'); ?>	
<script src="<?php echo e(asset('backend/js/sweetalert.min.js')); ?>"></script>
<script type="text/javascript">

var globalImageArr=[];

var template_id='';
var systemConfig='';
var file_aws_local='';
	jQuery.validator.addMethod("sizeCheck", function(value,element) {		

		var files = $('#'+element.id)[0].files;
		
		for (var i = 0; i < files.length; i++) {
			var size = files[i].size / 1024;
			// console.log(size);
			if(size > 200){
				return false
			}
		}
		return true;
	}, "Please upload image less then 200KB");	

	jQuery.validator.addMethod("extCheck", function(value,element) {
		
		var files = $('#'+element.id)[0].files;
		
		for (var i = 0; i < files.length; i++) {
			var fname = files[i].name.toLowerCase();
			
			var re = /(\.jpg|\.png|\.jpeg|\.JPG|\.JPEG\.PNG)$/i;
            if(!re.exec(fname))
            {
                return false;
            }
		}
		return true;
	}, "Select image only JPG & PNG");

	//on submit click upload multiple images
	$('#img_save').click(function (event) {
		validateUpload();
	});


	function validateUpload() {
		$('#img_error').text('');
		var token = '<?= csrf_token()?>';
		var validator2 = $('#img_form').validate({
			rules: {
				"image_upload[]": { sizeCheck: true,extCheck:true,required: true }
			},
			messages: {
				image_upload: {
					"sizeCheck[]": "Please upload image less then 200KB",
					"extCheck[]":"Select image only JPG & PNG",
					"required":"Please select image"
				}
			},
			submitHandler:function(form){
				var folder_name = $('#folder_name').val();
				//on submit click upload multiple images
		       	$('#img_form').ajaxSubmit({
			        url: "<?=route('dynamic-image-management.store')?>",
			        type: "POST",
			        dataType: "json",
			        success: function(response){
		          		var data = response.data;
		              if(data[0].success == 'true'){
		              		if(data[0].imageCounts==1&&data[0].alreadyExistImages!=''){
		              		toastr["error"]('File already exists.');
		              	}else{

		              
		              	toastr["success"](data[0].message);
		              		}

		              		if(data[0].alreadyExistImages!=''){
		              			var link = document.createElement('a');
								link.href = data[0].alreadyExistImages;
								link.setAttribute('target', '_blank');
								link.download = data[0].alreadyExistImages;
								link.click();
								link.remove();
					
		              		}
		              	var prv_count = $('#imageCounts').text();
		              	$('.selectedcategoryServices').children().find('div').text(data[0].imageCounts);
		              	form.reset();
		              	imageShow(data[0].folder_name,'atoz');
		              }else{
		              	form.reset();
		              	toastr["error"](data[0].message);
		              	// $('#img_error').text(data[0].message);
		              	// $('#img_error').css("color", "red");
		              }
		          	}
		       });
			}
		});
	}


	//on template name(folder) click image show function call
	function imageShow(value,sort_by,searchkey='') {
		//console.log(value);

		$('#folder_name').val(value);
		if(sort_by == 'atoz'){
		
			$("#sort_by").val(sort_by)
				.find("option[value=" + sort_by +"]").attr('selected', true);
		}
		$('#image_div').css('display','block');
		$('#image_upload').css('display','block');
		$('#all_images').css('display','block');
		$('#all_images img').remove();
		$('#all_images div').remove();
		$('#imageNotFound').hide();
		$('#imagesLoader').hide();
		var temp_array = '<?php echo json_encode($template_data) ?>';

		var temp_Array = JSON.parse(temp_array);
		
		var url = "<?= route('dynamic-image-management.Display',[':sortby',':value',':searchkey'])?>";
		//console.log(url);
		url = url.replace(':sortby',sort_by);
		//console.log(url);
		url = url.replace(':value',value)
		//console.log(url);
		url = url.replace(':searchkey',searchkey)
    //console.log(url);
   // $("#folder_name").val(value);

		$.ajax({
		    url : url,
		    type:'GET',	
		    dataType: "json", 
		    beforeSend: function(){
		                $("#imagesLoader").show();
		           },
		    success: function (response) { 
		    	 $("#imagesLoader").hide();
		    	var selected_folder = response['template_name']; 
		    	var data = response['data'];
		    	globalImageArr= response['data'];
		    	var totalImages=globalImageArr.length;
		    	//console.log(totalImages);
		    	if(totalImages>20){
		    		$('#nextImageOffset').val(20);
		    		$('#loadMoreImages').show();
		    		var maxLoadImages=20;
		    	}else{
		    		if(totalImages==0){
		    			$('#imageNotFound').show();
		    		}
		    			var maxLoadImages=totalImages;
		    		$('#nextImageOffset').val(totalImages);
		    		$('#loadMoreImages').hide();	
		    	
		    		
		    	}
		    	template_id=value;
		    	$('#sort_folder_name').val($('#folder_name').val());
		    	$('#selected_folder').html('<b>'+selected_folder+'</b>');
		    	

	for (var j =0; j< maxLoadImages;  j++) {
		
		
		var v=globalImageArr[j];
		//console.log(v)
		        	var myJSON = JSON.stringify(v);
		        	var folder_json = JSON.stringify(value);
//console.log();

		        	<?php
		        		$domain = \Request::getHost();
        				$subdomain = explode('.', $domain);
		        	?>
		    		
		        	systemConfig=response['systemConfig'] ;
		        	file_aws_local=response['get_file_aws_local_flag']['file_aws_local'];

		        	var subdomain0='<?php echo $subdomain[0];?>';
		            if( v.match(/\.(jpeg|png|jpg|JPG|PNG|JPEG)$/) ) { 
		            	if(value=="DC_Photo"&&subdomain0=="bmcc"){
		            		var path = '<?= \Config::get('constant.local_base_path').$subdomain[0].'/'.Config::get('constant.backend').'/'?>'+value+'/'+v
		            	}else{
		            	if(response['systemConfig'] == '1'){
				            if(response['get_file_aws_local_flag']['file_aws_local'] == '1'){
				            	var path = '<?= \Config::get('constant.amazone_path').$subdomain[0].'/'.Config::get('constant.sandbox').'/'?>'+value+'/'+v
				            }
				            else{
				            	var path = '<?= \Config::get('constant.local_base_path').$subdomain[0].'/'.Config::get('constant.sandbox').'/'?>'+value+'/'+v
				            }
				        }
				        else{
				        	if(response['get_file_aws_local_flag']['file_aws_local'] == '1'){
				            	var path = '<?= \Config::get('constant.amazone_path').$subdomain[0].'/'.Config::get('constant.template').'/'?>'+value+'/'+v
				            }
				            else{
				            	var path = '<?= \Config::get('constant.local_base_path').$subdomain[0].'/'.Config::get('constant.template').'/'?>'+value+'/'+v
				            }
				        }

				    	}
		                $('#all_images').append("<div id='all_images_"+j+"' class='all_images_inner' style='position: relative;float: left; width:162px; height:135px;border:1px solid #ccc; margin:10px;'> <img src='"+path+"' style='width:150px; height:100px; margin:5px;'><div style='position: absolute; right: 0; top: 0;' id='close_"+j+"'> <a href='JavaScript:void(0);' onclick='imageRemove("+folder_json+","+myJSON+","+j+");' class='fa fa-close'></i></a></div> <div id="+j+"> <a href='JavaScript:void(0);' onclick='editImageText("+j+","+myJSON+","+folder_json+");' ><div id='img_name_"+j+"' class='content'>"+v+"</div></a></div> </div>")
		            }
			
					}
		        /*$.each(data, function( i, v ) {
		        	//console.log(value)
		        	var myJSON = JSON.stringify(v);
		        	var folder_json = JSON.stringify(value);

		        	<?php
		        		$domain = \Request::getHost();
        				$subdomain = explode('.', $domain);
		        	?>

		        	var subdomain0='<?php echo $subdomain[0];?>';
		            if( v.match(/\.(jpeg|png|jpg|JPG|PNG|JPEG)$/) ) { 
		            	if(value=="DC_Photo"&&subdomain0=="bmcc"){
		            		var path = '<?= \Config::get('constant.local_base_path').$subdomain[0].'/'.Config::get('constant.backend').'/'?>'+value+'/'+v
		            	}else{
		            	if(response['systemConfig'] == '1'){
				            if(response['get_file_aws_local_flag']['file_aws_local'] == '1'){
				            	var path = '<?= \Config::get('constant.amazone_path').$subdomain[0].'/'.Config::get('constant.sandbox').'/'?>'+value+'/'+v
				            }
				            else{
				            	var path = '<?= \Config::get('constant.local_base_path').$subdomain[0].'/'.Config::get('constant.sandbox').'/'?>'+value+'/'+v
				            }
				        }
				        else{
				        	if(response['get_file_aws_local_flag']['file_aws_local'] == '1'){
				            	var path = '<?= \Config::get('constant.amazone_path').$subdomain[0].'/'.Config::get('constant.template').'/'?>'+value+'/'+v
				            }
				            else{
				            	var path = '<?= \Config::get('constant.local_base_path').$subdomain[0].'/'.Config::get('constant.template').'/'?>'+value+'/'+v
				            }
				        }

				    	}
		                $('#all_images').append("<div id='all_images_"+i+"' class='all_images_inner' style='position: relative;float: left; width:162px; height:135px;border:1px solid #ccc; margin:10px;'> <img src='"+path+"' style='width:150px; height:100px; margin:5px;'><div style='position: absolute; right: 0; top: 0;' id='close_"+i+"'> <a href='JavaScript:void(0);' onclick='imageRemove("+folder_json+","+myJSON+","+i+");' class='fa fa-close'></i></a></div> <div id="+i+"> <a href='JavaScript:void(0);' onclick='editImageText("+i+","+myJSON+","+folder_json+");' ><div id='img_name_"+i+"' class='content'>"+v+"</div></a></div> </div>")
		            } 
		        });*/
		    }
		});
	}

	//on dropdown change
	$('#sort_by').change(function(){
		
		var folder_name = $('#sort_folder_name').val();
		var sort_by = $(this).val();
		imageShow(folder_name,sort_by);
	})

$('#searchBtn').click(function (event) {
		var folder_name = $('#sort_folder_name').val();
		var sort_by = $('#sort_by').val();
		var searchkey = $('#image_search').val();
		if(searchkey!=''){
			imageShow(folder_name,sort_by,searchkey);
		}else{
			toastr["error"]('Please enter search keyword.');
		}
		//console.log(folder_name+' '+sort_by+' '+searchkey);
	});

$( "#clearSearchBtn" ).click(function() {
  var folder_name = $('#sort_folder_name').val();
		var sort_by = $('#sort_by').val();
		var searchkey = $('#image_search').val();
		if(searchkey!=''){
			$('#image_search').val('');
			imageShow(folder_name,sort_by,'');
		}
});

	//on image text click open text box
	function editImageText(id,img,folder){
		
		var image = img;
		var	name = image.split(".");
		img_name = name[0];
    	var image_str = JSON.stringify(img);
    	var folder = JSON.stringify(folder);
    	
		$('#all_images').find("input").remove();
		$('.all_images_inner').height(180);
		$('#'+id).append("<div ><input type='text' id='edit_image' value='"+img_name+"' class='txtbox'><input type='submit' name='edit_image_name' value='Go' id='edit_image_name' class='btn btn-primary go' onclick='editImage("+image_str+","+folder+","+id+")'></div>");
	}

	//after changing name in text box on name click update the image name
	function editImage(img,folder,i){
		//get image name
		var img_name = $('#edit_image').val();
		var edit_url = "<?= route('dynamic-image-management.updatingImage')?>";
		var token = "<?php echo e(csrf_token()); ?>";
		$('#error_'+i).text('');
		$.ajax({
		    url : edit_url,
		    dataType: "json",
		    type: "POST",
		    data:{'_token':token,'folder_name':folder,'old_image_name':img,'image_name':img_name},
		    success: function (data) {	    	
		    	if(data.success == 'true'){
		          	$('#img_name_'+i).text(data.image);
		          	$('#edit_image').css('display','none');
		          	$('#edit_image_name').css('display','none');
		          	$('#'+i).find('a').removeAttr('onclick');
		          	$('#close_'+i).find('a').removeAttr('onclick');
					$('#'+i).find('a').attr('onclick',"editImageText('"+i+"','"+data.image+"','"+folder+"')");
					$('#close_'+i).find('a').attr('onclick',"imageRemove('"+folder+"','"+data.image+"','"+i+"')");
					toastr["success"](data.message);
		
		        }else{
		          	toastr["error"](data.message);
		          	
		        }
	    	}
	    });
	} 

	// sweetalert for image remove
	function imageRemove(folder,image,i) {
		
  		bootbox.confirm({
			message : "Are you sure you want to delete?",
			buttons : {
				confirm: {
					label: "Yes",
					className: 'btn-success'
				},
				cancel : {
					label: "No",
					className: 'btn-danger'
				}
			},
			callback: 
				function(result) {
					if(result) {
						deleteImage(folder,image,i);
					}
				}
		});
	}

	// image remove
	function deleteImage(folder_name,image_name,i) {
		var delete_url = '<?= route('dynamic-image-management.deleting')?>';
		var token = "<?php echo e(csrf_token()); ?>";
		$.ajax({
		    url : delete_url,
		    dataType: "json",
		    type: "POST",
		    data:{'_token':token,'folder_name':folder_name,'image_name':image_name},
		    success: function (data) {
		    	if(data.success == 'true'){
		    		toastr["success"](data.message);
		          	$('#all_images_'+i).remove();
		          	$('.selectedcategoryServices').children().find('div').text(data.imageCounts);
		        }
	    	}
	    });
	}

	$(document).ready(function(){
	    $("div").scroll(function(){
	        
	    });

	    $('.sub_menu').click(function(){
	    	$(".sub_menu").removeClass("selectedcategoryServices");
	     	$(this).addClass('selectedcategoryServices');
	    });

	    $("#serial_no").removeAttr('readonly');
		$('a[href^="dynamic_image_management.php"]').parent().addClass('active');
		$('a[href^="dynamic_image_management.php"]').parent().parent().parent().addClass('active');

		$('#loadMoreImages').click(function(){
			//console.log(globalImageArr);

			var nextImage=$('#nextImageOffset').val();

	var globalImageArrLen=globalImageArr.length;

	var imagesToLoad=parseInt(globalImageArrLen)-parseInt(nextImage);

	if(imagesToLoad<20){
		var maxLoadImages=parseInt(nextImage)+parseInt(imagesToLoad);
		//var nextImageOffset=parseInt(nextImage)+parseInt(maxLoadImages);
		$('#nextImageOffset').val(maxLoadImages);
		$('#loadMoreImages').hide();

	}else{
		var maxLoadImages=parseInt(nextImage)+parseInt(20);
		//var nextImageOffset=parseInt(nextImage)+parseInt(maxLoadImages);
		$('#nextImageOffset').val(maxLoadImages);
	}
	var value=template_id;
	for (var j =nextImage; j< maxLoadImages;  j++) {
		
		
		var v=globalImageArr[j];
		//console.log(v)
		        	var myJSON = JSON.stringify(v);
		        	var folder_json = JSON.stringify(value);
//console.log();

		        	<?php
		        		$domain = \Request::getHost();
        				$subdomain = explode('.', $domain);
		        	?>

		        	var subdomain0='<?php echo $subdomain[0];?>';
		            if( v.match(/\.(jpeg|png|jpg|JPG|PNG|JPEG)$/) ) { 
		            	if(value=="DC_Photo"&&subdomain0=="bmcc"){
		            		var path = '<?= \Config::get('constant.local_base_path').$subdomain[0].'/'.Config::get('constant.backend').'/'?>'+value+'/'+v
		            	}else{
		            	if(systemConfig == '1'){
				            if(file_aws_local == '1'){
				            	var path = '<?= \Config::get('constant.amazone_path').$subdomain[0].'/'.Config::get('constant.sandbox').'/'?>'+value+'/'+v
				            }
				            else{
				            	var path = '<?= \Config::get('constant.local_base_path').$subdomain[0].'/'.Config::get('constant.sandbox').'/'?>'+value+'/'+v
				            }
				        }
				        else{
				        	if(file_aws_local == '1'){
				            	var path = '<?= \Config::get('constant.amazone_path').$subdomain[0].'/'.Config::get('constant.template').'/'?>'+value+'/'+v
				            }
				            else{
				            	var path = '<?= \Config::get('constant.local_base_path').$subdomain[0].'/'.Config::get('constant.template').'/'?>'+value+'/'+v
				            }
				        }

				    	}
		                $('#all_images').append("<div id='all_images_"+j+"' class='all_images_inner' style='position: relative;float: left; width:162px; height:135px;border:1px solid #ccc; margin:10px;'> <img src='"+path+"' style='width:150px; height:100px; margin:5px;'><div style='position: absolute; right: 0; top: 0;' id='close_"+j+"'> <a href='JavaScript:void(0);' onclick='imageRemove("+folder_json+","+myJSON+","+j+");' class='fa fa-close'></i></a></div> <div id="+j+"> <a href='JavaScript:void(0);' onclick='editImageText("+j+","+myJSON+","+folder_json+");' ><div id='img_name_"+j+"' class='content'>"+v+"</div></a></div> </div>")
		            }
			
	}
		});
	});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\inetpub\wwwroot\demo\resources\views/admin/dynamicImageManagement/index.blade.php ENDPATH**/ ?>