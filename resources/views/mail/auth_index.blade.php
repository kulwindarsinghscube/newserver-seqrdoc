<div>
Dear {{ucfirst($user_data['name'])}},<br/><br/>

<p style="margin: 0;">Forgot your password?</p>
<p style="margin: 0;">We received a request to reset the password for your account</p>
<p>To reset your password, click on the button below</p>
<a style="background-color: #337ab7;color: #fff;padding: 10px 15px;" href="{{ URL('/auth/password_reset/'.$user_data['token'] )}}">Reset Password</a>
<p>Or Copy and paste the URL into your browser :</p>
<a sty href="{{ URL('/auth/password_reset/'.$user_data['token'] )}}">{{ URL('/auth/password_reset/'.$user_data['token'] )}}</a>
<br><br/>
{{-- <a href="{{ URL('/auth/password_reset/'.$user_data['token'] )}}">Click here</a> to verify your email ID in order to activate the account on SeQR Mobile app <br><br/> --}}

Thanks & Regards,<br/>

</div>