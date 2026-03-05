<?php
set_time_limit(0);
require('login_session.php');
date_default_timezone_set('Asia/Kolkata');
//require('connection.php'); 
$user_id=$logged_in_user_id;
$template_id = $_POST['template_id'];
$pdf_page = $_POST['pdf_page'];
$filename = $_POST['file'];
$progressfile = $_POST['progressfile'];
$unids = $_POST['unids'];
$unids_arr = explode (",", $unids); 
$total_count=count($unids_arr);
$entry_type="Proceed";
$enter_date = date('y-m-d h:i:s');
$publish=2;
$data = [
    'filename' => $filename,
    'template_id' => $template_id,
    'pdf_page' => $pdf_page,
    'unids' => $unids,
    'total_count' => $total_count,
    'user_id' => $user_id,
    'created_at' => $enter_date
];
$col="";
$v="";
foreach ($data as $key => $value)
{
    $col .=$key.",";
    $v .=":".$key.",";
}
$fields=substr($col, 0, -1);
$vs=substr($v, 0, -1);
$response = 0;
if($pdf_page=="Single"){
    $sql = "INSERT INTO duplicate_records ($fields) VALUES ($vs)";
    try {                
        $conn->beginTransaction();
        $conn->prepare($sql)->execute($data);
    }catch (Exception $e){
        $conn->rollback();
        //$result = json_encode(array('type'=>'error','message'=>'Failed to submit the form. '.$e->getMessage( )));
        exit;
    }    
    $sql_update = $conn->prepare("UPDATE individual_records SET publish = ? WHERE unique_no=? and publish != 0");
    foreach ($unids_arr as $unid) {  
      $sql_update->execute([$publish,$unid]);      
    }    
    $conn->commit();
    $location = 'uploads/data/'.$filename;
    $py_location = $filename;
    $pyscript = directoryUrlBackward."Python_files\\extract_and_place.py";
    //$pyscript = "C:\\Program^ Files\\Python38\\projects\\demo\\extract_and_place.py";
    $cmd = "$pyscript $template_id $py_location $user_id $entry_type $progressfile ".dbName." ".subdomain." ".directoryUrlForward." ".directoryUrlBackward." ".servername." ".username." ".password." $logged_in_user_site_id 2>&1"; 
    exec($cmd, $output, $return);
    #print_r($output);
    $response = json_encode(array('type'=>'Success', 'dlink'=>end($output)));
}else{
    $sql = "INSERT INTO duplicate_records ($fields) VALUES ($vs)";
    try {                
        $conn->beginTransaction();
        $conn->prepare($sql)->execute($data);
    }catch (Exception $e){
        $conn->rollback();
        //$result = json_encode(array('type'=>'error','message'=>'Failed to submit the form. '.$e->getMessage( )));
        exit;
    }    
    $sql_update = $conn->prepare("UPDATE individual_records SET publish = ? WHERE unique_no=? and publish != 0");
    foreach ($unids_arr as $unid) {  
      $sql_update->execute([$publish,$unid]);      
    }    
    $conn->commit();    
    $folder = $_POST['folder'];
    $pyscript = directoryUrlBackward."Python_files\\directory_extract_and_place_QR.py"; 
    //$pyscript = "C:\\Program^ Files\\Python38\\projects\\demo\\directory_extract_and_place_QR.py";
    $cmd = "$pyscript $template_id $folder $user_id $entry_type $progressfile ".dbName." ".subdomain." ".directoryUrlForward." ".directoryUrlBackward." ".servername." ".username." ".password." $logged_in_user_site_id 2>&1";
    exec($cmd, $output, $return);
    //print_r($output);
    $response = json_encode(array('type'=>'Success', 'dlink'=>end($output)));
}
echo $response;
