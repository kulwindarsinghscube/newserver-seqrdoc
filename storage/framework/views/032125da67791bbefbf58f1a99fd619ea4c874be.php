<?php $__env->startSection('content'); ?>

<style>
.containerPdf {
  position: relative;
  width: 100%;
  overflow: hidden;
  padding-top: 56.25%; /* 16:9 Aspect Ratio */
    min-height: 1140px;
}

.responsive-iframe {
  position: absolute;
  top: 0;
  left: 0;
  bottom: 0;
  right: 0;
  width: 100%;
  height: 100%;

  border: none;
}

@media  only screen and (max-width: 600px) {
  .containerPdf {
    min-height: 1140px;
  }
}

@media  only screen and (max-width: 420px) {
  .containerPdf {
  	margin-top: 20px;
    min-height: 550px;
  }

  .responsive-iframe {
  	width: 420;
  }
}

</style>

<br>
<div class="row">
	<div class="col-lg-12 col-md-12 col-sm-12">

		<div class="row">
			<div class="col-lg-12 col-md-12 col-sm-12">
				<div style="    color: red;
    font-size: 23px;
    max-width: 600px;
    margin: auto;
    text-align: center;
    background-color: #fff;
    padding: 10px;
    border: 1px solid #dbdbdb;
    border-radius: 5px;">
    <img src="../backend/images/error.png" style="    max-width: 100px;" />
    <br>
		<?php echo $data['message']; ?>
				</div>
				</div>

		</div>
	</div>
</div>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>
<script>
$('a[data-url^="dashboard"]').parent().addClass('active');
$("#name").fadeToggle(1000);
$("#description").fadeToggle(1500);
$("#card1").fadeToggle(2000);
$("#card2").fadeIn(2500);
$("#card3").fadeIn(3000);
$("#card4").fadeIn(3500);
$("#card5").fadeIn(4000);
$("#cardDownload").fadeIn(4000);
$("#pdfDiv").slideUp(1500).slideDown(2000);

$("#showMint").click(function(){
	if($('.mint-heading').is(':visible')){

  		$(".mint-details").fadeOut(500);
		$('.mint-heading').fadeOut(800);
	}else{
		$('.mint-heading').fadeIn(1500);
  		$(".mint-details").fadeToggle(2000);		
	}
  
});



</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('bverify.layout.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/bverify/failed.blade.php ENDPATH**/ ?>