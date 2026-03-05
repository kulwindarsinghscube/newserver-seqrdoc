
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title><?php echo e($sitename); ?> SeQR</title>
    <link rel="icon" type="image/png" href="assets/images/fav.png">
    <!-- Bootstrap Core CSS -->
    <link href="<?php echo e(asset('/backend/css/bootstrap.min.css')); ?>" rel="stylesheet" type="text/css" />

    <!-- MetisMenu CSS -->
    <link href="<?php echo e(asset('/backend/css/metisMenu.min.css')); ?>" rel="stylesheet" type="text/css" />
   
   <!-- Animate CSS -->
    <link href="<?php echo e(asset('/backend/css/animate.css')); ?>" rel="stylesheet" type="text/css" />

    <!-- Custom CSS -->
    <link href="<?php echo e(asset('/backend/css/sb-admin-2.css')); ?>" rel="stylesheet" type="text/css" />

    <!-- Custom Fonts -->
    <link href="<?php echo e(asset('/backend/css/font-awesome.min.css')); ?>" rel="stylesheet" type="text/css" />
    
   
    <link href="<?php echo e(asset('/backend/css/AbelRoboto.css')); ?>" rel="stylesheet" type="text/css" />
</head>

<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-xs-12">
            <div class="pull-left">
                <h2 style="font-family: 'Abel', sans-serif; color:#fff;"><?php echo e($sitename); ?> SeQR Web App</h2>
            </div>
            <div class="pull-right">
            <h2>
                <?php
                if(isset($site_data['apple_app_url'])){
                ?>
                <a href="<?php echo e($site_data['apple_app_url']); ?>" target="_blank"><img src="<?php echo e(asset('/webapp/images/store.png')); ?>" /></a>
                <?php } ?>

                <?php
                if(isset($site_data['android_app_url'])){
                ?>
                <a href="<?php echo e($site_data['android_app_url']); ?>" target="_blank"><img src="<?php echo e(asset('/webapp/images/gplay.png')); ?>"/></a>
                <?php } ?>
            </h2>
            </div>
        </div>
    </div>
    <?php if($isVerified!=1){ ?>
    <div class="row">
        <div class="login-bg clearfix" style="width: 400px;">
            <div class="title-hdg">
                <div class="row">
                    <div class="col-xs-12">
                       <h4 style="font-weight: 600;"><i class="fa fa-certificate"></i> <span id="modalTitle">Waiting for email verification...</span></h4>
                    </div>
                  
                </div>
            </div>
           
            <div class="tab-content">
              <div id="login-holder" class="tab-pane fade in active">
                <p>
                  
                        <?php echo csrf_field(); ?>
                        <fieldset>
                            <h5>Verification link has been sent on <strong><?php echo e($email); ?></strong></h5> 
                            <span class="will-close">If you dont receive verification link please wait. Resend verification link option will appear after : <strong>n</strong> seconds</span>
                            <span id="credential" class="text-danger"></span>
                            <!-- Change this to a button or input when using this as a form -->
                         <button type="submit" id="resendVerification" class="btn btn-sm btn-block"><i class="fa fa-paper-plane"></i> Resend Verification Link</button>
                            
                        </fieldset>
                    <!-- </form> -->
                </p>
              </div>
             <div id="success-holder" class="tab-pane fade in ">
                <p>
                  
                      
                        <fieldset>
                            <h5><i class="fa fa-check-circle green"></i> Congrats! Your account has been verified successfully. <a href="<?php echo e(URL::route('webapp.index')); ?>">Click here</a> for login.</h5>
                            
                        </fieldset>
                    <!-- </form> -->
                </p>
              </div>   
            </div>
        </div>
    </div>
    <button type="btn" id="checkVerificationStatus" class="btn btn-sm btn-block" style="visibility: hidden;">Check Verification Status</button>
<?php }else{ ?>

<div class="row">
        <div class="login-bg clearfix" style="width: 400px;">
            <div class="title-hdg">
                <div class="row">
                    <div class="col-xs-12">
                       <h4 style="font-weight: 600;"><i class="fa fa-certificate"></i> Verification Success!</h4>
                    </div>
                  
                </div>
            </div>
           
            <div class="tab-content">
              
             <div id="success-holder" class="tab-pane fade in active">
                <p>
                  
                      
                        <fieldset>
                            <h5><i class="fa fa-check-circle green"></i> Congrats! Your account has been verified successfully. <a href="<?php echo e(URL::route('webapp.index')); ?>">Click here</a> for login.</h5>
                            
                        </fieldset>
                    <!-- </form> -->
                </p>
              </div>   
            </div>
        </div>
    </div>
<?php } ?>

</div>
<!-- jQuery -->
<script src="<?php echo e(asset('/backend/js/jquery.min.js')); ?>"></script>

<!-- Bootstrap Core JavaScript -->
<script src="<?php echo e(asset('/backend/js/bootstrap.min.js')); ?>"></script>

<!-- Metis Menu Plugin JavaScript -->
<script src="<?php echo e(asset('/backend/js/metisMenu.min.js')); ?>"></script>

<!-- Custom Theme JavaScript -->
<script src="<?php echo e(asset('/backend/js/sb-admin2.js')); ?>"></script>
<!-- ToastrJS -->
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js" type="text/javascript"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" rel="stylesheet"  type="text/css"> -->
<!-- JQuery Validation -->
<link rel="stylesheet" href="<?php echo e(asset('/backend/css/toastr.min.css')); ?>" rel="stylesheet" type="text/css" />
<script src="<?php echo e(asset('/backend/js/toastr.min.js')); ?>"></script> 
<script src="<?php echo e(asset('/backend/js/jquery.mockjax.js')); ?>"></script>
<script src="<?php echo e(asset('/backend/js/jquery.form.js')); ?>"></script>
<script src="<?php echo e(asset('/backend/js/jquery.validate.js')); ?>"></script>
</body>

