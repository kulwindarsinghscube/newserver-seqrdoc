<!DOCTYPE html>
<html lang="en-US">
  <head>
    <meta charset="utf-8" />
  </head>
  <body>
    <p>
    Reset Password - Monad University Document Verification Portal.
     Dear  <?php echo e($fullname); ?> 
     <br/><br/> 
    We have receieved your reset password request. Please find your updated password below. To login and submit verification request form. 
    Visit <a href='https://monad.seqrdoc.com/verify/login' target='_blank'>https://monad.seqrdoc.com/verify/login</a>  <br><br/>
    <table style='margin: auto;border: 1px solid #000;border-collapse: collapse;'>
      <tr>
        <td style='border: 1px solid black;padding: 15px;'>Username</td>
        <td style='border: 1px solid black;padding: 15px;'><?php echo e($email_id); ?></td>
      </tr>
      <tr>
        <td style='border: 1px solid black;padding: 15px;'>Password</td>
        <td style='border: 1px solid black;padding: 15px;'><?php echo e($password); ?></td>
      </tr>
    </table>
    <br/><br/>
   
    <!-- Website: <a href='https://ghrce.raisoni.net/' target='_blank'>https://ghrce.raisoni.net/</a> 
    <br/><br/> -->
  </body>
</html>
<?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/verify/reset_password_monad.blade.php ENDPATH**/ ?>