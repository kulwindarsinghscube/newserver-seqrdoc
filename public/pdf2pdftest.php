<?php 

/*$c=exec('python session.py 2>&1');
      //$res=shell_exec($c);
      echo $c;
*/

      include('..\login_session.php');
      $dirFrontUrl="C:\\Inetpub\\vhosts\\seqrdoc.com\\httpdocs\\demo\\public\\pdf2pdf\\Python_files\\";
      $pyscript = $dirFrontUrl."session.py";
        //$pyscript = "C:\\Program^ Files\\Python38\\projects\\demo\\extract_and_place.py";
        $cmd = "$pyscript 2>&1";
        exec($cmd, $output, $return);
        print_r($output);

?>