</html>
<style>
html,body{
    height:100%;
    width:100%;
}

body{
    background: #0052CC;
    color:#222;
    background-size:cover;
    background-attachment:fixed;
}

#resendVerification{
    background:#0052CC !important;
    border:1px solid #0052CC;
    border-radius:4px;
    margin-top:30px !important;
    color:#fff;
    text-transform:uppercase;
    font-size:14px;
    font-weight:700;
    padding:15px 0;
}

#resendVerification:hover{
    border-color:#0747A6;
    background:#0747A6 !important;
}

*{
    transition:all ease 0.5s;
}

.footer-links{
    color:#fff;
    position:fixed;
    bottom:5px;
    left:0;
    right:0;
    text-align:center;
    font-size:11px;
}

.login-bg{
    background:#fff;
    padding:0 25px 25px 25px;
    border-radius:4px;
    width:390px;
}

.title-hdg{
    margin-top:10px;
    margin-bottom:10px;
    border-bottom:1px solid #ececec;
    padding:0px 5px ;
}

.login-bg .nav-pills>li.active>a, .nav-pills>li.active>a:focus, .nav-pills>li.active>a:hover, .nav>li>a
.login-bg .nav-pills>li>a, .nav-pills>li>a:focus, .nav-pills>li>a:hover, .nav>li>a
{
    background:#fff;
    border:0px solid #fff;
    color:#0052CC;
    border-bottom:2px solid #0052CC;
    border-radius:0;
    padding:5px 15px;
}

.nav>li>a{
    border:0px solid #fff;
    border-bottom:2px solid #fff;   
}

.signupform label,
.loginform label{
    color:#5c5c5c;
}

.help-inline{
    color: #F44336;
    display: inline-block;
    font-size: 12px;
    position: absolute;
    top: 4px;
    right: 0;
    border-radius: 4px
}

.form-group{
    position:relative;
}

.signupform sup{
    color:red;
}

.viewpass{
    position:absolute;
    bottom:10px;
    right:10px;
    cursor:pointer;
}
</style>
<script>
$(document).ready(function(){
    alignMiddle();

    $('.nav-pills li a').click(function(){
        setTimeout(function(){alignMiddle();},500);
    });

});

function alignMiddle(){
    $('.login-bg').css({
        'position' : 'absolute',
        'left' : '50%',
        'top' : '50%',
        'margin-left' : -$('.login-bg').outerWidth()/2,
        'margin-top' : -$('.login-bg').outerHeight()/2
    });
}


</script>
<script>
/*$(function() {*/
 var timerStatus;
 function showTimer(){

  var verificationLink = $('#resendVerification');
  var time = $(".will-close strong");
  var timerSection = $('.will-close');
  var closeSeconds = 60;
  var openSeconds = 0;
  
  setTimeout(function(e) {
    timerSection.show();
    verificationLink.hide();
    time.html(closeSeconds);
    
    var interval = setInterval(function(){
      time.html(closeSeconds);
      closeSeconds--;
      
      if(closeSeconds < 0){
        timerSection.hide();
        verificationLink.show();
        clearInterval(interval);
      }
      
    }, 1000)
    
  }, openSeconds * 1000);
}

   <?php if($isVerified!=1){ ?>
showTimer();
   <?php } ?>
/*});*/
</script>
<script>
$(document).ready(function(){
    $('[data-toggle="tooltip"]').tooltip(); 
    //login ajax
    $("#resendVerification").click(function(e){

        e.preventDefault();     
        var login_url="<?php echo e(URL::route('webapp.resendverificationlink')); ?>";
        var method_type="post";
        var token="<?php echo e(csrf_token()); ?>";

        $.ajax({
              url:login_url,
              type:method_type,
              data:{'_token':token,'email_id':'<?php echo e($email); ?>'},

              success:function(data)
              {
                if(data.status==200)
                {
                   toastr.success(data.message);    
                   showTimer();
                }else if(data.status==201)
                {   clearInterval(timerStatus); 
                    $('#login-holder').hide();
                     $('#success-holder').show();
                     $('#modalTitle').html('Verification Success!');
                   toastr.success(data.message);    
                } 
                else if(data.status==405)
                {
                   toastr.error(data.message);    
                }
              },
              error:function(resobj)
              {

                toastr.error('Something are wrong');
                 $.each(resobj.responseJSON.errors,function(k,v){
                   
                   $('#'+k+'_errors').text(v);
                 });
              }            
        });     
    });
    
    $("#checkVerificationStatus").click(function(e){

        e.preventDefault();     
        var login_url="<?php echo e(URL::route('webapp.checkverificationstatus')); ?>";
        var method_type="post";
        var token="<?php echo e(csrf_token()); ?>";

        $.ajax({
              url:login_url,
              type:method_type,
              data:{'_token':token,'email_id':'<?php echo e($email); ?>'},

              success:function(data)
              {
                if(data.status==200)
                {
                     clearInterval(timerStatus); 
                   $('#login-holder').hide();
                     $('#success-holder').show();
                     $('#modalTitle').html('Verification Success!');
                   toastr.success(data.message);   
                }
              },
              error:function(resobj)
              {

                toastr.error('Something are wrong');
                 $.each(resobj.responseJSON.errors,function(k,v){
                   
                   $('#'+k+'_errors').text(v);
                 });
              }            
        });     
    });
    
    
});

timerStatus = setInterval(function() { 
            $('#checkVerificationStatus').click();
            }, 10000);
</script>



<?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/auth/verify.blade.php ENDPATH**/ ?>