<?php $__env->startSection('content'); ?>
  <div class="container">
	<div class="container-fluid">
		
		 <?php echo $__env->make('admin.raisoni.paymentGatewayConfig.documentRateMaster', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>	
		 <?php echo $__env->make('admin.raisoni.paymentGatewayConfig.templateRateMaster', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>	
	</div>
</div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>
<script type="text/javascript">
$(document).ready(function() {	

	
   /*______________Mandar Gawade Start__________*/
  // Fetch Documents Rate Master Data
  $("#documentRateMastertbody").empty();
     var ajaxURL="<?php echo e(URL::route('documentsratemaster.index')); ?>";
     var token="<?php echo e(csrf_token()); ?>";
	 $.get(ajaxURL,
	 { 
		 _token:token
	 },function(data){
	 	$.each(data,function(index, el) {
	 		 $("#documentRateMastertbody").append('<tr>'+
										'<td>'+el.document_name+'</td>'+
										'<td><input type="text" class="form-control allow_number" id="'+el.document_id+'" name="'+el.document_id+'" data-rule-required="true" value="'+el.amount_per_document+'" ></td>'+
										'<td>'+moment(el.updated_at).format('DD MMM YYYY hh:mm A')+'</td>'+
										'</tr>');
	 	});
		
	 });
   // End Fetch Documents Rate Master Data

   //Update Request Document Master
   $("#update_save_document_rate").click(function(event) {
   	 event.preventDefault();
      var update_path="<?php echo e(URL::route('documentsratemaster.update')); ?>";
      var token="<?php echo e(csrf_token()); ?>";
      var method_type="post";
      $("#document_rate_data").ajaxSubmit({
            url:update_path,
            data:{'_token':token},
            type:method_type,
            beforeSubmit:function(){
             $("#document_rate_data").find('span').text('').end();
             $(".loadsave").addClass('fa fa-spinner fa-spin');
            },
            success:function(data){
             if(data.success==true)
             {
             	toastr.success('Updated successfully');
             	//window.location.reload();
              $(".loadsave").removeClass('fa fa-spinner fa-spin');
             }
            },
            error:function(resobj){
            
              $.each(resobj.responseJSON.errors,function(k,v){
 
                $("#"+k+'_error').text(v); 
              });
              $(".loadsave").removeClass('fa fa-spinner fa-spin');
            }
           
      });

   });
   //End Update Request Document Master

   //Start Datatable Template Rate Master
   	var oTable = $('#example').DataTable({
		'dom':  "<'row'<'col-sm-3'i><'col-sm-4' p><'col-sm-1' ><'col-sm-4'f>>",
        "bProcessing": false,
        "bServerSide": true,
        "autoWidth": true,

        "aaSorting": [
        [4, "desc"]
        ],
        "sAjaxSource":"<?= URL::route('templateratemaster.index',['status'=>1])?>",
        "aoColumns":[
		{mData: "rownum", bSortable:false},
		{mData: "actual_template_name"},
		{mData: "scanning_fee",bSortable:true,"sClass": "text-center",
		 mRender: function(v, t, o) {
            	var buttons = '';
            	buttons = '<input type="text" class="form-control allow_number" id="'+o['id']+'" name="'+o['id']+'" data-rule-required="true" value="'+o['scanning_fee']+'" />';
				return buttons;
            },
        },
		{mData: "updated_at",bSortable:true,
              "sClass": "text-center",  
              mRender:function(v)
              {
              	if(v!=null){
              		return moment(v).format('DD MMM YYYY hh:mm A');
              	}else{
              		return '-';
              	}
                  
              } 
          },
		{mData: 'id',
            bSortable: false,
            sWidth: "30%",
            sClass: "text-center",
            mRender: function(v, t, o) {
            	var buttons = '';
            	buttons = '<span class="update_template_fee" onclick="updateFee('+o['id']+')" data-id="'+o['id']+'" style="cursor: pointer;padding: 8px;color: #fff;background-color: #0052cc;border-radius: 5px;">Update Fee</span>';
            	buttons = '<?php if(App\Helpers\SitePermissionCheck::isPermitted('templateratemaster.update')): ?> <?php if(App\Helpers\RolePermissionCheck::isPermitted('templateratemaster.update')): ?> <span class="update_template_fee" onclick="updateFee('+o['id']+')" data-id="'+o['id']+'" style="cursor: pointer;padding: 8px;color: #fff;background-color: #0052cc;border-radius: 5px;">Update Fee</span> <?php endif; ?> <?php endif; ?>'; 				return buttons;
            },
         		   	
        },
	],
	
});
oTable.on('draw', function () {
	$('[title]').tooltip(); 
});
$('#success-pill').click(function(){

	var url="<?= URL::route('templateratemaster.index',['status'=>1])?>";
	oTable.ajax.url(url);
	oTable.ajax.reload();
	$('.loader').addClass('hidden');
});

$('#fail-pill').click(function(){

	var url="<?= URL::route('templateratemaster.index',['status'=>0])?>";
	oTable.ajax.url(url);
	oTable.ajax.reload();
	$('.loader').addClass('hidden');
});
//End Datatable template rate master



   /*______________Mandar Ends__________*/

});
 /*______________Mandar Start__________*/
// Update data Template Master
     function updateFee(id)
       {
         $(".update_template_fee").attr('disabled'); 
         var template_id=id;
         var scanning_fee=$('#'+template_id).val();
         event.preventDefault();
         var update_path="<?= URL::route('templateratemaster.update',array(':id')) ?>";
         update_path=update_path.replace(':id',template_id);
         var token="<?php echo e(csrf_token()); ?>";
         var method_type="post";

         $.ajax({
                
                url  : update_path,
                type : method_type,
                data : {'_token':token,'id':template_id,'scanning_fee':scanning_fee},
                before:function(){
                  $(".loadupdate").addClass('fa fa-spinner fa-spin');
                  $(".update_template_fee").attr('disabled','disabled');
               },
                success:function(data)
               {
                 if(data.success==true)
                 {
                   
                   toastr.success('Fee updated successfully');
                   //oTable.ajax.reload();
                   $(".update_template_fee").removeAttr('disabled'); 
                   $(".loadupdate").removeClass('fa fa-spinner fa-spin');
                 }
               },
               error:function(resobj)
               {
                 $.each(resobj.responseJSON.errors, function(k,v){
                     $('#'+k+'_error').text(v);
                  });
                 $(".update_template_fee").removeAttr('disabled');
                 $(".loadupdate").removeClass('fa fa-spinner fa-spin'); 
               },
          });

              
  }

   /*______________Mandar Ends__________*/
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layout.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/admin/raisoni/paymentGatewayConfig/index.blade.php ENDPATH**/ ?>