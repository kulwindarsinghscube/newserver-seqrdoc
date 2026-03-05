<?php
require('login_session.php');
if(!empty($_GET['file']))
{
    if($_GET['status'] == 'start'){
        $filename = $_GET['file'];
        $file = "processed_pdfs/" . $filename;    
        if (!file_exists($file)) {
            $myfile=fopen($file, "w");
            //$txt = '{"percent": 0,  "message": "" }';
            //fwrite($myfile, $txt);            
            echo "File created.";
        } 
    }
    if($_GET['status'] == 'end'){
        $filename = $_GET['file'];
        $file = "processed_pdfs/" . $filename;    
        if (file_exists($file)) {
            unlink($file);
            echo "File deleted.";
        }         
    }
}
?>