<?php $__env->startSection('content'); ?>
<style type="text/css">.iconModalCss {
    right:92px !important;
  }</style>
<div class="container">
	<h1 class="page-header"><i class="fa fa-envira"></i> Scan History
<ol class="breadcrumb pull-right" style="background:transparent;font-size:14px;"><?php echo e(Breadcrumbs::render('scanhistory')); ?></ol>
<i class="fa fa-info-circle iconModalCss" title="User Manual" id="scanHistoryReportClick"></i>
</h1>
<div class="">
		<ul class="nav nav-pills" id="addUser">
		  <li class="active"><a id="web-pill" data-toggle="pill" href="#webapp"><i class="fa fa-fw fa-lg fa-desktop"></i> WebApp </a></li>
		  <li><a id="android-pill"data-toggle="pill" href="#android"><i class="fa fa-fw fa-lg fa-android"></i> Android</a></li>
		  <li><a id="iphone-pill"data-toggle="pill" href="#iphone"><i class="fa fa-fw fa-lg fa-apple"></i> iPhone</a></li>
		</ul>
<?php 
$domain =$_SERVER['HTTP_HOST'];
            $subdomain = explode('.', $domain);

            ?>
		<table id="example" class="table table-hover display" cellspacing="0" width="100%">
			<thead>
				<tr>
					<th>#</th>
					<th>Date</th>
					<th>Scanned Data</th>
					<th>Scan by</th>
					<th>User Type</th>
					<th>Result</th>
          <th></th>
				</tr>
			</thead>
			<tbody></tbody>
			<tfoot>
			</tfoot>
		</table>
	</div>
</div>
 <!-- // Student's info model -->
