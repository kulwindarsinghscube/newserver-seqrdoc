<?php
/**
 *
 *  Author : Ketan valand 
 *   Date  : 28/12/2019
 *   Use   : Check specific User route permission
 *
**/
namespace App\Helpers;
use Log,Auth,DB;

use App\Models\StudentTable;
/*use App\Models\SuperAdmin;*/
use App\models\Demo\SuperAdmin as DemoSuperAdmin;
use App\models\Demo\Site as DemoSite;
use App\models\TemplateMaster;
use Storage;
use App\models\SbExceUploadHistory;
use App\models\ExcelUploadHistory;

use App\Utility\BlockChain;
use App\Utility\BlockChainV1;
use App\models\BlockChainMintData;

use App\models\BcSmartContract;

use Illuminate\Support\Facades\Schema;

include(app_path('Services/SftpService.php'));


class CoreHelper
{
	public static function getverticaldata($input)
{
    $output = [];
    
    foreach ($input as $student) {
        $studentName = $student[0];
        $currentSemester = '';
        $subjects = [];
        $semesterSgpaMap = [];

        $i = 1;
        while ($i < count($student)) {
            $value = $student[$i];

            // Skip null or empty values
            if ($value === null || trim((string)$value) === '') {
                $i++;
                continue;
            }

            // Detect SEMESTER header
            if (stripos($value, 'SEMESTER') !== false) {
                $currentSemester = trim($value);
                $subjects[$currentSemester] = [];
                $i++;
                continue;
            }

            // Detect SGPA (numeric followed by next non-null = SEMESTER or end)
            if (is_numeric($value) && $currentSemester) {
                // Find next non-null value
                $nextNonNull = null;
                for ($j = $i + 1; $j < count($student); $j++) {
                    if ($student[$j] !== null && trim((string)$student[$j]) !== '') {
                        $nextNonNull = $student[$j];
                        break;
                    }
                }

                // If next non-null value is SEMESTER or end of array, it's SGPA
                if ($nextNonNull === null || stripos($nextNonNull, 'SEMESTER') !== false) {
                    $semesterSgpaMap[$currentSemester] = (float)$value;
                    $i++;
                    continue;
                }
            }

            // Each subject group: [Code, Name, Marks, Grade, Credits]
            if (isset($student[$i + 4])) {
                $subjects[$currentSemester][] = [
                    'student' => $studentName,
                    'semester' => $currentSemester,
                    'code' => $student[$i],
                    'subject' => $student[$i + 1],
                    'marks' => $student[$i + 2],
                    'grade' => $student[$i + 3],
                    'credit' => $student[$i + 4]
                ];
                $i += 5;
            } else {
                $i++;
            }
        }

        // Assign SGPA to all subjects of each semester
        foreach ($subjects as $semester => $subjectList) {
            $sgpa = $semesterSgpaMap[$semester] ?? null;
            foreach ($subjectList as $subject) {
                $subject['sgpa'] = $sgpa;
                $output[] = array_values($subject);
            }
        }
    }

    return $output;
}

    public static function genRandomStr($length) {
        $characters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $string = '';
        for ($p = 0; $p < $length; $p++) {
            $string .= $characters[mt_rand(0, strlen($characters) - 1)];
        }
        return $string;
    }

    public static function sendSMS($mobile_no, $text) {
        $apiKey = "A32ba2b0a6770c225411fd95ff86401c4";
        $message = urlencode($text);
        $sender_id = "scubes";

        $url = "https://alerts.solutionsinfini.com/api/v4/?api_key=" . $apiKey . "&method=sms&message=" . $message . "&to=" . $mobile_no . "&sender=" . $sender_id . "";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        $result = curl_exec($ch);
        curl_close($ch);

        $data = (json_decode($result, true));

        if ($data['status'] == "OK") {
            return 1;
        } else {
            return 0;
        }
    }

    public static function generateOTP($digits) {
        //$digits = 6;
        $OTP = rand(pow(10, $digits - 1), pow(10, $digits) - 1); //6 Digit OTP
        return $OTP;
    }

    public static function uploadSingleFile($fieldname, $maxsize, $uploadpath, $extensions = false, $ref_name = false) {
    $upload_field_name = $_FILES[$fieldname]['name'];
    if (empty($upload_field_name) || $upload_field_name == 'NULL') {
        return array('file' => $_FILES[$fieldname]["name"], 'status' => false, 'msg' => 'Please upload a file');
    }
    
    $file_extension = strtolower(pathinfo($upload_field_name, PATHINFO_EXTENSION));

    if ($extensions !== false && is_array($extensions)) {
        if (!in_array($file_extension, $extensions)) {
            return array('file' => $_FILES[$fieldname]["name"], 'status' => false, 'msg' => 'Please upload valid file ');
        }
    }
    $file_size = @filesize($_FILES[$fieldname]["tmp_name"]);
    if ($file_size > $maxsize) {
        return array('file' => $_FILES[$fieldname]["name"], 'status' => false, 'msg' => 'File Exceeds maximum limit');
    }
    if (isset($upload_field_name)) {
        if ($_FILES[$fieldname]["error"] > 0) {
            return array('file' => $_FILES[$fieldname]["name"], 'status' => false, 'msg' => 'Error: ' . $_FILES[$fieldname]['error']);
        }
    }
    if ($ref_name == false) {
        

        $file_name_without_ext = self::FileNameWithoutExt($upload_field_name);
        $file_name = time() . '_' . self::RenameUploadFile($file_name_without_ext) . "." . $file_extension;
    } else {
        $file_name = str_replace(" ", "_", $ref_name) . "." . $file_extension;
    }
    if (!is_dir($uploadpath)) {
        mkdir($uploadpath, 0777, true);
    }
    if (move_uploaded_file($_FILES[$fieldname]["tmp_name"], $uploadpath . $file_name)) {
        return array('file' => $_FILES[$fieldname]["name"], 'status' => true, 'msg' => 'File Uploaded Successfully!', 'filename' => $file_name);
    } else {
        return array('file' => $_FILES[$fieldname]["name"], 'status' => false, 'msg' => 'Sorry unable to upload your file, Please try after some time.');
    }
}

public static function FileNameWithoutExt($filename) {
    return substr($filename, 0, (strlen($filename)) - (strlen(strrchr($filename, '.'))));
}

public static function RenameUploadFile($data) {
    $search = array("'", " ", "(", ")", ".", "&", "-", "\"", "\\", "?", ":", "/");
    $replace = array("", "_", "", "", "", "", "", "", "", "", "", "");
    $new_data = str_replace($search, $replace, $data);
    return strtolower($new_data);
}

