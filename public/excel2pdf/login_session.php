<?php
//session_start();

//print_r($_SESSION);
/*if(empty($_SESSION['user_id']))
{
    header("Location: login.php");
}
define("alert_msg", [
  "Do not have permission to view.",
  "Do not have permission to add.",
  "Do not have permission to edit.",
  "Do not have permission to delete.",
  "Do not have permission to make a duplicate template.",
  "Do not have permission to assign permissions."
]);*/
//$_SESSION['user_id']=1;

/*$data=array();
 $url="http://bmcc.seqrdoclocal.com/session-data";
$curl = curl_init();


   
     curl_setopt($curl, CURLOPT_POST, 1);
      curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    $result = curl_exec($curl);

    curl_close($curl);
    print_r($result);*/



              /*$reaquestParameters = array
                (
                    'accesstoken' => 'SESSIONAPI',
                );*/
 /*
                $headers = array
                    (
                    'Authorization: key=SEQRDOC',
                    'Content-Type: application/json',
                    'apikey: '.apiKey,
                    'accesstoken: '.$accessToken,
                );*/

               // $url = "http://".$_SERVER['HTTP_HOST']."/session-data";
               /* $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, true);
               // curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                //curl_setopt($ch, CURLOPT_TIMEOUT, 2);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($reaquestParameters));
              //  exit;
                $result = curl_exec($ch);
            print_r($result);
                curl_close($ch);*/
   /*             $ch = curl_init();

curl_setopt($ch, CURLOPT_URL,$url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS,
            "postvar1=value1&postvar2=value2&postvar3=value3");

// In real life you should use something like:
// curl_setopt($ch, CURLOPT_POSTFIELDS, 
//          http_build_query(array('postvar1' => 'value1')));

// Receive server response ...
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$server_output = curl_exec($ch);
print_r($server_output);
curl_close ($ch);*/


// Further processing ...
//if ($server_output == "OK") { echo "string"; } else { echo "string1"; }
              //  exit;
               // echo $_SESSION['CKFinder_UserRole'];

           /*     $response = file_get_contents( "http://".$_SERVER['HTTP_HOST']."/session-data");
                $response = json_decode($response);
                print_r($response);

                exit;*/

    /*            require getcwd() . '/../../vendor/autoload.php';
                       //echo  getcwd() . '/../../config/auth.php';

                        $Auth= require getcwd() . '/../../config/auth.php';
    $app = require_once getcwd() . '/../../bootstrap/app.php';
use Illuminate\Support\Facades\Auth;
    $kernel = $app->make('Illuminate\Contracts\Http\Kernel');

    $response = $kernel->handle(
        $request = Illuminate\Http\Request::capture()
    );

//print_r($Auth['guards']['admin']->user()->id);
//echo $_SERVER['REMOTE_ADDR'];
    //echo $_SERVER['HTTP_HOST'];

if(Auth::guard('admin')->user()){
	 echo $logged_in_user_id = Auth::guard('admin')->user()->id;
	 echo $logged_in_user_name = Auth::guard('admin')->user()->username;
}else{
	$logged_in_user_id=0;
echo 'Access Denide';
exit;
}*/

    // $id = $app['encrypter']->decrypt($_COOKIE[$app['config']['session.cookie']]);

    // print_r($id);
    /*$app['session']->driver()->setId($id);
    $app['session']->driver()->start();
    */
//echo $_SESSION['CKFinder_UserRole'];
    // print_r($_COOKIE[$app['config']['session.cookie']]);
/*
    if(!$app['auth']->check()){
    	echo 'false';
    }else{
    	echo 'true';  
    }  */   


   /*  $status=Session::get('session_id');
      print_r($status);
      $session_val = $request->session()->get('session_id');
      if($session_val){
        return  response()->json(['success'=>true,'session_id'=>$session_val]);
      }else{
        return  response()->json(['success'=>false]);
      }*/
//echo getcwd();
     require('connection.php');
     $sql_p="SELECT * FROM `sites` WHERE site_url='".$_SERVER['HTTP_HOST']."';";		
    $query_p = $GLOBALS['conn']->prepare($sql_p);	
    $query_p->execute();  
    $row = $query_p->fetch();

    if($row){
    	$site_id=$row['site_id'];
    	 $sql_p2="SELECT a.* FROM `session_manager` as s INNER JOIN admin_table as a ON a.id=s.user_id WHERE s.ip='".$_SERVER['REMOTE_ADDR']."' AND s.site_id='".$site_id."' AND s.device_type='webAdmin' AND s.is_logged='1' ORDER BY s.login_time DESC;";		
	    $query_p2 = $GLOBALS['conn']->prepare($sql_p2);	
	    $query_p2->execute();  
	    $row2 = $query_p2->fetch();
	    if($row2){
	    	 $logged_in_user_id = $row2['id'];
	 		   $logged_in_user_name =$row2['username'];
         $logged_in_user_site_id = $row['site_id'];
	    }else{
	    	echo 'Access Denide';
		exit;
	    }
    }else{
		echo 'Access Denide';
		exit;
    }

?>