<?php

if(!empty($_POST['template_name']))
{    
	$domain = $_SERVER['HTTP_HOST'];
    $subdomain = explode('.', $domain);

    $filename = $_POST['template_name'].".txt";
    $txt = json_encode($_POST['columns'] , true);

    $folderPath = $_SERVER['DOCUMENT_ROOT'].'/'.$subdomain[0]."/excel2pdf";
    if (!is_dir($folderPath)) {
        mkdir($folderPath, 0777, true);
    }

    $folderPath1 = $_SERVER['DOCUMENT_ROOT'].'/'.$subdomain[0]."/excel2pdf/processed_pdfs";
    if (!is_dir($folderPath1)) {
        mkdir($folderPath1, 0777, true);
    }

    $folderPath2 = $_SERVER['DOCUMENT_ROOT'].'/'.$subdomain[0]."/excel2pdf/processed_pdfs/excel";
    if (!is_dir($folderPath2)) {
        mkdir($folderPath2, 0777, true);
    }


    // $file = $_SERVER['DOCUMENT_ROOT']."/excel2pdf/processed_pdfs/excel/" . $filename;  
    $file = $_SERVER['DOCUMENT_ROOT'].'/'.$subdomain[0]."/excel2pdf/processed_pdfs/excel/" . $filename;  
    $myfile=fopen($file, "w");
    fwrite($myfile, $txt);            
    $response = json_encode(array('type'=>'Success')); 
}

echo $response;
?>