    public static function deleteDirectory($dirPath) {
        if (is_dir($dirPath)) {
            $objects = scandir($dirPath);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dirPath . DIRECTORY_SEPARATOR . $object) == "dir") {
                        self::deleteDirectory($dirPath . DIRECTORY_SEPARATOR . $object);
                    } else {
                        unlink($dirPath . DIRECTORY_SEPARATOR . $object);
                    }
                }
            }
            reset($objects);
            rmdir($dirPath);
        }
    }

    public static function uploadBlob($filetoUpload,$blobName){
    $accesskey=env('AZURE_STORAGE_KEY'); 
    $storageAccount =env('AZURE_STORAGE_NAME'); 
    $containerName = env('AZURE_STORAGE_CONTAINER');  
    $destinationURL = "https://$storageAccount.blob.core.windows.net/$containerName/$blobName";
    //exit;
    $currentDate = gmdate("D, d M Y H:i:s T", time());
    $handle = fopen($filetoUpload, "r");
    $fileLen = filesize($filetoUpload);

    $headerResource = "x-ms-blob-cache-control:max-age=3600\nx-ms-blob-type:BlockBlob\nx-ms-date:$currentDate\nx-ms-version:2015-12-11";
    $urlResource = "/$storageAccount/$containerName/$blobName";

    $arraysign = array();
    $arraysign[] = 'PUT';               /*HTTP Verb*/  
    $arraysign[] = '';                  /*Content-Encoding*/  
    $arraysign[] = '';                  /*Content-Language*/  
    $arraysign[] = $fileLen;            /*Content-Length (include value when zero)*/  
    $arraysign[] = '';                  /*Content-MD5*/  
    $arraysign[] = 'application/pdf';         /*Content-Type*/  
    $arraysign[] = '';                  /*Date*/  
    $arraysign[] = '';                  /*If-Modified-Since */  
    $arraysign[] = '';                  /*If-Match*/  
    $arraysign[] = '';                  /*If-None-Match*/  
    $arraysign[] = '';                  /*If-Unmodified-Since*/  
    $arraysign[] = '';                  /*Range*/  
    $arraysign[] = $headerResource;     /*CanonicalizedHeaders*/
    $arraysign[] = $urlResource;        /*CanonicalizedResource*/

    $str2sign = implode("\n", $arraysign);

    $sig = base64_encode(hash_hmac('sha256', urldecode(utf8_encode($str2sign)), base64_decode($accesskey), true));  
    $authHeader = "SharedKey $storageAccount:$sig";

    $headers = [
        'Authorization: ' . $authHeader,
        'x-ms-blob-cache-control: max-age=3600',
        'x-ms-blob-type: BlockBlob',
        'x-ms-date: ' . $currentDate,
        'x-ms-version: 2015-12-11',
        'Content-Type: application/pdf',
        'Content-Length: ' . $fileLen
    ];

    $ch = curl_init($destinationURL);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_INFILE, $handle); 
    curl_setopt($ch, CURLOPT_INFILESIZE, $fileLen); 
    curl_setopt($ch, CURLOPT_UPLOAD, true); 
    $result = curl_exec($ch);

   
    $error= curl_error($ch);
    curl_close($ch);
    
    if(!empty($error)){
        print_r($error);
        exit;
    }
    return $result;

    }

    public static function checkMaxCertificateLimit($recordToGenerate){
          $site_id=Auth::guard('admin')->user()->site_id;

        $studentTableCounts = StudentTable::select('id')->where('site_id',$site_id)->count();
     
        $superAdminUpdate = DemoSuperAdmin::where('property','print_limit')->where('site_id',$site_id)
                            ->update(['current_value'=>$studentTableCounts]);
                              
        //get value,current value from super admin
        $template_value = DemoSuperAdmin::select('value','current_value')->where('property','print_limit')->where('site_id',$site_id)->first();

        // $domain = \Request::getHost();
        // $subdomain = explode('.', $domain);
        // if($subdomain[0]=='tpsdi'){
        //    dd($studentTableCounts); 
        // }
   
       // DB::connection('superdb')->table("super_admin")->where('property','print_limit')->where('site_id',$site_id)->update(['current_value'=>$studentTableCounts]);
        
        $currentValue=(int)$template_value['current_value'];
        $printLimit=(int)$template_value['value'];
        if($template_value['value'] == null || $template_value['value'] == 0 || $currentValue < $printLimit){
            
            $totalRecordsCount=$currentValue+$recordToGenerate;
            $noOfCertificateCanGenerate=$printLimit-$currentValue;
          
            if($totalRecordsCount<=$printLimit){
                $arrResp=array('status'=>true,'type' => 'success',"message"=>"success");
            }else{
                $arrResp=array('status'=>false,'type' => 'error',"message"=>"Your are limit to geneate certificate is ".$noOfCertificateCanGenerate);
            }
        }
        else{
             $arrResp=array('status'=>false,'type' => 'error',"message"=>"Limit exceed");
            
        } 
        return $arrResp;
    }

    public static function fetchStorageDetails(){
       // $site_id=Auth::guard('admin')->user()->site_id;
        
        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);
        //$site = Site::select('site_id')->where('site_url',$domain)->first();
       
        $siteData = DemoSite::select('pdf_storage_path','site_id')->where('site_url',$domain)->first();

        //print_r($siteData);
        if($siteData){
             $site_id= $siteData['site_id'];
            if(!empty($siteData['pdf_storage_path'])){

             $pdf_storage_path=$siteData['pdf_storage_path'];
            }else{
                $pdf_storage_path=false;
            }

            /*if(!empty($siteData->pdf_base_url)){
                $pdf_base_url=$siteData->pdf_base_url;
            }else{
                 $pdf_base_url=false;
            }*/

            $arrResp=array('status'=>true,'type' => 'success',"message"=>"success","pdf_storage_path"=>$pdf_storage_path,"site_id"=>$site_id);

        }else{
             $arrResp=array('status'=>false,'type' => 'error',"message"=>"Site data not found");

        }
        return $arrResp;
    }

    public static function checkMonadFtpStatus(){
        // FTP server details
        $ftpHost = \Config::get('constant.monad_ftp_host');
        $ftpPort = \Config::get('constant.monad_ftp_port');
        $ftpUsername = \Config::get('constant.monad_ftp_username');
        $ftpPassword = \Config::get('constant.monad_ftp_pass');        
        // open an FTP connection
        $connId = ftp_connect($ftpHost,$ftpPort); //or die("Couldn't connect to $ftpHost");   
        /*if($connId){
            $ftp_flag=is_array(ftp_nlist($connId, ".")) ? 'Connected' : 'Not Connected';            
        }else{
            $ftp_flag='Not Connected';
        }*/
        if (@ftp_login($connId, $ftpUsername, $ftpPassword))
        {
            //echo "Connection established.";
            $ftp_flag='Connected';
        }
        else
        {
            //echo "Couldn't establish a connection.";
            $ftp_flag='Not Connected';
        } 
        
        if($ftp_flag=='Connected'){
           $arrResp=array('status'=>true,'type' => 'success',"message"=>"Server connected!","ftpHost"=>$ftpHost,"ftp_flag"=>$ftp_flag);
        }else{
           $arrResp=array('status'=>false,'type' => 'error',"message"=>"Failed to connect to Monad University Server. Please try again after sometime.","ftpHost"=>$ftpHost,"ftp_flag"=>$ftp_flag); 
        }

        return $arrResp;
    }

    public static function checkAnuFtpStatus(){
        // FTP server details
        $ftpHost = \Config::get('constant.anu_ftp_host');
        $ftpPort = \Config::get('constant.anu_ftp_port');
        $ftpUsername = \Config::get('constant.anu_ftp_username');
        $ftpPassword = \Config::get('constant.anu_ftp_pass');        
        // open an FTP connection
        $connId = ftp_connect($ftpHost,$ftpPort); //or die("Couldn't connect to $ftpHost");   
        /*if($connId){
            $ftp_flag=is_array(ftp_nlist($connId, ".")) ? 'Connected' : 'Not Connected';            
        }else{
            $ftp_flag='Not Connected';
        }*/
        if (@ftp_login($connId, $ftpUsername, $ftpPassword))
        {
            //echo "Connection established.";
            $ftp_flag='Connected';
        }
        else
        {
            //echo "Couldn't establish a connection.";
            $ftp_flag='Not Connected';
        }
        
        if($ftp_flag=='Connected'){
           $arrResp=array('status'=>true,'type' => 'success',"message"=>"Server connected!","ftpHost"=>$ftpHost,"ftp_flag"=>$ftp_flag);
        }else{
           $arrResp=array('status'=>false,'type' => 'error',"message"=>"Failed to connect to Anant National University Server. Please try again after sometime.","ftpHost"=>$ftpHost,"ftp_flag"=>$ftp_flag); 
        }

        return $arrResp;
    }	
	
    public static function thousandsCurrencyFormat($num) {

      if($num>9999) {

            $x = round($num);
            $x_number_format = number_format($x);
            $x_array = explode(',', $x_number_format);
            $x_parts = array('k', 'm', 'b', 't');
            $x_count_parts = count($x_array) - 1;
            $x_display = $x;
            $x_display = $x_array[0] . ((int) $x_array[1][0] !== 0 ? '.' . $x_array[1][0] : '');
            $x_display .= $x_parts[$x_count_parts - 1];

            return $x_display;

      }

      return $num;
    }

    public static function createLoaderJson($jsonData,$flag=1){

         $domain = \Request::getHost();
         $subdomain = explode('.', $domain);

        
            $fileName = $jsonData['token']. '_loader.json';
            $loaderDir=public_path().'/'.$subdomain[0].'/backend/loader/';
         
            if($flag!=1&&!empty($jsonData['token'])){
                $jsonNew=$jsonData;
                $jsonData = json_decode(file_get_contents($loaderDir.$fileName), true); 

                if(isset($jsonNew['generatedCertificates'])){


                    $jsonData['pendingCertificates']=$jsonData['recordsToGenerate']-$jsonNew['generatedCertificates'];
                    if($jsonData['pendingCertificates']<0){

                        $jsonData['pendingCertificates']=($jsonData['recordsToGenerate']*-1)-$jsonNew['generatedCertificates'];

                    }
                    $jsonData['generatedCertificates'] =$jsonNew['generatedCertificates'];

                    if($jsonData['pendingCertificates']==0){
                        $jsonData['isGenerationCompleted'] =1; 
                        $predictedCompletion=round($jsonData['recordsToGenerate']/100);
                        if($predictedCompletion==0){
                            $predictedCompletion=1;
                        }

                        $predictedCompletion=$predictedCompletion*3;
                        $jsonData['totalTimeForGeneration'] = gmdate("H:i:s", $jsonData['totalSecondsForGeneration']+$predictedCompletion);

                        $timeArr=explode(':', $jsonData['totalTimeForGeneration']);
                        $timeStr='';
                        if($timeArr[0]!='00'){
                            $timeStr .=$timeArr[0].' Hours ';
                        }
                        if($timeArr[1]!='00'){
                            $timeStr .=$timeArr[1].' Minutes ';
                        }
                        if($timeArr[2]!='00'){
                            $timeStr .=$timeArr[2].' Seconds';
                        }
                        if(!empty($timeStr)){
                        $jsonData['totalTimeForGeneration'] =$timeStr;
                        }else{
                        $jsonData['totalTimeForGeneration'] ='Less than 1 second.'; 
                        }
                    }
                }
                if(isset($jsonNew['timePerCertificate'])){
                    $jsonData['timePerCertificate'] =$jsonNew['timePerCertificate'];

                    $totalSeconds =$jsonData['pendingCertificates']*$jsonData['timePerCertificate'];
                    $jsonData['predictedTime'] = date("h:i:s A", strtotime("+$totalSeconds sec"));
                    $jsonData['totalSecondsForGeneration'] = $jsonData['totalSecondsForGeneration']+$jsonNew['timePerCertificate'];
                    
                }
                
            }


            //if($subdomain[0] == 'rrmu'){

                //dd($jsonData);
            //}

            //dd($jsonData);
            if(!empty($jsonData['recordsToGenerate'])){
            $jsonData['percentageCompleted']=round(($jsonData['generatedCertificates']/$jsonData['recordsToGenerate'])*100);
             
            }else{
             $jsonData['percentageCompleted']=round($jsonData['percentageCompleted']);    
            }
            
            if(!is_dir($loaderDir)){
    
                        mkdir($loaderDir, 0777);
            }
            \File::put($loaderDir.$fileName,json_encode($jsonData));
            $protocol = (isset($_SERVER["HTTPS"])&& $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
            $prefix = $protocol.'://'.$subdomain[0].'.'.$subdomain[1].'.com/'.$subdomain[0].'/backend/loader/';
            //$prefix = 'http://'.$subdomain[0].'.seqrdoc.com/'.$subdomain[0].'/loader/';
            return array('loader_token'=>$jsonData['token'],'fileName'=> $prefix.$fileName);

    }

    public static function getLoaderJson($token) {
        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);
    
        $fileName = $token . '_loader.json';
        $loaderDir = public_path() . '/' . $subdomain[0] . '/backend/loader/';
    
        // Check if the file exists before attempting to read it
        if (file_exists($loaderDir . $fileName)) {
            $jsonData = json_decode(file_get_contents($loaderDir . $fileName), true);
            return $jsonData;
        } else {
            // Return an error or an empty array if the file doesn't exist
            return [];
        }
    }
    


    public static function rrmdir($dir) {
        if (is_dir($dir)) {
          $objects = scandir($dir);
          foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
              if (filetype($dir."/".$object) == "dir") 
                 rrmdir($dir."/".$object); 
              else unlink   ($dir."/".$object);
            }
          }
          reset($objects);
          rmdir($dir);
        }
    }

    public static function formatSizeUnits($bytes)
    {
        if ($bytes >= 1073741824)
        {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        }
        elseif ($bytes >= 1048576)
        {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        }
        elseif ($bytes >= 1024)
        {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        }
        elseif ($bytes > 1)
        {
            $bytes = $bytes . ' bytes';
        }
        elseif ($bytes == 1)
        {
            $bytes = $bytes . ' byte';
        }
        else
        {
            $bytes = '0 bytes';
        }

        return $bytes;
    }


    public static function getFilesAndSize($directory,$file_size_pdf_file,$file_count_pdf_file){
        // if(!empty($extensions)){
        //     $files = glob($directory . "*.{$extensions}",GLOB_BRACE);
        // }else{
        $files = glob($directory . "*");    

        //print_r($files);
       // exit;
        //}
        // $file_size_pdf_file=0;
        // $file_count_pdf_file=0;
        if ($files){
         //$file_count_pdf_file = count($files);
            foreach($files as $path){

                if(is_file($path)){
                    $file_count_pdf_file++;
                    $file_size_pdf_file += filesize($path);
                }

                if(is_dir($path)){  
                    $resp=self::getFilesAndSize($path,$file_size_pdf_file,$file_count_pdf_file);
                    //$file_size_pdf_file=$resp['file_size_pdf_file']+$file_size_pdf_file;

                    //$file_count_pdf_file=$resp['file_count_pdf_file']+$file_count_pdf_file;
                }
                //is_file($path) && $file_size_pdf_file += filesize($path);
                //is_dir($path)  && $size += get_dir_size($path);
            }
        }

        return array("file_size_pdf_file"=>$file_size_pdf_file,"file_count_pdf_file"=>$file_count_pdf_file);
    }

    public static function getDirContents($dir,&$file_count_pdf_file=0,&$file_size_pdf_file=0, &$results = array()) {
        $files = scandir($dir);

        foreach ($files as $key => $value) {
            $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
            if (!is_dir($path)) {
                $results[] = $path;
                $file_count_pdf_file++;
                $file_size_pdf_file += filesize($path);
            } else if ($value != "." && $value != "..") {
                $resp=self::getDirContents($path,$file_count_pdf_file,$file_size_pdf_file, $results);
                $results[] = $path;
                //$file_size_pdf_file=$resp['file_size_pdf_file']+$file_size_pdf_file;
                //$file_count_pdf_file=$resp['file_count_pdf_file']+$file_count_pdf_file;
            }
        }

        //return $results;
        return array("file_size_pdf_file"=>$file_size_pdf_file,"file_count_pdf_file"=>$file_count_pdf_file);
    }

    public static function compressPdfFile($inputFile,$outputFile,$pdfSetting=''){
		if($pdfSetting == ''){
			$pdfSetting='ebook';
		}else{
			$pdfSetting=$pdfSetting;
		}
        $output = exec('"'.\Config::get('constant.ghostscriptPath').'" -r120 -dSAFER -dQUIET -dBATCH -dNOPAUSE -sDEVICE=pdfwrite -dPDFSETTINGS=/'.$pdfSetting.' -dCompatibilityLevel=1.4 -sOutputFile='.$outputFile.' '.$inputFile);
        // echo '"'.\Config::get('constant.ghostscriptPath').'" -r120 -dSAFER -dQUIET -dBATCH -dNOPAUSE -sDEVICE=pdfwrite -dPDFSETTINGS=/'.$pdfSetting.' -dCompatibilityLevel=1.4 -sOutputFile='.$outputFile.' '.$inputFile;
        // print_r($output);
        // exit;
        return $output;
    }

  	
  	public static function geneatePayUHash($data){



// Merchant key here as provided by Payu
//$MERCHANT_KEY = "hDkYGPQe";

    //    $MERCHANT_KEY="S8u5hD";// scube live
		$MERCHANT_KEY = "ICFgMpPe";//monad live
     //   $MERCHANT_KEY = "rjQUPktU"; //scube test

		// Merchant Salt as provided by Payu
		//$SALT = "yIEkykqEH3";

        //$SALT = "HCZltttx"; // scube live
        $SALT = "j73HrLwwlT";//monad live
      //  $SALT = "e5iIg1jwi8";//scube test

		// End point - change to https://secure.payu.in for LIVE mode
        $PAYU_BASE_URL = "https://secure.payu.in";// live
    //    $PAYU_BASE_URL = "https://test.payu.in"; //test

		$action = '';

		$posted = array();
		/*if(!empty($_POST)) {
		    //print_r($_POST);
		  foreach($_POST as $key => $value) {    
		    $posted[$key] = $value; 
			
		  }
		}*/

		$posted['key']=$MERCHANT_KEY;
		$posted['txnid']=$data['txnid'];
		//$txnid =substr(hash('sha256', mt_rand() . microtime()), 0, 20);
		$posted['amount']=$data['amount'];
		$posted['firstname']=$data['name'];
		$posted['email']=$data['email'];
		$posted['phone']=$data['mobile_number'];
		$posted['productinfo']=$data['product_info'];
		$posted['surl']= $data['success_url'];//URL::route('payumoney-success');
		$posted['furl']=$data['failure_url']; //URL::route('payumoney-failure');
		$posted['service_provider']="payu_paisa";
		$posted['udf1']=$data['user'];
        $posted['udf2']=$data['platform'];
		
		$formError = 0;
		//print_r($posted);
		//exit;
		/*if(empty($posted['txnid'])) {
		  // Generate random transaction id
		  $txnid = substr(hash('sha256', mt_rand() . microtime()), 0, 20);
		} else {
		  $txnid = $posted['txnid'];
		}*/
		$hash = '';
		// Hash Sequence
		$hashSequence = "key|txnid|amount|productinfo|firstname|email|udf1|udf2|udf3|udf4|udf5|udf6|udf7|udf8|udf9|udf10";
		if(empty($posted['hash']) && sizeof($posted) > 0) {
		  if(
		          empty($posted['key'])
		          || empty($posted['txnid'])
		          || empty($posted['amount'])
		          || empty($posted['firstname'])
		          || empty($posted['email'])
		          || empty($posted['phone'])
		          || empty($posted['productinfo'])
		          || empty($posted['surl'])
		          || empty($posted['furl'])
				  || empty($posted['service_provider'])
				  || empty($posted['udf1'])
		  ) {
		    $formError = 1;

		  } else {
		    //$posted['productinfo'] = json_encode(json_decode('[{"name":"tutionfee","description":"","value":"500","isRequired":"false"},{"name":"developmentfee","description":"monthly tution fee","value":"1500","isRequired":"false"}]'));
			$hashVarsSeq = explode('|', $hashSequence);
		    $hash_string = '';	
			foreach($hashVarsSeq as $hash_var) {
		      $hash_string .= isset($posted[$hash_var]) ? $posted[$hash_var] : '';
		      $hash_string .= '|';
		    }

		    $hash_string .= $SALT;


		    $hash = strtolower(hash('sha512', $hash_string));
		    $action = $PAYU_BASE_URL . '/_payment';
		  }
		} elseif(!empty($posted['hash'])) {
		  $hash = $posted['hash'];
		  $action = $PAYU_BASE_URL . '/_payment';
		}
		$result['posted']=$posted;
		$result['hash']=$hash;
		$result['MERCHANT_KEY']=$MERCHANT_KEY;
		$result['txnid']=$data['txnid'];
		$result['action']=$action;
		$result['formError']=$formError;

		return $result;
  	}



    /******************************BlockChain Start*********************************************/

    public static function checkContactAddress($template_id,$templateType='NORMALTEMPLATE'){//NORMALTEMPLATE, PDF2PDFTEMPLATE
        
        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);
        if($templateType=='NORMALTEMPLATE'){
         $checkContact = TemplateMaster::select('bc_contract_address')->where('id',$template_id)->first();

         if($checkContact&&empty($checkContact['bc_contract_address'])){
                if($template_id==698||$template_id==718||$template_id==719||$subdomain[0]=="anu"){
                $mode=1;//1:live 0:testnet
                }else{
                $mode=0;    
                }
                $response=BlockChain::deployContract($mode);
                if($response&&$response['status']==200&&isset($response['contractAddress'])&&!empty($response['contractAddress'])){

                     TemplateMaster::where('id',$template_id)->update(['bc_contract_address'=>$response['contractAddress']]);

                }
         }
        }elseif($templateType=='CUSTOMTEMPLATE'){
            //$response=BlockChain::deployContract($mode);

        }else{
            $checkContact=DB::select(DB::raw('SELECT bc_contract_address FROM `uploaded_pdfs` WHERE id = "'.$template_id.'"')); 
            if($checkContact&&empty($checkContact['bc_contract_address'])){

                if($subdomain[0]=="mitwpu"||$subdomain[0]=="anu"){
                    $mode=1; 
                }else{
                    $mode=0;     
                }
                
                $response=BlockChain::deployContract($mode);
                if($response&&$response['status']==200&&isset($response['contractAddress'])&&!empty($response['contractAddress'])){
                    DB::select(DB::raw('UPDATE `uploaded_pdfs` SET bc_contract_address="'.$response['contractAddress'].'" WHERE id="'.$template_id.'"'));    
                }
            }
        }
     }


    public static function mintPDF($data){//NORMALTEMPLATE, PDF2PDFTEMPLATE
        
        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);


        $response=["status"=>0];
        $mintData=[];

        $siteData = DemoSite::select('bc_wallet_address')->where('site_url',$domain)->first();

        if(!empty($siteData&&!empty($siteData['bc_wallet_address'])&&$data['pdf_file'])&&!empty($data['uniqueHash'])&&!empty($data['template_id'])){

            if(isset($data['bc_contract_address'])){
                $checkContact['bc_contract_address']=$data['bc_contract_address'];
            }else{
            $checkContact = TemplateMaster::select('bc_contract_address')->where('id',$data['template_id'])->first();

             $checkContact['bc_contract_address'];

            }
            
            if($checkContact&&!empty($checkContact['bc_contract_address'])){

                $mintData['pdf_file']=$data['pdf_file'];
                
                //Fetch from Demo sites table
                $mintData['walletID']=$siteData['bc_wallet_address'];

                //Fetch from Template Master
                $mintData['smartContractAddress']=$checkContact['bc_contract_address'];

                $mintData['documentType']=(!empty($data['documentType']))?$data['documentType']:"";;
                $mintData['description']=(!empty($data['description']))?$data['description']:"";
                $mintData['uniqueHash']=$data['uniqueHash'];



                for($i=1;$i<=5;$i++){
                
                    
                    if(isset($data['metadata'.$i])){
                        $mintData['metadata'.$i]=$data['metadata'.$i];    
                    }

                }
                
                if($subdomain[0]=="mpkv"||$subdomain[0]=="ksu"||$subdomain[0]=="konkankrishi"||$subdomain[0]=="mitwpu" ||$subdomain[0]=="aiimsnagpur"||$subdomain[0]=="unikbp"){
                    $mode=1;//1:live
                }else if($data['template_id']==2||$data['template_id']==698||$data['template_id']==718||$data['template_id']==719||$subdomain[0]=="anu"||$subdomain[0]=="aiimsnagpur"||($subdomain[0]=="demo"&&$data['template_id']==137)){
                    $mode=1;//1:live 0:testnet
                
                    if($subdomain[0]=="anu"){
                        if(empty($data['walletID'])){
                            $mintData['walletID']="0x06c87829C80924E355F61C1b535533770553FD37";
                        }
                        
                    }else{
                        $mintData['walletID']="0xB509AF6532Af95eE59286A8235f2A290c26b5730";
                    }
                }else{
                    $mode=0;    
                }

                if($subdomain[0]=="mitwpu"){
                    $mintData['walletID']="0x87d292d5Ccbf2e0c3807eE1D561c95f4f090EA48";
                    
                }
               
                // $response=BlockChain::mintData($mode,$mintData);
                if($subdomain[0]=="anu" || $subdomain[0]=="demo" || $subdomain[0]=="ksu") {
                    $mode =1;
                    // dd($mode,$mintData);
                    $response=BlockChainV1::mintData($mode,$mintData);
                } else {
                    $response=BlockChain::mintData($mode,$mintData); 
                }
                if($response&&$response['status']==200&&isset($response['txnHash'])&&!empty($response['txnHash'])){
                    
                    
                    $data=$response;
                    $datetime  = date("Y-m-d H:i:s");
                    BlockChainMintData::create(['txn_hash'=>$response['txnHash'],'gas_fees'=>$response['gasPrice'],'token_id'=>$response['tokenID'],'key'=>$mintData['uniqueHash'],'created_at'=>$datetime,'data'=>$data]);

                //     BlockChainMintData::create([
                //     'txn_hash' => $response['txnHash'],
                //     'gas_fees' => $response['gasPrice'],
                //     'token_id' => $response['tokenID'],
                //     'key' => $mintData['uniqueHash'],
                //     'created_at' => $datetime
                // ]);
                    
                    $response=["status"=>$response['status'],"txnHash"=>$response['txnHash'],"ipfsHash"=>$response['ipfsHash'],"pinataIpfsHash"=>$response['pinataIpfsHash'],'data'=>$data];

                }
            }
        }
        return $response;
    }

    public static function getBCVerificationUrl($encryptedData){
        
        $domain = \Request::getHost();
        //$subdomain = explode('.', $domain);
        if($domain == "anu.seqrdoc.com"){
            return "https://verification.anu.edu.in/bverify/".$encryptedData;
        }
        return "https://".$domain."/bverify/".$encryptedData;
    }

    public static function retreiveDetails($data){

        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);
        // dd($data);
         if(!empty($data['template_id'])&&!empty($data['walletID'])&&!empty($data['uniqueHash'])){
                if($subdomain[0]=="mpkv"||$subdomain[0]=="ksu"||$subdomain[0]=="konkankrishi" || $subdomain[0]=="mitwpu"|| $subdomain[0]=="unikbp"){
                    $mode=1;//1:live 0:testnet
                    if($subdomain[0]=="mitwpu"&&$data['uniqueHash']=="3433C65331CFB83A101464F46E019E5D"){
                        $data['contractAddress']="";
                    }
                }else if($data['template_id']==2||$data['template_id']==698||$data['template_id']==718||$data['template_id']==719||$subdomain[0]=="anu"||$subdomain[0]=="aiimsnagpur"||$subdomain[0]=="verification"||($subdomain[0]=="demo"&&$data['template_id']==137)){
                    if($subdomain[0]!="anu"&&$subdomain[0]!="verification"){
                        $data['walletID']="0xB509AF6532Af95eE59286A8235f2A290c26b5730";    
                    }
                    

                    
                    $mode=1;//1:live 0:testnet
                }else{
                    $mode=0;    
                }
                // $data['walletID'] = '0x4a8c8e4D5D255f95253FC93E99b68c05aa869c4F';
            //    print_r($data);die;
              // dd($data);



                $response=BlockChain::retreiveDetails($mode,$data);
                
                return $response;
         }
        
     }
    /******************************BlockChain End*********************************************/

      /****************Encryption*****************************************/
    public static function encrypt_adv($plaintext, $key) {
        $ivlen = openssl_cipher_iv_length($cipher = "AES-256-CBC");
        $iv = openssl_random_pseudo_bytes($ivlen);
        $ciphertext_raw = openssl_encrypt($plaintext, $cipher, $key, $options = OPENSSL_RAW_DATA, $iv);
        $ciphertext = base64_encode($iv . /*$hmac.*/$ciphertext_raw);
        //$ciphertext = str_replace('/', '[slash]', $ciphertext);
        //$ciphertext = str_replace('+', '[plus]', $ciphertext);
        return $ciphertext;
    }

    public static function decrypt_adv($ciphertext, $key) {
        //$ciphertext = str_replace('[slash]', '/', $ciphertext);
        //$ciphertext = str_replace('[plus]', '+', $ciphertext);
        $c = base64_decode($ciphertext);
        $ivlen = openssl_cipher_iv_length($cipher = "AES-256-CBC");
        $iv = substr($c, 0, $ivlen);
        $ciphertext_raw = substr($c, $ivlen);

        $original_plaintext = openssl_decrypt($ciphertext_raw, $cipher, $key, $options = OPENSSL_RAW_DATA, $iv);
        return $original_plaintext;
    }
    /************************************************************************/

    /****************  Rohit changes 18/05/2023 *****************/
    public static function awsUpload($output,$outputFile,$serial_no,$certName) {
        
        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);
        
        $s3 = \Storage::disk('s3');
        // // File Exist in folder
        if($s3->exists($outputFile)) {
            $student = StudentTable::where('status',1)->where('serial_no',$serial_no)->value('id');
            $awsInactivePDFFolder = 'public/'.$subdomain[0].'/backend/pdf_file/Inactive_PDF';
            // check folder 
            if($s3->exists($awsInactivePDFFolder)) {
                $newFileNamePdf = 'public/'.$subdomain[0].'/backend/pdf_file/Inactive_PDF/'.$student.'_'.$certName;

               // echo $newFileNamePdf;
                $s3->move($outputFile, $newFileNamePdf);
            } else {
                // folder create
                $s3->makeDirectory($awsInactivePDFFolder, 0777);
                $newFileNamePdf = 'public/'.$subdomain[0].'/backend/pdf_file/Inactive_PDF/'.$student.'_'.$certName;
                $s3->move($outputFile, $newFileNamePdf);
            }
        }
        if(!$s3->exists($outputFile)) {
            $s3->put($outputFile, file_get_contents($output));
        }
       
        @unlink($output);
        
      
    }
    /**************** Rohit changes 18/05/2023 *****************/

    
    /**************** Rohit changes 24/08/2023 *****************/
    /**************** Sandboxing *****************/
    
    public static function sandboxingFile($systemConfig,$fullpath,$template_id,$excelfile) {
        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);

        if($systemConfig['sandboxing'] == 1){
            $sandbox_directory = public_path().'/'.$subdomain[0].'/'.\Config::get('constant.sandbox').'/'.$template_id;
            //if directory not exist make directory
            if(!is_dir($sandbox_directory)){
                mkdir($sandbox_directory, 0777);
            }
            $aws_excel = \File::copy($fullpath,$sandbox_directory.'/'.$excelfile);
            // $filename1 = \Storage::disk('s3')->url($excelfile);
            // dd($aws_excel);
        }else{
	    $sandbox_directory = public_path().'/'.$subdomain[0].'/'.\Config::get('constant.template').'/'.$template_id;
            //if directory not exist make directory
            if(!is_dir($sandbox_directory)){
                mkdir($sandbox_directory, 0777);
            }
            
            $aws_excel = \File::copy($fullpath,$sandbox_directory.'/'.$excelfile);

            //$aws_excel = \File::copy($fullpath,public_path().'/'.$subdomain[0].'/'.\Config::get('constant.template').'/'.$template_id.'/'.$excelfile);
            // $filename1 = \Storage::disk('s3')->url($excelfile);
        }
    }


    public static function sandboxingFileAWS($systemConfig,$fullpath,$template_id,$excelfile) {
        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);
        
        if($systemConfig['sandboxing'] == 1){
            $sandbox_directory = \Config::get('constant.amazone_path').$subdomain[0].'/'.\Config::get('constant.sandbox').'/'.$template_id;
            //if directory not exist make directory
            if(!is_dir($sandbox_directory)){
                mkdir($sandbox_directory, 0777);
            }
            $aws_excel = \Storage::disk('s3')->put($subdomain[0].'/'.\Config::get('constant.sandbox').'/'.$template_id.'/'.$excelfile, file_get_contents($fullpath), 'public');
            $filename1 = \Storage::disk('s3')->url($excelfile);
            // dd($aws_excel);
        }else{
	    $sandbox_directory = \Config::get('constant.amazone_path').$subdomain[0].'/'.\Config::get('constant.template').'/'.$template_id;
            //if directory not exist make directory
            if(!is_dir($sandbox_directory)){
                mkdir($sandbox_directory, 0777);
            }

	    $aws_excel = \Storage::disk('s3')->put($subdomain[0].'/'.\Config::get('constant.template').'/'.$template_id.'/'.$excelfile, file_get_contents($fullpath), 'public');
            //$aws_excel = \Storage::disk('s3')->put($subdomain[0].'/'.\Config::get('constant.template').'/'.$template_id.'/'.$excelfile, file_get_contents($fullpath), 'public');
            $filename1 = \Storage::disk('s3')->url($excelfile);
        }

    }



    public static function sandboxingDB($systemConfig,$template_name,$excelfile,$file_name,$user,$no_of_records,$auth_site_id) {
        if(isset($systemConfig['sandboxing']) && $systemConfig['sandboxing'] == 1){
            // with sandbox
            $result = SbExceUploadHistory::create(['template_name'=>$template_name,'excel_sheet_name'=>$excelfile,'pdf_file'=>$file_name,'user'=>$user,'no_of_records'=>$no_of_records,'site_id'=>$auth_site_id]);
        }else{
            // without sandbox
            $result = ExcelUploadHistory::create(['template_name'=>$template_name,'excel_sheet_name'=>$excelfile,'pdf_file'=>$file_name,'user'=>$user,'no_of_records'=>$no_of_records,'site_id'=>$auth_site_id]);
        }

    }

    /**************** Rohit changes 24/08/2023 *****************/



    public static function SFTPConnect() {
        $result = mySftpConnect();
        return $result;
    }


    public static function SFTPUpload($localFile) {
        $result = uploadFileOnServer($localFile);
        return $result;
    }



    public static function SFTPUploadAnu($sftpData) {

       // dd($sftpData);
        $result = uploadFileOnServerAnu($sftpData);
        return $result;
    }

    public static function SFTPUploadKMTC($localFile) {
        $result = uploadFileOnServerKmTC($localFile);
        return $result;
    }


    public static function listFilesKMTC($localFile) {
        $result = listFileOnServerKMTC($localFile);
        return $result;
    }


    public static function getFilesKMTC($certName) {
        $result = getFileOnServerKMTCV1($certName);
        return $result;
    }





    



    public static function getFileWithExtension($str, $fileBasePath) {
        // Check if the string does not already have a file extension
        if (strpos($str, '.') === false) {

            $extension = strtolower(pathinfo($str, PATHINFO_EXTENSION));

            // Check if the extension is valid (you can customize this)
            $validExtensions = ['jpg', 'jpeg', 'png', 'bmp'];
            if (!in_array($extension, $validExtensions)) {  
                // Loop through each extension and check if the file exists
                foreach ($validExtensions as $extension) {
                    $filePath = $fileBasePath . '.' . $extension;
                    
                    // If file exists, return the file path with the extension
                    if (file_exists($filePath)) {
                        return $str. '.' . $extension;
                    }
                }
            }
                
            // Return false if no file exists with the given extensions
            return false;
        }
        
        // If the string already contains an extension, return it as is
        return $str;
    }


    
    // Rohit Blockchain 18-07-2025

    public static function customMintPDF($data,$blockchain_type,$template_type){//NORMALTEMPLATE, PDF2PDFTEMPLATE
        

        
        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);


        $response=["status"=>0];
        $mintData=[];

        $siteData = DemoSite::select('bc_wallet_address')->where('site_url',$domain)->first();
        if($subdomain[0] == 'anu') {
            $siteData['bc_wallet_address'] = '0x06c87829C80924E355F61C1b535533770553FD37';
        }
        if(!empty($siteData&&!empty($siteData['bc_wallet_address'])&&$data['pdf_file'])&&!empty($data['uniqueHash'])&&!empty($data['template_id'])){


            // if(isset($data['bc_contract_address'])){
            //     $checkContact['bc_contract_address']=$data['bc_contract_address'];
            // }else{
            //     $checkContact = TemplateMaster::select('bc_contract_address')->where('id',$data['template_id'])->first();

            //     $checkContact['bc_contract_address'];

            // }

            if($blockchain_type == '1') {

            
                $mode = 1;
                $template_id = $data['template_id'];
                $bc_wallet_address = $siteData['bc_wallet_address']; 
                

                $checkActiveContractExist = self::checkActiveContract($template_id,$mode,$template_type);

                //print_r($checkActiveContractExist);


                if (!$checkActiveContractExist) {

                    
                    // No active contract exists, create a new one
                    $createNewContractResponse = self::createNewContract($bc_wallet_address, $template_id,$mode,$template_type);
                    // print_r($createNewContractResponse);

                    if ($createNewContractResponse) {
                        $bc_contract_address = $createNewContractResponse['contract_address'];
                        $bc_wallet_address = $createNewContractResponse['wallet_address'];
                        $bc_sc_id = $createNewContractResponse['bc_sc_id'];
                        // checkContract or any further logic here
                    }
                } else {
                    // Use existing active contract
                    $bc_contract_address = $checkActiveContractExist['contract_address'];
                    $bc_wallet_address = $checkActiveContractExist['wallet_address'];
                    $bc_sc_id = $checkActiveContractExist['bc_sc_id'];
                }
                

                $checkContractExist =self::checkContract($bc_wallet_address, $template_id,$mode,5000,$template_type);
                if($checkContractExist) {
                    $checkContact['bc_contract_address'] = $checkContractExist['contract_address'];
                    $mintData['bc_sc_id'] = $checkContractExist['bc_sc_id'];
                    
                    $bc_contract_address = $checkContractExist['contract_address'];
                    $bc_wallet_address = $checkContractExist['wallet_address'];
                    $bc_sc_id = $checkContractExist['bc_sc_id'];
                    
                }
                
            }


            // $result = getOrCreateActiveContract($template_id, $bc_wallet_address);
            // $bc_contract_address = $result['contract_address'];
            // $bc_wallet_address   = $result['wallet_address'];
            // $bc_sc_id            = $result['bc_sc_id'];

           
            if($checkContact&&!empty($checkContact['bc_contract_address'])){
                
                $mintData['pdf_file']=$data['pdf_file'];
                
                //Fetch from Demo sites table
                $mintData['walletID']=$bc_wallet_address;

                //Fetch from Template Master
                $mintData['smartContractAddress']=$checkContact['bc_contract_address'];

                $mintData['documentType']=(!empty($data['documentType']))?$data['documentType']:"";
                $mintData['description']=(!empty($data['description']))?$data['description']:"";
                $mintData['uniqueHash']=$data['uniqueHash'];

                $mintData['storageIdentifier']='lighthouse';

                if($subdomain[0]=="mitwpu"){
                    $mintData['storageIdentifier']='lighthouse';
                }

                for($i=1;$i<=5;$i++){
                
                    
                    if(isset($data['metadata'.$i])){
                        $mintData['metadata'.$i]=$data['metadata'.$i];    
                    }

                }
                
                
                $mode =1;

                if($blockchain_type=="1") {
                    // if($subdomain[0]=="anu") {
                        // $response=BlockChainV1::mintDataAnu($mode,$mintData);
                    // } else {
                        $response=BlockChainV1::mintData($mode,$mintData);    
                    // }
                        // if($subdomain[0]=="unikbp") {
                        // $response=BlockChainV1::mintData($mode,$mintData); 
                        // dd($response);   
                        // }
                    
                } else {
                    $response=BlockChain::mintData($mode,$mintData); 
                }

                if($response&&$response['status']==200&&isset($response['txnHash'])&&!empty($response['txnHash'])){
                    $datetime  = date("Y-m-d H:i:s");
                    BlockChainMintData::create(['txn_hash'=>$response['txnHash'],'gas_fees'=>$response['gasPrice'],'token_id'=>$response['tokenID'],'key'=>$mintData['uniqueHash'],'ipfs_hash'=>$response['ipfsHash'],'created_at'=>$datetime]);

                    if(isset($response['ipfsHash'])){
                        $response=["status"=>$response['status'],"txnHash"=>$response['txnHash'],"ipfsHash"=>$response['ipfsHash'],"pinataIpfsHash"=>$response['pinataIpfsHash'],"metadata_ipfs_hash"=>$response['metadata_ipfs_hash'],"bc_sc_id"=>$bc_sc_id ,'token_id'=>$response['tokenID'],'gasPrice'=>$response['gasPrice'] ];
                    }else{
                        $response=["status"=>$response['status'],"txnHash"=>$response['txnHash'],"metadata_ipfs_hash"=>$response['metadata_ipfs_hash'],"bc_sc_id"=>$bc_sc_id,'token_id'=>$response['tokenID'],'gasPrice'=>$response['gasPrice']];
                    }
                    

                }
            }
        }
        
        return $response;
    }

    public static  function deployContractNew($mode = '0')
    {

        $url = ($mode == '0')
                ? 'http://localhost:9090/testnet/deploy_contract'  // Testnet
                : 'http://localhost:9090/mainnet/deploy_contract'; // Live
                

        $postData = [
            'contract_name'   => 'scube',
            'contract_symbol' => 'string',
        ];

        $headers = [
            'Accept: application/json',
            'Content-Type: application/json',
            'x-api-key: 123456789',
        ];

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            curl_close($ch);
            return [
                'status'  => 400,
                'message' => 'cURL Error: ' . curl_error($ch),
            ];
        }

        curl_close($ch);

        $decoded = json_decode($response, true);

        if ($httpCode === 200 && isset($decoded['status']) && $decoded['status'] == 200) {
            return [
                'status'           => 200,
                'message'          => $decoded['message'],
                'contractAddress'  => $decoded['contractAddress'],
            ];
        } else {
            return [
                'status'  => $httpCode,
                'message' => $decoded['message'] ?? 'Failed to deploy contract.',
            ];
        }
    }




    public static  function checkContract($wallet_address, $template_id,$mode, $limit = 100,$template_type)
    {
        try {
            // Step 1: Check current active smart contract
            $current = DB::table('bc_smart_contracts')
                ->select('count', 'wallet_address', 'smart_contract_address', 'id')
                ->where([
                    ['template_type', '=', $template_type],
                    ['template_id', '=', $template_id],
                    ['is_active', '=', 1],
                    ['is_live', '=', $mode]
                ])
                ->first();

            if ($current && $current->count < $limit) {
                return [
                    'contract_address' => $current->smart_contract_address,
                    'wallet_address'   => $current->wallet_address,
                    'bc_sc_id'         => $current->id
                ];
            }

            // Step 2: Deploy new contract using cURL
            $deploy_response = self::deployContractNew($mode); // Pass '0' for testnet

            if (isset($deploy_response['status']) && $deploy_response['status'] == 200) {
                $contract_address = $deploy_response['contractAddress'];

                // Step 3: Deactivate previous contracts
                DB::table('bc_smart_contracts')
                    ->where([
                        ['template_id', '=', $template_id],
                        ['template_type', '=', $template_type],
                        ['is_live', '=', $mode],
                        
                    ])
                    ->update([
                        'is_active'   => 0,
                        'updated_at'  => now(),
                    ]);
                    // ->update(['is_active' => 0]);

                // Step 4: Insert new contract
                $bc_sc_id = DB::table('bc_smart_contracts')->insertGetId([
                    'count'                 => 0,
                    'template_id'           => $template_id,
                    'smart_contract_address'=> $contract_address,
                    'wallet_address'        => $wallet_address,
                    'template_type'         => $template_type,
                    'is_active'             => 1,
                    'is_live'               => 0,
                    'created_at'            => now(),
                    'updated_at'            => now()
                ]);

                return [
                    'contract_address' => $contract_address,
                    'wallet_address'   => $wallet_address,
                    'bc_sc_id'         => $bc_sc_id
                ];
            }

            return null;

        } catch (\Exception $e) {
            // Log::error('Smart Contract Error: '.$e->getMessage());
            return null;
        }
    }



    public static  function checkActiveContract($template_id,$mode,$template_type)
    {
        try {
            $activeContract = DB::table('bc_smart_contracts')
                ->select('smart_contract_address', 'wallet_address', 'id')
                ->where([
                    ['template_type', '=', $template_type],
                    ['template_id', '=', $template_id],
                    ['is_active', '=', 1],
                    ['is_live', '=', $mode]
                    
                ])
                ->first();

            if ($activeContract) {
                return [
                    'contract_address' => $activeContract->smart_contract_address,
                    'wallet_address'   => $activeContract->wallet_address,
                    'bc_sc_id'         => $activeContract->id
                ];
            }

            return null;

        } catch (\Exception $e) {
            // Optional: Log the error
            // Log::error('Error fetching active contract: '.$e->getMessage());
            return null;
        }
    }



    
    public static  function createNewContract($bc_wallet_address, $template_id,$mode,$template_type)
    {
        // Step 1: Deploy contract using cURL
        // $mode = 1; // 0 for testnet, 1 for live
        $url = ($mode == '0')
                ? 'http://localhost:9090/testnet/deploy_contract'  // Testnet
                : 'http://localhost:9090/mainnet/deploy_contract'; // Live
                

        $postData = [
            'contract_name'   => 'scube',
            'contract_symbol' => 'string',
        ];

        $headers = [
            'Accept: application/json',
            'Content-Type: application/json',
            'x-api-key: 123456789',
        ];

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            curl_close($ch);
            return [
                'status'  => 400,
                'message' => 'cURL Error: ' . curl_error($ch),
            ];
        }

        curl_close($ch);
        // $response = json_decode($response, true);
        // print_r($response);
        $data = json_decode($response, true);
        
        if ($httpCode == 200 && $data && isset($data['status']) && $data['status'] == 200 && isset($data['contractAddress'])) {
            $datetime = date("Y-m-d H:i:s");
            $contract_address = $data['contractAddress'];
            // Step 2: Insert new smart contract
            $bc_sc_id = \DB::table('bc_smart_contracts')->insertGetId([
                'count'                  => 0,
                'template_id'            => $template_id,
                'smart_contract_address' => $contract_address,
                'wallet_address'         => $bc_wallet_address,
                'template_type'          => $template_type,
                'is_active'              => 1,
                'is_live'                => $mode,
                 'created_at'             => $datetime,
            ]);

            // Step 3: Retrieve latest active contract
            $contract = \DB::table('bc_smart_contracts')
                ->select('smart_contract_address', 'wallet_address', 'id')
                ->where([
                    ['template_type', '=', $template_type],
                    ['template_id', '=', $template_id],
                    ['is_active', '=', 1],
                    ['is_live', '=', $mode]
                    
                ])
                ->first();

            if ($contract) {
                return [
                    'contract_address' => $contract->smart_contract_address,
                    'wallet_address'   => $contract->wallet_address,
                    'bc_sc_id'         => $contract->id,
                ];
            }

            // return [
            //     'contract_address' => $contract_address,
            //     'wallet_address'   => $bc_wallet_address,
            //     'bc_sc_id'         => null,
            // ];
        }

        // Failure case
        return [
            'contract_address' => null,
            'wallet_address'   => null,
            'bc_sc_id'         => null,
            'error'            => 'Blockchain contract deployment failed',
        ];
    }





    /**************** Mandar changes 03/10/2024 *****************/
    public static function generateFileHash($filePath){

        $fileContent=file_get_contents($filePath);

        $hmacHash256 = hash_hmac('sha3-256', $fileContent, \Config::get('constant.EStamp_Salt'));

        return $hmacHash256;

    }
    


    public static function updateContractCount($contract_id,$student_id)
    {
         
        $limit = config('constants.BLOCKCHAIN_DOCUMENT_PER_CONTRACT_LIMIT');
        try {
 
            // Step 1: Validate input
            if (empty($contract_id)) {
                Log::error("Input validation failed: Missing or invalid parameters", [
                    'contract_id' => $contract_id
                ]);
                return ['success' => false, 'message' => "Failed to update contract count. Please contact the administrator."];
            }
            //  $student = StudentTable::where('id',$student_id)->first();
            $student = StudentTable::where('id', $student_id)->where('status',1)->first();
            if(!empty($student)){
                 
                $student->bc_sc_id = $contract_id;
                $student->save();
            }else{
                Log::error("Student  not found", [
                    'student_id' => $student_id
                ]);
            }
            // Step 2: Fetch the contract from the database
            $contract = BcSmartContract::find($contract_id);
     
            if (!$contract) {
                Log::error("Contract not found", [
                    'contract_id' => $contract_id
                ]);
                return ['success' => false, 'message' => "Contract not found."];
            }
            $total_count = $contract->count + 1;
            // Increment the count for the current contract
            $contract->count = $total_count;
            $contract->updated_at = now();
            $contract->save();

            Log::info("Contract count updated successfully", [
                'contract_id' => $contract_id,
                'updated_count' => $contract->count
            ]);

            return [
                'success' => true,
                'message' => "Contract count updated successfully.",
                'updated_count' => $contract->count
            ];
        } catch (\Exception $e) {
            // Step 4: Handle any exceptions
            Log::error("Error updating contract count: " . $e->getMessage(), [
                'exception' => $e
            ]);
            return ['success' => false, 'message' => "Failed to update contract count. Please contact the administrator."];
        }
    }


    public static function migrationMintPDF($data){//NORMALTEMPLATE, PDF2PDFTEMPLATE
        
        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);

        $response=["status"=>0];
        $mintData=[];

        if(!empty($data['pdf_file'])&&!empty($data['uniqueHash'])&&!empty($data['template_id'])){


            if(isset($data['bc_contract_address'])){
                $checkContact['bc_contract_address']=$data['bc_contract_address'];
            }
            
            if($checkContact&&!empty($checkContact['bc_contract_address'])){
                
                $mintData['pdf_file']=$data['pdf_file'];
                
                //Fetch from Demo sites table
                $mintData['walletID']=$data['walletID'];

                //Fetch from Template Master
                $mintData['smartContractAddress']=$checkContact['bc_contract_address'];

                $mintData['documentType']=(!empty($data['documentType']))?$data['documentType']:"";
                $mintData['description']=(!empty($data['description']))?$data['description']:"";
                $mintData['uniqueHash']=$data['uniqueHash'];

                $mintData['storageIdentifier']='lighthouse';

                for($i=1;$i<=5;$i++){
                
                    
                    if(isset($data['metadata'.$i])){
                        $mintData['metadata'.$i]=$data['metadata'.$i];    
                    }

                }
                
                
                $mode =1;

                
                $response=BlockChainV1::mintData($mode,$mintData);    
                
                if($response&&$response['status']==200&&isset($response['txnHash'])&&!empty($response['txnHash'])){
                    $datetime  = date("Y-m-d H:i:s");
                    BlockChainMintData::create(['txn_hash'=>$response['txnHash'],'gas_fees'=>$response['gasPrice'],'token_id'=>$response['tokenID'],'key'=>$mintData['uniqueHash'],'ipfs_hash'=>$response['ipfsHash'],'created_at'=>$datetime]);

                    if(isset($response['ipfsHash'])){
                        $response=["status"=>$response['status'],"txnHash"=>$response['txnHash'],"ipfsHash"=>$response['ipfsHash'],"pinataIpfsHash"=>$response['pinataIpfsHash'],"metadata_ipfs_hash"=>$response['metadata_ipfs_hash'],"bc_sc_id"=>$bc_sc_id ,'token_id'=>$response['tokenID'] ];
                    }else{
                        $response=["status"=>$response['status'],"txnHash"=>$response['txnHash'],"metadata_ipfs_hash"=>$response['metadata_ipfs_hash'],"bc_sc_id"=>$bc_sc_id,'token_id'=>$response['tokenID']];
                    }
                    

                }
            }
        }
        return $response;
    }


}