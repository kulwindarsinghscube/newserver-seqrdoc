<?php
set_time_limit(0);
require('login_session.php');

$user_id=$logged_in_user_id;
$template_id = $_POST['template_id'];
$pdf_page = $_POST['pdf_page'];
$entry_type="Fresh";
$response = 0;
$progressfile=$_POST['progress_file'];
//fopen("processed_pdfs/".$progressfile, "w");
if($pdf_page=="Single"){
    // file name
    $filename = $_FILES['file']['name'];
    $temps = explode(".", $filename);
    $filename = str_replace(' ', '_', $temps[0])."_".round(microtime(true)) . '.' . end($temps);
    // Location
    $location = 'uploads/data/'.$filename;
    $py_location = $filename;
    // file extension
    $file_extension = pathinfo($location, PATHINFO_EXTENSION);
    $file_extension = strtolower($file_extension);
    // Valid image extensions
    $image_ext = array("pdf");   
    if(in_array($file_extension,$image_ext)){
      // Upload file
      if(move_uploaded_file($_FILES['file']['tmp_name'],$location)){
        $pyscript = directoryUrlBackward."Python_files\\extract_and_place.py";
        //$pyscript = "C:\\Program^ Files\\Python38\\projects\\demo\\extract_and_place.py";
        $cmd = "$pyscript $template_id $py_location $user_id $entry_type $progressfile ".dbName." ".subdomain." ".directoryUrlForward." ".directoryUrlBackward." ".servername." ".username." ".password." $logged_in_user_site_id 2>&1"; 
        //print($cmd);
        exec($cmd, $output, $return);
        //print_r($output);
        if(array_values($output)[0]=='Duplicates'){
            $count=array_values($output)[1];
            $unids=array_values($output)[2];
            $response = json_encode(array('type'=>'Duplicates', 'msg'=>$count.' record(s) are already existed in DB.<br />Please confirm to generate these and inactivate the old ones.', 'filename'=>$filename, 'template_id'=>$template_id, 'pdf_page'=>$pdf_page, 'unids'=>$unids, 'progressfile'=>$progressfile));
        }else{
            $response = json_encode(array('type'=>'Success', 'dlink'=>end($output)));
        }
      }
    }
}else{
    $folder_name = $_POST['folder_name'];
    if($folder_name == "multi_pages"){ $folder="multi_pages"; }
    else{        
        $f=round(microtime(true));
        mkdir("multi_pages/".$f);    
        $folder="multi_pages/".$f;
        foreach($_FILES['files']['name'] as $i => $name)
        {
            if(strlen($_FILES['files']['name'][$i]) > 1)
            {  
                move_uploaded_file($_FILES['files']['tmp_name'][$i],$folder."/".$name);
            }
        }                
    }
    $pyscript = directoryUrlBackward."Python_files\\directory_extract_and_place_QR.py"; 
    //$pyscript = "C:\\Program^ Files\\Python38\\projects\\demo\\directory_extract_and_place_QR.py";
    $cmd = "$pyscript $template_id $folder $user_id $entry_type $progressfile ".dbName." ".subdomain." ".directoryUrlForward." ".directoryUrlBackward." ".servername." ".username." ".password." $logged_in_user_site_id 2>&1";
    exec($cmd, $output, $return);
    //print_r($output);
    if(array_values($output)[0]=='Duplicates'){
        $count=array_values($output)[1];
        $unids=array_values($output)[2];
        $filenames=array_values($output)[3];
        $response = json_encode(array('type'=>'Duplicates', 'msg'=>$count.' record(s) are already existed in DB.<br />Please confirm to generate these and inactivate the old ones.', 'folder'=>$folder, 'filename'=>$filenames, 'template_id'=>$template_id, 'pdf_page'=>$pdf_page, 'unids'=>$unids, 'progressfile'=>$progressfile));
    }else{
        $response = json_encode(array('type'=>'Success', 'dlink'=>end($output)));
    }    
    //$response = end($output);   
}
echo $response;
