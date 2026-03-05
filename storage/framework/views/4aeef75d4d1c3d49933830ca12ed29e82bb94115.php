<?php $__env->startSection('content'); ?>
<br><br>
<?php $domain =$_SERVER['HTTP_HOST'];
            $subdomain = explode('.', $domain);?>
 <div class="" id="QR-Code">

				<?php	
          		if($subdomain[0] == "galgotias"){?>
          		<div class="col-xs-12 col-md-10 col-lg-8" style="background-color: #fff;color: #890c0e;padding: 30px;border-radius: 5px;text-align: justify;font-size: 17px;background-repeat:   no-repeat;background-size:     cover;background-position: center center;   ">
				<div class="col-md-0 helper" >
				<?php
            	echo "Galgotia University Noida";
            	}else if($subdomain[0] == "monad"){
     	       	?>
     	       	<div class="col-xs-12 col-md-10 col-lg-12" style="color: #890c0e;padding: 30px;border-radius: 5px;text-align: justify;font-size: 17px;background-repeat:   no-repeat;background-size:     cover;background-position: center center;   ">
				<div class="col-md-0 helper" style="margin: auto;max-width: 1024px;background-color: #fff;padding: 50px;">
				<img src="../backend/images/monad_logo.png" style="margin-bottom:10px;">
				<!-- <img src="../backend/images/verify1.png" style="float: right;"> -->
				<img src="../backend/images/Certificate-Verification.jpg" style="width: 100%;">

            	<?php

            	}else{

            	?>
            	<div class="col-xs-12 col-md-10 col-lg-8" style="background-color: #fff;color: #890c0e;padding: 30px;border-radius: 5px;text-align: justify;font-size: 17px;background-repeat:   no-repeat;background-size:     cover;background-position: center center;   ">
				<div class="col-md-0 helper" >
            	<?php
            	echo "<p>Established in 1996, G H Raisoni College of Engineering [GHRCE] is a premier Autonomous institution in the central India imparting a holistic technical education to the students residing not only in India but also international students. The institution has always been ranked amongst a well performing institution by National Institutional Ranking Framework (NIRF) 2017, MHRD, Government of India.</p>

<p>Currently GHRCE is ranked 139th PAN-India in Engineering Discipline as declared by NIRF Ranking 2020. The institution has also been ranked prominently by India Today MDRA Survey and Atal Ranking of Institutions on Innovation Achievements [ARIIA]. 2nd Rank in India at ARIIA-2020 among private / Self Financed College / institutions.</p>

<p>GHRCE has been ranked under Platinum category for Best Industry linked institution by AICTE-CII Survey since last three years. Since 2015, institute has been ranked consistently among the top 10 patent filers as per the Indian Patent Office Report. GHRCE was selected as one of the youngest institution for implementing the Technical Education Quality Improvement Program of MHRD with assistance of World Bank.</p>

<p>World Bank and MHRD appreciated the initiatives undertaken for improving transition rate and had invited institute to present the case study for guiding 06 states in the country. Memberships of Profession Societies like ASME, IEEE, CSI, SAE, ICI, AWWA, SESI, IETE and ISHRAE help the institute in institutional technical upgradation.</p>

<p>The success story of GHRCE is due to implementation of various Quality Assurance initiatives practiced at the institution. The institution has an Internal Quality Assurance Cell [IQAC] that takes care of all academic activities on campus. Internal and external academic audits are a part of the institutional system along with a formal 360 degrees feedback system from all stakeholders for continuous improvement. The result of all quality initiatives are visible through achievement of accreditations by various accreditation agencies like National Board of Accreditation [NBA] and National Assessment & Accreditation Council [NAAC]. The institution is graded A+ Rank by NAAC and almost all programs are Tier-I accredited by NBA.</p>

<p>At GHRCE, Choice Based Credit System (CBCS) is offered to the students. Reforms like Credit Transfer Scheme [CTS] with VJTI Mumbai, College of Engineering, Pune and IIT Gandhi Nagar, modern teaching learning practices with blended MOOCS, Industrial Electives in curriculum are regularly practiced at the institution. To make the students industry ready, the Institute has included 6 months mandatory industry internship in the course. Students undertake internship in 300 plus companies located all over the country and abroad.</p>

<p>MoUs with industries and Academic institutions of repute helps GHRCE for adopting various best and latest practices followed by them. The beneficiaries of these MoUs lead towards improvement in teaching learning practices through trainings for faculty members as well as students. The institution has a formal Industry Advisory Board [IAB] for imparting the currently used technology in industry. Many leading MNCs like Mahindra & Mahindra, Yamaha, Intel, Cranes, Texas Instruments, Xilinx and National Instruments have established their laboratories in the campus.</p>

<p>The Institute has setup Incubation Centre in the premises to create eco-system for entrepreneurship and has received NIDHI-TBI, a project worth 14 Crore from DST. GHRCE has received number of Sponsored research projects amounting to more than INR 10 crores from DST, AICTE, IEI, UGC, TEQIP-II etc. since inception.</p>

<p>The Placements of GHRCE are almost 100%. Mercedes Benz, Amazon, TCS, Tech Mahindra, IBM, Cybage, Capgemini, Godrej & Boyce are the major recruiters. The Institute has strong alumni network spread across the globe. The Institute has NSS unit which conducts lot of community services for societal development. Students regularly represent the institution as Color holders at various National & International Sport events.</p>

<p>We work on a mission to achieve Our Vision to achieve excellent standards of quality education by keeping pace with rapidly changing technologies and to create technical manpower of global standards with capabilities of accepting new challenge.s.</p>";
            }


            ?>

		</div>



	</div>
