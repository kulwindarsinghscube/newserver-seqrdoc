<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

// include(app_path('Services/SftpService.php'));
use App\Helpers\CoreHelper;
// use App\Services\SftpService;

class SFTPController extends Controller
{
    
    // protected $sftpService;

    public function __construct()
    {
        // $this->sftpService = new SftpService();
    }


    public function index()
    {
        

        


        $result = CoreHelper::SFTPConnect();


        return response()->json(['result' => $result]);
        // $sftp = new Net_SFTP('34.93.122.82');
        // if (!$sftp->login('sftpuser3', 'Hubs2228&@12')) {
        //     echo 'Login Failed';
        // } else {
        //     echo 'Login Success';
        //     echo "<br>";
        // }
        
    




    }

    public function listFiles()
    {
        $files = $this->sftpService->listFiles('/remote/path');
        return response()->json($files);
    }

    public function downloadFile()
    {
        $this->sftpService->downloadFile('/remote/path/file.txt', storage_path('app/local_file.txt'));
    }

    public function uploadFile()
    {
        // $this->sftpService->uploadFile(storage_path('app/local_file.txt'), '/remote/path/file.txt');

        $localFile = public_path().'/'.'anu/backend/pdf_file/SD_01489.pdf';
        $localFileName = basename($localFile, '.pdf');

        // echo $localFileName;
        // die();
        // $serverFile = '/var/www/verify.anu.edu.in/verify/seqrdoc/pdf_file/';


        // $result = putFtpServerFile($localFile);
        $result = CoreHelper::SFTPUpload($localFile);

    }
    
    
}
