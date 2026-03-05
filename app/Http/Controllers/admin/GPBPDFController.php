<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use TCPDF;
//use phpseclib2\Crypt\AES;
//use phpseclib\phpseclib\phpseclib\Crypt\AES;

// use phpseclib\phpseclib\phpseclib\Crypt\AES;
use App\Utility\GibberishAES;
use TCPDF_FONTS;
use App\models\StudentTable;
use App\Helpers\CoreHelper;
use DB;
use Storage;
use App\Utility\AesCipher;


//phpseclib\phpseclib\phpseclib

class GPBPDFController extends Controller
{
    

    public function encrypt()
    {
        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);
        
        $path = "E:\\wamp64\\www\\uneb\\public\\request_file\\GPB\\CBREQP_O_24_24012024";
        $pdf_content = file_get_contents($path);
        
        
        $encryptedString= AesCipher::encrypt('rtYrtEofjtg7676h',$pdf_content);
        file_put_contents("E:\\wamp64\\www\\uneb\\public\\request_file\\GPB\\request_file_encrypted.txt", $encryptedString);
        echo "encryption success";
        die();

    }


    public function decrypt()
    {
        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);

        $path = "E:\\wamp64\\www\\uneb\\public\\request_file\\GPB\\PC24012024.SEQ";
        // $path = "E:\\wamp64\\www\\uneb\\public\\request_file\\GPB\\request_file_encrypted.txt";
        $pdf_content = file_get_contents($path);
        

        // // Read the encrypted file
        // $base64Encrypted = file_get_contents($path);

        // // Base64 decode the encrypted data
        // $encryptedData = base64_decode($base64Encrypted);

        // // Your secret key (should be 16, 24, or 32 bytes)
        // $secretKey = "rtYrtEofjtg7676h";

        // // Your initialization vector (should be 16 bytes)
        // $iv = substr($encryptedData, 0, 16);

        // // Create the cipher
        // $decrypted = openssl_decrypt($encryptedData, 'AES-256-CFB', $secretKey, 0, $iv);

        // // Output the decrypted data
        // echo $decrypted;

        //echo base64_decode($pdf_content);

        //echo $pdf_content;
        // die();
        
        $encryptedString= AesCipher::decrypt('rtYrtEofjtg7676h',$pdf_content);
        // echo $encryptedString;
        // die();
        file_put_contents("E:\\wamp64\\www\\uneb\\public\\request_file\\GPB\\gpb_request_file_decrypted.txt", $encryptedString);
        echo "decryption success";
        die();

    }

    


   
}