</div>
</div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('style'); ?>

<link rel="stylesheet" href="<?php echo e(asset('backend/css/webcamjs_style.css')); ?>">
<?php 
	if($subdomain[0] == "galgotias"){

	}else if($subdomain[0] == "monad"){
 	?>
 		<style>
			body{
			padding:0;
			background-image:  url("../backend/images/monad-university_bg1.jpg");
			}
		</style>

 	<?php }else{ ?>
		<style>
		body{
			padding:0;
			background-image:  url("../backend/images/home.png");
		}
		</style>
<?php }  ?>


<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>
<script src="<?php echo e(asset('webcamjs/js/filereader.js')); ?>"></script>
<script src="<?php echo e(asset('webcamjs/js/qrcodelib.js')); ?>"></script>
<script src="<?php echo e(asset('webcamjs/js/webcodecamjs.js')); ?>"></script>
<script src="<?php echo e(asset('webcamjs/js/main.js')); ?>"></script>
<script>
var key = '';
var pay = '';

$(window).focusin(function(){
	if(key != '' && pay !=''){
		ajaxCall(key,'1');
	}else{
		return false;
	}
});

$(window).focusout(function(){
	pay = '1';
});

function load(){
	// console.log('load');
	$('.payment-url').click(function(e){
		e.preventDefault();
		$url = $(this).attr('href');
		window.open($url,'_blank');
		pay = '1';
	});
}

$('#play,#stop').click(function(){
	if (navigator.userAgent.match(/chrome/i))
	{
		// bootbox.alert('Scaning by web-cam will work only on Mozilla Firefox currently. So open the website on Firefox browser. You can download it <a target="_blank" href="https://www.mozilla.org/en-US/firefox/new/">here</a> if you dont have the browser.');
	}
	else
	{
	$('#qrcode').fadeOut();
	$('.phonebg').addClass('col-md-offset-0');
	$('.thumbnail').addClass('hidden');
	$('.helper').removeClass('hidden');
	key ='';
	pay = '';
	}
});

function ajaxCall(val,update='0'){
	if(sessionStorage.getItem('qrCodeKey') != null && sessionStorage.getItem('qrCodeKey') != 'null'){
		sessionStorage.removeItem('qrCodeKey')
	}

	key = val;
	$('#scanned-QR').text('success');
	$('#response-QR').text(key);
	$('#qrcode').removeClass('hidden');
	$('#qrcode').fadeIn();
	
	var key = key;
	var token = "<?php echo e(csrf_token()); ?>";
	var action = 'scanData';
	$.ajax({
        url: "<?php echo e(URL::route('raisoni.store')); ?>",
        type: 'post',
        data:{
        	action:action,
            key:key,
            _token:token,
            update_scan : update
        },
        success: function(data) {
			$('#QR-Code').show()
        	console.log(data);
        	$('.ajaxResponse').html(data);
			$('.phonebg').removeClass('col-md-offset-0');
			//$('.loader').removeClass('hidden');
			$('.thumbnail').removeClass('hidden');
			$('.thumbnail').removeClass('col-md-6');
			$('.thumbnail').removeClass('col-md-offset-1');
			$('.thumbnail').addClass('col-md-12');
			$('.phonebg').addClass('hidden');
			$('.helper').addClass('hidden');
			pay ='';
			load();
        }
    });
    return;
}



$('a[data-url^="home"]').parent().addClass('active');

</script>
<?php $__env->stopSection(); ?>
<style>
.phonebg{
	background:url("../assets/images/phonebg2.png");
	padding:25px 25px;
	background-repeat:no-repeat;
	background-position:center center;
	height:766px;
	padding-top:105px;
}

.alert-default{
	background:linear-gradient(to bottom, rgba(255, 255, 255, 0.8), rgba(255,255,255, 0.8));
	border:0px;
}

.thumbnail{
	border:0;
	background:transparent;
}
</style>
<?php echo $__env->make('verify.layout.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/verify/home.blade.php ENDPATH**/ ?>