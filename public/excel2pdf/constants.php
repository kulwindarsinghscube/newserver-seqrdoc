<?php
// $servername = "seqrdoc.com";
// $username = "developer";
// $password = "developer";

$servername = "localhost";
$username = "root";
$password = "";


$host=$_SERVER['HTTP_HOST'];
$domain=explode('.', $host);

if($domain[0]=='demo'){
    $dbName = 'seqr_demo';
}else{
    $dbName = 'seqr_d_'.$domain[0];
}
$dbName = 'seqr_demo';
$subdomain=$domain[0];
define("servername",$servername);
define("username",$username);
define("password",$password);
define("dbName",$dbName);
define("subdomain",$subdomain);
// define("directoryUrlForward","C:/Inetpub/vhosts/seqrdoc.com/httpdocs/demo/public/pdf2pdf/");
// define("directoryUrlBackward","C:\\Inetpub\\vhosts\\seqrdoc.com\\httpdocs\\demo\\public\\pdf2pdf\\");

define("directoryUrlForward","C:/wamp64/www/uneb/public/pdf2pdf/");
define("directoryUrlBackward","C:\\wamp64\\www\\uneb\\public\\pdf2pdf\\");

?>