<?php echo $__env->make('admin.scanHistory.model', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
 <!-- // end Student's info model -->
<?php $__env->stopSection(); ?>


<?php $__env->startSection('script'); ?>

 <script type="text/javascript">

       // datatable	 
   var oTable = $('#example').DataTable( {
     'dom':  "<'row'<'col-sm-5' p><'col-sm-3' ><'col-sm-4'f>>",
        "bProcessing": false,
        "bServerSide": true,
        "autoWidth": true,
            "aaSorting": [
        [1, "desc"]
        ],
        "sAjaxSource":"<?= URL::route('scanHistory.index',['device_type'=>'webapp'])?>",
        "aoColumns":[
          {mData: "rownum", bSortable:false,"sClass": "text-center","width": "1%"},
          {
             mData: "date_time",
             bSortable:true,
             'Width':'5%',
             mRender:function(v){

                return moment(v).format('DD MMM YYYY hh:mm a');

             },
           },
         /* { 
          	mData: "device_type",
            'Width':'20%',
             bSortable:false,
            mRender:function(v){

             var  icon=null;
             var type=v.toLowerCase(); 
              if(type=="webapp")
              {
                 return icon='<i class="fa fa-fw fa-2x fa-desktop"></i>';
              } 
              else if(type=="android")
              {
                return icon='<i class="fa fa-fw fa-2x fa-android green"></i>';  
              }
              else if(type=="ios")
              {
                return icon='<i class="fa fa-fw fa-lg fa-apple"></i>';
              }
              
            },

          },*/
          {mData: "scanned_data", bSortable:false,'Width':'10%'},
          {mData: "fullname",bSortable:true,'Width':'5%',
            mRender:function(v,s,obj){
             
             if(obj['user_type'] == 1)
              {

                if(obj['username'] == null)
                {
                  return "admin";
                }
                else{
                  return obj['username'];
                }
                
                

              }else{
                  if(obj['scan_by'] == null)
                  {
                    return "admin";
                  }else if(obj['fullname'] == null)
                  {
                    return "admin";
                  }else{
                    return v;
                    
                  }
              }

            }
          },
          {
          	mData: "user_type",
            'Width':'14%',
          	bSortable:true,
          	mRender:function(v){
              //console.log(obj);
          		if(v == 1)
          		{
          			return "institute admin";
          		}
          		else
          		{
                var instance='<?php echo $subdomain[0];?>';
                if(instance=="sgrsa"){
                  return "verifier";
                }else{
                  return "student";
                }
          			
          		}
          	}
          },
          {
          	mData: "scan_result",
          	bSortable:false,
          	mRender:function(v,t,o){
            var button=null;
            <?php if(App\Helpers\SitePermissionCheck::isPermitted('scanningHistory.getdata')): ?>
            <?php if(App\Helpers\RolePermissionCheck::isPermitted('scanningHistory.getdata')): ?> 
              if(v==1)
              {
              	 return button='<span style="cursor: pointer; color: #fff;" class="btn btn-theme" id="infoData">Success</span>';
              }
              else if(v==0)
              {
              	return button='<span style="cursor:not-allowed;color: #fff;" class="btn btn-theme2">Inactive QR</span>';
              }
              else if(v==2)
              {
              	return button ='<span style="cursor:not-allowed;color: #fff;" class="btn btn-theme2">Regular QR</span>';
              }
              else
              {
                 return '';
              }
            <?php endif; ?>
            <?php endif; ?>
            return '';
          	},
          },

          {mData: "updated_at",bSortable:false,bVisible:false},  
      ],
    });
   // show information  Student's info
 	oTable.on('click', '#infoData', function () {
      
	    	var key_id = $(this).closest("tr").find('td:eq(2)').text();
        var url_path="<?php echo e(URL::route('scanningHistory.getdata')); ?>";
        var token="<?php echo e(csrf_token()); ?>";

        $("#info1,#info2,#info3,#info4,#info5").text(' ');
		 $.ajax({    
	    		url: url_path,
	    		type:'post',
	    		data:{'key':key_id,'_token':token},
	    		dataType: 'json',
	    		success: function(response) {
					if(response)
					{
						$('#info').modal('show');
						var $status = response['status'] ? 'Active' : 'Inactive';
						var $path   = response['path'];

						$('#info1').html(response['serial_no']);
						$('#info2').html(response['student_name']);
						$('#info3').html(response['certificate_filename']);
						$('#info4').html($status);
						$('#info5').html('<img class="img-responsive img-thumbnail" src="<?= Config::get('constant.server_canvas_upload_path')?>/'+$path+'">');
						$('#info6').html(response['key']);
						$("a#info3").attr("href", "<?= Config::get('constant.show_pdf')?>/"+response['certificate_filename']);
					}
          $('#info').modal('show');
				}
		 });

	}); 
  
  oTable.on('draw.dt', function () {
    $vary = $('#addUser');
    $('#example_length').prepend($vary);
    $('#addUser').css('margin-right','20px');
    $('.loader').addClass('hidden');
  }); 
  // get data webapp user      
 $('#web-pill').click(function(){
    $('.loader').removeClass('hidden');
    var url="<?= URL::route('scanHistory.index',['device_type'=>'webapp'])?>";
    oTable.ajax.url(url);
    oTable.ajax.reload();
  
  });
  // get data android user

  $('#android-pill').click(function(){
    $('.loader').removeClass('hidden');
    var url="<?= URL::route('scanHistory.index',['device_type'=>'android'])?>";
    oTable.ajax.url(url);
    oTable.ajax.reload();
  });
   // get data ios user
   $('#iphone-pill').click(function(){
     $('.loader').removeClass('hidden');
    var url="<?= URL::route('scanHistory.index',['device_type'=>'ios'])?>";
    oTable.ajax.url(url);
    oTable.ajax.reload();  
  });
 </script>


 <?php $__env->stopSection(); ?>

 <?php $__env->startSection('style'); ?>
<style>
#example td{
	word-break: break-all;
}

.nav-pills>li.active>a, .nav-pills>li.active>a:focus{
	background:#0052CC;
	color:#fff;
	border:1px solid #0052CC;
}


.nav-pills>li.active>a:hover, .nav-pills>li>a:focus, .nav-pills>li>a:hover
{
	background:#fff;
	background:#ddd;
	border-radius:0;
	padding:10px 20px;
	color:#333;
	border-radius:2px;
	border:1px solid #ddd;
}

.nav-pills>li>a, .nav-pills>li>a
{
	background:#fff;
	color:#aaa;
	border-radius:0;
	padding:10px 20px;
	border-radius:2px;
	margin-bottom:20px;
	border:1px solid #ddd;
}


#example_length label{
	display:none;
}
.help-inline{
  color:red;
  font-weight:normal;
}

.breadcrumb{
  background:#fff;
}

.breadcrumb a{
  color:#666;
}

.breadcrumb a:hover{
  text-decoration:none;
  color:#222;
}

.loader{
  display: table;
    background: rgba(0,0,0,0.5);
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    text-align: center;
    vertical-align: middle;
}

.loader-content{
  display:table-cell;
  vertical-align: middle;
  color:#fff;
}
</style>
 <?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layout.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/admin/scanHistory/index.blade.php ENDPATH**/ ?>