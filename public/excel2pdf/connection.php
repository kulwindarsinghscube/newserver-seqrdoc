<?php

require('constants.php');
$servername = servername;
$username = username;
$password =password;
$dbName = dbName;

try {
	$conn = new PDO("mysql:host=$servername;dbname=$dbName;charset=utf8", $username, $password);
	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	//$conn->setAttribute(PDO::SQLSRV_ATTR_ENCODING, PDO::SQLSRV_ENCODING_UTF8);
	//echo "Connected successfully";
}
catch(PDOException $e)
{
	//echo "Connection failed: " . $e->getMessage();
	$result = json_encode(array('type'=>'error','message'=>'Connection failed'));
	echo $result;
	exit;
}
$GLOBALS['conn'] = $conn;
function checkPermissions($key){
    $role_id=$_SESSION['role_id'];		
    $sql_p="SELECT count(*) FROM `user_role_permissions` WHERE role_id=".$role_id." and permission_keys REGEXP '[[:<:]]".$key."[[:>:]]';";		
    $query_p = $GLOBALS['conn']->prepare($sql_p);	
    $query_p->execute();        
    if ($query_p->fetchColumn() > 0){
        return 1;
    }else{
        return 0;
    }		
}
?>