<!DOCTYPE html>
<html lang="en-US">
  <head>
    <meta charset="utf-8" />
  </head>
  <body>
    <p>
    Reset Password - 
    Dear  {{ $user_data['name'] }} 
    <br/><br/> 
        We have receieved your reset password. Please find your updated password below. To login and submit seqr app. 
    <br><br/>
    <table style='margin: auto;border: 1px solid #000;border-collapse: collapse;'>
      <tr>
        <td style='border: 1px solid black;padding: 15px;'>Username</td>
        <td style='border: 1px solid black;padding: 15px;'>{{ $user_data['username'] }}</td>
      </tr>
      <tr>
        <td style='border: 1px solid black;padding: 15px;'>Password</td>
        <td style='border: 1px solid black;padding: 15px;'>{{ $user_data['password'] }}</td>
      </tr>
    </table>
    <br/><br/>
   
  </body>
</html>
