<!DOCTYPE html>
<html lang="en-US">
  <head>
    <meta charset="utf-8" />
  </head>
  <body>
    <p>
    Welcome to Raisoni Document Verification Portal.
    <br>
     Dear  {{ $fullname }} 
     <br/><br/> 
    Thank you for registering to Raisoni Secure Document Verification Portal. To verify your account please enter below OTP.<br><br/>
    <table style='margin: auto;border: 1px solid #000;border-collapse: collapse;'>
      <tr>
        <td style='border: 1px solid black;padding: 15px;'>OTP</td>
        <td style='border: 1px solid black;padding: 15px;'>{{ $OTP }}</td>
      </tr>
    </table>
    <br/><br/>
    
  </body>
</html>
