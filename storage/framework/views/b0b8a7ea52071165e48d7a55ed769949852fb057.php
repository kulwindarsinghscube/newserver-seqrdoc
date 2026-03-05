<!DOCTYPE html>
<html lang="en-US">
  <head>
    <meta charset="utf-8" />
  </head>
  <body>
    <p>
    Welcome to Monad University Document Verification Portal.
    <br>
     Dear  <?php echo e($fullname); ?> 
     <br/><br/> 
    Thank you for registering to Monad University Secure Document Verification Portal. Your account is verified now. To login and submit verification request form. Visit <a href='https://monad.seqrdoc.com/verify/login' target='_blank'>https://monad.seqrdoc.com/verify/login</a>  <br><br/>
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
    <br/><br/>
    <br/><br/>
  </body>
</html>
<?php /**PATH C:\inetpub\vhosts\seqrdoc.com\httpdocs\demo\resources\views/verify/login_mail_monad.blade.php ENDPATH**/ ?>