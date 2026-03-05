<?php 
$arr = $logged_in_user_name; 
$splitStr=explode(" ",$arr);
$first_char=$splitStr[0][0];
if(!empty($splitStr[1])){
$second_char=$splitStr[1][0];
}else{$second_char='';}
?>        
        <div class="col-md-4 pull-left">
            <img src="images/logo.png" class="logo" style="margin-top: 0 !important;margin-left: 0 !important;border-radius:0;">
            <!--<span style="font-size:30px;cursor:pointer;color:#684791;" onclick="openNav()">&#9776;</span>-->
            <span class="shortname" onclick="openNav()">
            <div id="initials" data-letters="<?php echo strtoupper($first_char).strtoupper($second_char); ?>"></div>
            <div style="float:left; position:relative; left:25%; top:-32%; font-size:26px;cursor:pointer;color:#684791;">&#9776;</div>
            </span>
        </div>  
        
<script>
var initials = document.getElementById('initials');
var intletter = initials.getAttribute('data-letters'); 
$('.shortname').prepend('<div class="my-circle"  style="margin-top: -20px;margin-left: 10px;">' + intletter + '</div>');
</script>        