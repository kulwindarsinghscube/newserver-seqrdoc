<?php
$dirFrontUrl="C:\\Inetpub\\vhosts\\seqrdoc.com\\httpdocs\\demo\\public\\pdf2pdf\\";
 $pyscript = $dirFrontUrl."Python_files\\session.py";
        //$pyscript = "C:\\Program^ Files\\Python38\\projects\\demo\\extract_and_place.py";
        $cmd = "$pyscript 2>&1"; 
        //print($cmd);
        exec($cmd, $output, $return);
        print_r($output);

//exec("begali.py");
//echo 'Script is executed by: ' . get_current_user() . getmygid();
?>