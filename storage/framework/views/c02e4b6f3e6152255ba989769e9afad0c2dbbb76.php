<div>
Dear <?php echo e(ucfirst($user_data['name'])); ?>,<br/><br/>

<p style="margin: 0;">Forgot your password?</p>
<p style="margin: 0;">We received a request to reset the password for your account</p>
<p>To reset your password, click on the button below</p>
<a style="background-color: #337ab7;color: #fff;padding: 10px 15px;" href="<?php echo e(URL('/auth/password_reset/'.$user_data['token'] )); ?>">Reset Password</a>
<p>Or Copy and paste the URL into your browser :</p>
<a sty href="<?php echo e(URL('/auth/password_reset/'.$user_data['token'] )); ?>"><?php echo e(URL('/auth/password_reset/'.$user_data['token'] )); ?></a>
<br><br/>


Thanks & Regards,<br/>

</div><?php /**PATH C:\inetpub\wwwroot\demo\resources\views/mail/auth_index.blade.php ENDPATH**/ ?>