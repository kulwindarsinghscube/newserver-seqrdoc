<!DOCTYPE html>
<html lang="en-US">
  <head>
    <meta charset="utf-8" />
  </head>
  <body>
    <p>
    Welcome to Galgotias Document Verification Portal.
    <br>
     Dear  {{ $fullname }} 
     <br/><br/> 
    Thank you for registering to Galgotias Secure Document Verification Portal. Your account is verified now. To verify your account please enter below OTP.<br><br/>
    <table style='margin: auto;border: 1px solid #000;border-collapse: collapse;'>
      <tr>
        <td style='border: 1px solid black;padding: 15px;'>OTP</td>
        <td style='border: 1px solid black;padding: 15px;'>{{ $OTP }}</td>
      </tr>
      <tr>
    </table>
    <br/><br/>
    <br/><br/>
    <br/><br/>
  </body>
</html>
