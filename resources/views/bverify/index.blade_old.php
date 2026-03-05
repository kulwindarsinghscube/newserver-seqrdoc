@extends('bverify.layout.layout')
@section('content')
<br><div class="row">
	<div class="col-lg-4 col-md-4 col-sm-12">
	<div class="row" style="margin: auto;border: 2px solid #dbdbdb;">

		<?php if($data['metadata']){
				$i=1;
			?>
		<div class="col-lg-12 col-md-12 col-sm-12 text-center" style="background-color: orange;padding: 10px;color: #fff;margin-bottom: 10px;font-size: 17px;">
			<b>DATA</b>
			<!-- <span style="    color: #fff;
    background-color: darkred;
    padding: 3px 5px 3px 5px;
    border-radius: 5px;
    font-size: 12px;
    position: absolute;
    right: 3px;
    bottom: 3px;cursor: pointer;">View MINT Details</span> -->
		</div>
		<?php foreach ($data['metadata'] as $readData) { 
				if(!empty($readData->trait_type)&&!empty($readData->value)&&$readData->trait_type!="UniqueHash"){
			?>
				<div class="col-lg-12 col-md-1 col-sm-12 text-center">
					<div class="card" style="margin: auto;display: none;" id="<?php echo "card".$i;?>">
					  <div class="card-header"  style="  word-wrap: break-word;background-color: #0052cc;color: #fff;">
					    <?php echo $readData->trait_type;?>
					  </div>
					  <ul class="list-group list-group-flush">
					    <li class="list-group-item" style="  word-wrap: break-word;"><b><?php echo $readData->value;?></b></li>
					  </ul>
					</div>
			    </div>	
			   
		<?php $i++;}}} ?>
		
		<div class="col-lg-12 col-md-12 col-sm-12 text-center" style="padding: 5px;color: #000;    padding: 15px;">
		<b style="color: #fff;background-color: #3f51b5;border:1px solid #3f51b5; padding:5px;border-radius: 5px;font-size: 17px;cursor: pointer;" id="showMint">MINT Details</b>
		</div>
		<div class="col-lg-12 col-md-12 col-sm-12 text-center mint-heading" style="margin-top: 10px;
    background-color: orange;
    margin-bottom: 10px;
    color: #fff;
    padding: 10px;font-size: 17px; display: none;">
			<b>MINT DETAILS</b>
		</div>
		<div class="col-lg-12 col-md-1 col-sm-12 text-center mint-details" style=" display: none;">
			<div class="card" style="margin: auto;">
			  <div class="card-header"  style="  word-wrap: break-word;background-color: #0052cc;color: #fff;">
			    Wallet Address
			  </div>
			  <ul class="list-group list-group-flush">
			    <li class="list-group-item" style="  word-wrap: break-word;"><b><?php echo $data['walletID'];?></b></li>
			  </ul>
			</div>

			<div class="card" style="margin: auto;">
			  <div class="card-header"  style="  word-wrap: break-word;background-color: #0052cc;color: #fff;">
			    Polygon Transaction URL
			  </div>
			  <ul class="list-group list-group-flush">
			    <li class="list-group-item" style="  word-wrap: break-word;text-align: left;"><a href="<?php echo $data['polygonTxnUrl'];?>" target="_blank" title="Click to check on Polygon Network"><b><?php echo $data['polygonTxnUrl'];?></b></a></li>
			  </ul>
			</div>

			<div class="card" style="margin: auto;">
			  <div class="card-header"  style="  word-wrap: break-word;background-color: #0052cc;color: #fff;">
			    Smart Contract Address
			  </div>
			  <ul class="list-group list-group-flush">
			    <li class="list-group-item" style="  word-wrap: break-word;"><b><?php echo $data['contractAddress'];?></b></li>
			  </ul>
			</div>

			<div class="card" style="margin: auto;">
			  <div class="card-header"  style="  word-wrap: break-word;background-color: #0052cc;color: #fff;">
			    Transaction Hash
			  </div>
			  <ul class="list-group list-group-flush">
			    <li class="list-group-item" style="  word-wrap: break-word;"><b><?php echo $data['txnHash'];?></b></li>
			  </ul>
			</div>
			

			
	    </div>
	</div>
	</div>

	<div class="col-lg-8 col-md-8 col-sm-12">
	  	<div id="pdfDiv" class="fade-in-right" style="text-align: center; left: 100;">
		<iframe src="<?php echo $data['pdfUrl']; ?>?page=hsn#toolbar=0" width="810" height="1140"></iframe>
		</div>
	</div>
</div>
@stop
@section('script')
<script>
$('a[data-url^="dashboard"]').parent().addClass('active');
$("#card1").fadeToggle(2000);
$("#card2").fadeIn(2500);
$("#card3").fadeIn(3000);
$("#card4").fadeIn(3500);
$("#card5").fadeIn(4000);
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
@stop
