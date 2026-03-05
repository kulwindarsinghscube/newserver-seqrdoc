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
use DPDF;
//phpseclib\phpseclib\phpseclib
use Dompdf\Dompdf;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Carbon\Carbon;

class PDFController extends Controller
{

    public function testPdfold(){

        
    }
    public function PCPTesting() {

        
        $encryptedData = file_get_contents(public_path().'\\'."e_Meeseva_Certificate_encrypted_data.txt");
        
        // echo $encryptedData;

        $encryptionKey = 'G7b4y$D8kqT3pF9@H2LmX7wRzC5NvQ3!'; // Load encryption key from environment
        $decryptedData = $this->decrypt_data($encryptedData, $encryptionKey);

        file_put_contents(public_path().'\\'."test_Decry[ptyed_e_Meeseva_Certificate_data.txt",$decryptedData);
        echo $decryptedData;
    }

    
    private function encrypt_data($b64Doc, $key)
    {
        // Define a static IV (16 bytes for AES-256-CBC)
        $iv = "1234567890abcdef"; // Example static IV (should be exactly 16 bytes)
        $cipher = 'AES-256-CBC';
        // Encrypt the data
        $compressedData = gzcompress($b64Doc);
        $encryptedData = openssl_encrypt($compressedData, $cipher, $key, 0, $iv);
        // Combine the IV and encrypted data for transmission
        return $encryptedData;
    }

    private function decrypt_data($encryptedData, $key)
    {
        $cipher = 'AES-256-CBC';
        $iv = "1234567890abcdef"; // The same static IV used during encryption

        // Decrypt the data using the same cipher, key, and IV
        $decryptedData = openssl_decrypt($encryptedData, $cipher, $key, 0, $iv);

        // Decompress the data using gzuncompress
        $originalData = gzuncompress($decryptedData);

        return $originalData;
    }



    public function customEncryption()
    {   

      

        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);

        // $encryptedpdfFile = 'C:\wamp64\www\uneb\public\demo\backend\pdf_file\69267.pdf';
        $encryptedpdfFile = 'C:\wamp64\www\uneb\public\69267.pdf';
        $decryptedPdfPath = public_path().'\encry_69267.pdf';
        // file_put_contents($decryptedPdfPath, '');

        // $pdf_content = file_get_contents($encryptedpdfFile);
        // $decryptedContent= GibberishAES::dec($pdf_content, \Config::get('constant.EStamp_Salt'));
        
        // file_put_contents($decryptedPdfPath, $decryptedContent);

        // // Return a response or redirect to the encrypted PDF
        // return response()->download($decryptedPdfPath, 'Deencrypted_69267.pdf');




        // Python 
        $directoryUrlBackward="C:\\wamp64\\www\\uneb\\public\\pdf2pdf\\";
        $pyscript = $directoryUrlBackward."Python_files\\encryptCustomScript.py";

        $cmd = "$pyscript $encryptedpdfFile $decryptedPdfPath 2>&1"; // 
        
        // echo 'Python '. $cmd;
        exec('Python '. $cmd, $output, $return);
        die();
        // Return a response or redirect to the encrypted PDF
        // return response()->download($decryptedPdfPath, 'Encrypted_69267.pdf');

    }


    public function customDecryption()
    {   

        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);

        // $encryptedpdfFile = 'C:\wamp64\www\uneb\public\demo\backend\pdf_file\69267.pdf';
        $encryptedpdfFile = public_path().'\encry_69267.pdf';
        $decryptedPdfPath = public_path().'\decry_69267.pdf';
        // file_put_contents($decryptedPdfPath, '');

        // $pdf_content = file_get_contents($encryptedpdfFile);
        // $decryptedContent= GibberishAES::dec($pdf_content, \Config::get('constant.EStamp_Salt'));
        
        // file_put_contents($decryptedPdfPath, $decryptedContent);

        // // Return a response or redirect to the encrypted PDF
        // return response()->download($decryptedPdfPath, 'Deencrypted_69267.pdf');




        // Python 
        $directoryUrlBackward="C:\\wamp64\\www\\uneb\\public\\pdf2pdf\\";
        $pyscript = $directoryUrlBackward."Python_files\\decryptCustomScript.py";

        $cmd = "$pyscript $encryptedpdfFile $decryptedPdfPath 2>&1"; // 
        
        // echo 'Python '. $cmd;
        exec('Python '. $cmd, $output, $return);
        // die();
        // Return a response or redirect to the encrypted PDF
        // return response()->download($decryptedPdfPath, 'Deencrypted_69267.pdf');

    }

    public function pdfTextWwrap() {

        $pdf = new TCPDF('P', 'mm', array('210', '297'), true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('TCPDF');
        $pdf->SetTitle('CERTIFICATE');
        $pdf->SetSubject('');

        // remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetAutoPageBreak(false, 0);


        $pdf->AddPage();

        //set fonts
        $arial = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Arial.TTF', 'TrueTypeUnicode', '', 96);
        $arialb = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Arialb.TTF', 'TrueTypeUnicode', '', 96);
        $ariali = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\ARIALI.TTF', 'TrueTypeUnicode', '', 96);


        // Define the text and the maximum width
        $text = "This is some example text that should fit within a specified width.";
        $max_width = 100; // Maximum width in mm

        // Set the initial font size
        $font_size = 11; // You can set an initial size as needed
        // $pdf->SetFont('helvetica', '', $font_size);

        // Calculate the width of the text with the current font size
        $text_width = $pdf->GetStringWidth($text);

        // Loop to reduce the font size until the text fits within the max width
        while ($text_width > $max_width && $font_size > 1) {
            $font_size--; // Decrease the font size
            // $pdf->SetFont('helvetica', '', $font_size); // Set the new font size
            $text_width = $pdf->GetStringWidth($text); // Recalculate the width
        }

        // Output the text with the final font size
        // $pdf->Cell($max_width, 10, $text, 1, 1);

        $pdf->MultiCell($max_width, 0, $text, 1, "L", 0, 0, '', '', true, 0, true);

        $pdf->output('test-wrap.pdf','I');

    }

    public function generateEncryptedPDF()
    {
        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);

        // Create a new TCPDF instance
        // $pdf = new TCPDF();

        $pdf = new TCPDF('P', 'mm', array('210', '297'), true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('TCPDF');
        $pdf->SetTitle('CERTIFICATE');
        $pdf->SetSubject('');

        // remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetAutoPageBreak(false, 0);

        // $pdf->SetProtection(
        //     ['print', 'modify', 'copy', 'annot-forms', 'fill-forms', 'extract', 'assemble', 'print-high'],
        //     'test123', 'test456', 3
        // );

        // Add a page
        $pdf->AddPage();

        //set fonts
        $arial = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Arial.TTF', 'TrueTypeUnicode', '', 96);
        $arialb = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Arialb.TTF', 'TrueTypeUnicode', '', 96);
        $ariali = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\ARIALI.TTF', 'TrueTypeUnicode', '', 96);
        

        $arialNarrow = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\arialn.TTF', 'TrueTypeUnicode', '', 96);
        $arialNarrowB = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\ARIALNB.TTF', 'TrueTypeUnicode', '', 96);
        $times = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Times-New-Roman.ttf', 'TrueTypeUnicode', '', 96);
        $timesb = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Times-New-Roman-Bold.ttf', 'TrueTypeUnicode', '', 96);
        $trebuc = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\trebuc.ttf', 'TrueTypeUnicode', '', 96);
        $trebucb = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\TREBUCBD.ttf', 'TrueTypeUnicode', '', 96);
        $trebuci = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Trebuchet-MS-Italic.ttf', 'TrueTypeUnicode', '', 96);
        $graduateR = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\graduate-regular.ttf', 'TrueTypeUnicode', '', 96);
        $poppinsR = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Poppins Regular 400.ttf', 'TrueTypeUnicode', '', 96);
        $poppinsM = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Poppins Medium 500.ttf', 'TrueTypeUnicode', '', 96);

        $urdu = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\ARIALUNI.ttf', 'TrueTypeUnicode', '', 32);

        // Add your content to the PDF (replace with your own content)
        $pdf->SetFont($arial, '',11, '', false);
        $pdf->SetXY(0, 39);
        $pdf->MultiCell(210, 0, "JAMHUURIYADDA FEDERAALKA SOOMAALIYA", 0, "C", 0, 0, '', '', true, 0, true);

        $pdf->SetXY(0, 44);
        $pdf->MultiCell(210, 0, "FEDERAL REPUBLIC OF SOMALIA", 0, "C", 0, 0, '', '', true, 0, true);

        $pdf->SetFont($arialb, '',11, '', false);
        $pdf->SetXY(0, 51);
        $pdf->MultiCell(210, 0, "TEMPORARY TRAVEL DOCUMENT", 0, "C", 0, 0, '', '', true, 0, true);
        
       

        $pdfContent = $pdf->Output('', 'S');

        // print_r($pdfContent);

        $encryptedString= GibberishAES::enc($pdfContent, \Config::get('constant.EStamp_Salt'));

        // print_r($encryptedString);
        // die();
        $encryptedPdfPath = storage_path('app/encrypted_pdf.pdf');
        file_put_contents($encryptedPdfPath, $encryptedString);
 
        // Return a response or redirect to the encrypted PDF
        return response()->download($encryptedPdfPath, 'encrypted_pdf.pdf');
        

        // -----------  Testing Code ----------- //
        
        // file_put_contents('test/output.pdf', $pdf->Output('', 'S'));


        // Generate the PDF as a string
        //$pdfContent = $pdf->Output('S');

        // $certName = "TESTpDF.pdf";
            
        // $myPath = public_path().'/test';
        // $dt = date("_ymdHis");
        
        // $pdf->output($myPath . DIRECTORY_SEPARATOR . $certName, 'F');

        // Generate the PDF as a string
        // $pdf->Output('I');
        //$pdf->output('watermark.pdf','I');
        //echo $pdfContent;
        //die();
        //die();
        // Encrypt the PDF content using AES



        // $key = \Config::get('constant.EStamp_Salt'); // Replace with your own encryption key
        // $aes = new \phpseclib\phpseclib\phpseclib\Crypt\AES();
        // echo $aes;
        // die();
        // $aes->setKey($key);


        // $encryptedContent = $aes->encrypt($pdfContent);

        // // $encryptedContent= GibberishAES::enc($pdfContent, \Config::get('constant.EStamp_Salt'));

        // // Save the encrypted PDF to a file or return as a response
        // $encryptedPdfPath = storage_path('app/encrypted_pdf.pdf');
        // file_put_contents($encryptedPdfPath, $encryptedContent);

        // // Return a response or redirect to the encrypted PDF
        // return response()->download($encryptedPdfPath, 'encrypted_pdf.pdf');
    }


    public function oldgenerateDecryptedPDF()
    {
        $encryptedPdfPath = storage_path('app/encrypted_pdf.pdf');

        $pdf_content = file_get_contents($encryptedPdfPath);
        $decryptedContent= GibberishAES::dec($pdf_content, \Config::get('constant.EStamp_Salt'));

        $decryptedPdfPath = storage_path('app/Deencrypted_pdf.pdf');
        file_put_contents($decryptedPdfPath, $decryptedContent);

        // Return a response or redirect to the encrypted PDF
        return response()->download($decryptedPdfPath, 'Deencrypted_pdf.pdf');

        
        // $pdf->output('Decrypt.pdf','I');

    }

    public function view()
    {
        return view('admin.pdfView');
        
    }

    public function displayPdf($qrKey)
    {
        
        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);
        $qrData = $qrKey;
        
        $result= StudentTable::where('key',$qrData)->where('status','=', 1)->first();
        // echo "<pre>";
        // print_r($result);
        // echo "<pre>";
        // die();

        if($result) {
            $certName = $result->certificate_filename;
            $encryptedPdfPath = public_path().'/'.$subdomain[0].'/backend/pdf_file/'.$certName;
            
            $pdf_content = file_get_contents($encryptedPdfPath);
            // echo "<pre>";
            // print_r($pdf_content);
            // echo "</pre>";
            $encryptedContent = file_get_contents($encryptedFilePath);

            // Decrypt the content using GibberishAES
            $decryptedContent = GibberishAES::dec($encryptedContent,  \Config::get('constant.EStamp_Salt'));
            echo $decryptedContent;

            die();
            // test
            $password = \Config::get('constant.EStamp_Salt');
            $decryptedData = openssl_decrypt($pdf_content, 'aes-256-cbc', $password, 0, $password);


            // $secret_key = base64_decode(\Config::get('constant.EStamp_Salt'));
            // $encrypt_method ="AES-256-CBC";
            // // $data = json_decode(base64_decode($pdf_content),true);
            // // $data['iv'] = base64_decode($data['iv']);
            // // $data['value'] = base64_decode($data['value']);
            // $decryptedContent = openssl_decrypt($pdf_content, $encrypt_method, $secret_key, OPENSSL_RAW_DATA|OPENSSL_ZERO_PADDING, $data['iv']);

            //test
            echo  $decryptedData;
            die();
            

            $decryptedContent= GibberishAES::dec(base64_decode($pdf_content), \Config::get('constant.EStamp_Salt'));
            return response($decryptedContent)->header('Content-Type', 'application/pdf');
            
            
            

        } else {
            http_response_code(404);
            echo "<h1 style='padding-top: 50; font-size:40px;text-align: center;
            color: indianred;
            min-height: 500px;
            background-color: #f0f0f0;
            border: 2px solid #dbdbdb;'>Server Error 404 - Page Not Found!</h1>";
        }


    }

    public function encrypt()
    {
        $path = "E:\\wamp64\\www\\uneb\\public\\rohit_testing\\TEST004.pdf";
        // $pdf_content = file_get_contents($path);
        // //echo base64_encode($pdf_content);
        // $pddfCo = base64_encode($pdf_content);
        
        // file_put_contents("E:\\wamp64\\www\\uneb\\public\\rohit_testing\\base64_text_php.txt", $pddfCo);
        // // file_put_contents("E:\\wamp64\\www\\pdf_script\\selva\\b64_data_rohit.txt", $pddfCo);
       // $textpath = 'E:\\wamp64\\www\\uneb\\public\\rohit_testing\\base64_text_php.txt';

        $content = file_get_contents($path);

        $encryptedString = GibberishAES::enc($content, 'AJITNATH');
        file_put_contents("E:\\wamp64\\www\\uneb\\public\\rohit_testing\\testing\\testing_encrypted_text_php.txt", $encryptedString);
        die();
       
       
        die();
        
        file_put_contents("E:\\wamp64\\www\\uneb\\public\\test\\TEST004_php_encrypted.txt", $encryptedString);
        die();
        file_put_contents("E:\\wamp64\\www\\uneb\\public\\test\\php_encrypted.pdf", $encryptedString);

        //echo $pdf_content;
    
    }

    public function decrypt_aes_base64($encrypted_data, $key, $iv) {
        $decrypted_data = openssl_decrypt(base64_decode($encrypted_data), 'aes-128-cbc', $key, OPENSSL_RAW_DATA, $iv);
        return $decrypted_data;
    }

    public function decrypt()
    {

        // Encrypted 
        $password = 'G7b4y$D8kqT3pF9@H2LmX7wRzC5NvQ3!';
        $method = "AES-256-CBC";
        $key = hash('sha256', $password, true); // Derive a 256-bit key from the password
        $iv = openssl_random_pseudo_bytes(16);  // Generate a secure 16-byte IV

        $ciphertext = openssl_encrypt($plaintext, $method, $key, OPENSSL_RAW_DATA, $iv);
        $encrypted = base64_encode($iv . $ciphertext); // Concatenate IV with ciphertext

        echo 'Encrypted Data :  '.$encrypted;

        echo "<br>";
        echo "<br>";

        // $encrypted ='j63CH+HFDNR+4NJhYiGSCQnfG+A9kDNG9Cie9BHuXjzqjPqIiP…YNQ2Hy9JM8wV6YcYcNkThVqYEkPdS1PTMtlVxzW0kd3+fCQ==';

       
        $method = "AES-256-CBC";
        $key = hash('sha256', $password, true);
        $data = base64_decode($encrypted);
    
        $iv = substr($data, 0, 16);               // Extract IV from the data
        $ciphertext = substr($data, 16);          // Extract the actual ciphertext
    
        $plaintext = openssl_decrypt($ciphertext, $method, $key, OPENSSL_RAW_DATA, $iv);
        echo "Decrypted Text : ".$plaintext;
        die();




        $pdf_content = 'U2FsdGVkX19qhk0R6EM4j9Gy+Id0VobRNOhWbbyAZyMVMzMNlSkxr30JXfGoA3d7C9uOfwfFX7t4T/tMbcPHtGYpIVqZRxIhG4dB34Zr58wai1oEtrG6XHawSHjqyL9H';
        
        // echo htmlspecialchars($pdf_content);

        $decryptedString = GibberishAES::dec($pdf_content, 'AJITNATH');

        echo $decryptedString;
        // echo  htmlentities($decryptedString);
        die();

        // Example usage
        // $encrypted_data = file_get_contents('E:\\wamp64\\www\\uneb\\public\\rohit_testing\\testing\\encrypted_data_b64_python.txt');
        $encrypted_data = 'uC748uM6LdaHRvR2AhallE5PiGMcoIfCRsQsA+4w0cQ=';
        $key = 'omkar\0\0\0\0\0\0\0\0';
        $iv = '0123456789012345';

        // Decrypt the data
        $decrypted_data = $this->decrypt_aes_base64($encrypted_data, $key, $iv);

        // Output the decrypted data
        echo "Decrypted data: " . $decrypted_data . PHP_EOL;
        die();
        // Save the decrypted data to a file
        file_put_contents('E:\\wamp64\\www\\uneb\\public\\rohit_testing\\testing\\decrypted_data_php.txt', $decrypted_data);
        file_put_contents('E:\\wamp64\\www\\uneb\\public\\rohit_testing\\testing\\decrypted_data_php.pdf', $decrypted_data);




        //$path = "E:\\wamp64\\www\\uneb\\public\\test\\php_encrypted.pdf";
        $path = "E:\\wamp64\\www\\uneb\\public\\rohit_testing\\encrypted_data_b64_python.txt";
        $pdf_content = file_get_contents($path);
        $decryptedString = GibberishAES::dec($pdf_content, 'AJITNATH');
        file_put_contents("E:\\wamp64\\www\\uneb\\public\\rohit_testing\\testing\\decrypted_data_b64_python.txt", $decryptedString);
        file_put_contents("E:\\wamp64\\www\\uneb\\public\\rohit_testing\\testing\\decrypted_data_b64_python.pdf", $decryptedString);
        die();


        // echo base64_decode($pdf_content);
        
        // $decryptedString = GibberishAES::dec($pdf_content, 'AJITNATH');
        // echo $decryptedString;
        // die();
        // file_put_contents("E:\wamp64\www\pdf_script\selva\selva_decrypt.pdf", $decryptedString);
        die();

        // echo $encryptedString;
        // die();
        file_put_contents("E:\\wamp64\\www\\uneb\\public\\request_file\\GPB\\php_decrypted.txt", $encryptedString);
        // file_put_contents("E:\\wamp64\\www\\uneb\\public\\test\\php_decrypted.txt", $encryptedString);

    }

    public function olddecrypt()
    {
        //$path = "E:\\wamp64\\www\\uneb\\public\\test\\php_encrypted.pdf";
        $path = "E:\\wamp64\\www\\uneb\\public\\request_file\\GPB\\PC24012024.SEQ";
        $pdf_content = file_get_contents($path);
        echo $pdf_content;
        die();
        $encryptedString= GibberishAES::dec($pdf_content, 'AJITNATH');
        // echo $encryptedString;
        // die();
        file_put_contents("E:\\wamp64\\www\\uneb\\public\\request_file\\GPB\\php_decrypted.txt", $encryptedString);
        // file_put_contents("E:\\wamp64\\www\\uneb\\public\\test\\php_decrypted.txt", $encryptedString);

    }


    public function Pdf2PdfEncyptedPDF()
    {
        // 2ecdc8d57e950d66408782b59eea4f6f
        // echo $qrKey;
        // die();
        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);
        $encString = 'WyJ7XCJpZFwiOiBcIjFcIiwgXCJsYWJlbFwiOiBcIkNvbGxlZ2UgTmFtZVwiLCBcInZhbHVlXCI6IFwiRGVla3NoYW50YSBTYW1hcmFtYmhhIC0gMjAyNURlZWtzaGFudGEgU2FtYXJhbWJoYSAtIDIwMjVEZWVrc2hhbnRhIFNhbWFyYW1iaGEgLSAyMDI1XFx1ZmZmZFxcdTBjZDVcXHVmZmZkXFx1MGNiZVxcdTBjODJcXHUwY2E0IFxcdTBjYjhcXHVmZmZkXFx1MGNiZVxcdTBjYjBcXHUwYzgyXFx1MGNhZCAtIFxcdTBjZThcXHUwY2U2XFx1MGNlOFxcdTBjZWIgXCJ9IiwgIntcImlkXCI6IFwiM1wiLCBcImxhYmVsXCI6IFwiY291cnNlIG5hbWVcIiwgXCJ2YWx1ZVwiOiBcIk1hc3RlciBvZiBDb21tZXJjZSBcIn0iXQ==';
        $qrData = base64_decode($encString);
        
        echo $qrData;
        die();



        $path = "E:\\wamp64\\www\\uneb\\public\\demo\\backend\\pdf_file\\U19042.pdf";
        // $pdf_content = file_get_contents($path);
        $pdf_content = 'uC748uM6LdaHRvR2AhallE5PiGMcoIfCRsQsA+4w0cQ=';
        
        // echo $pdf_content;
        // die();
        $encryptedString= GibberishAES::dec($pdf_content, 'omkar');
        echo $encryptedString;
        // file_put_contents("E:\\wamp64\\www\\uneb\\public\\laravel_php_encrypted.pdf", $encryptedString);
        die();
        // $encryptedPath = "E:\\wamp64\\www\\uneb\\public\\demo\\documents\\17001A0591_2.pdf";
        
        // $pdf_content = file_get_contents($encryptedPath);


        // $encryptedString= GibberishAES::enc($pdf_content, \Config::get('constant.EStamp_Salt'));
        
        // file_put_contents("E:\\wamp64\\www\\uneb\\public\\laravel_php_encrypted.pdf", $encryptedString);

        // die();
        // Storage::disk('local')->put('file.txt', $pdf_content);
        
        // die();



        $path = "E:\\wamp64\\www\\uneb\\public\\demo\\documents\\17001A0591_2.pdf";
    
        $directoryUrlBackward="E:\\wamp64\\www\\uneb\\public\\pdf2pdf\\";
        $pyscript = $directoryUrlBackward."Python_files\\gibberish.py";
        
        $cmd = "$pyscript $path";
        // echo "C:\\Users\\ABC\\AppData\\Local\\Programs\\Python\\Python38\\python.exe " .$cmd;
        // die();
        echo "C:\\Users\\ABC\\AppData\\Local\\Programs\\Python\\Python38\\python.exe " .$cmd;
        echo "<br>";
        exec("C:\\Users\\ABC\\AppData\\Local\\Programs\\Python\\Python38\\python.exe " .$cmd, $output, $return);
        print_r($output);
        //$path = "E:\wamp64\www\uneb\public\demo\documents\\".$serNo."_2.pdf";
        // echo $path;
        // die();
        // $pdf_content = file_get_contents($encryptedPdfPath);
        // $decryptedContent= GibberishAES::dec($pdf_content, \Config::get('constant.EStamp_Salt'));

        

    }

    public function Pdf2PdfDecryptedPDF($qrKey)
    {
        // 2ecdc8d57e950d66408782b59eea4f6f
        // echo $qrKey;
        // die();
        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);
        //$qrData = base64_decode($qrKey);
        
        $result= StudentTable::where('key',$qrKey)->where('status','=', 1)->first();
        // print_r($result);
        // die();

        if($result) {
            
            $serNo = $result->serial_no;
            $certName = $result->certificate_filename;
            $encryptedPdfPath = public_path().'/'.$subdomain[0].'/backend/pdf_file/'.$certName;
            
            $directoryUrlBackward="E:\\wamp64\\www\\uneb\\public\\pdf2pdf\\";
            $pyscript = $directoryUrlBackward."Python_files\\decryptScript.py";
            
            $cmd = "$pyscript $encryptedPdfPath $serNo";
            // echo "C:\\Users\\ABC\\AppData\\Local\\Programs\\Python\\Python38\\python.exe " .$cmd;
            // die();
            exec("C:\\Users\\ABC\\AppData\\Local\\Programs\\Python\\Python38\\python.exe " .$cmd, $output, $return);

            $path = "E:\wamp64\www\uneb\public\demo\documents\\".$serNo."_2.pdf";
            // echo $path;
            // die();
            // $pdf_content = file_get_contents($encryptedPdfPath);
            // $decryptedContent= GibberishAES::dec($pdf_content, \Config::get('constant.EStamp_Salt'));

            return response()->file($path);
            // return response($path)->header('Content-Type', 'application/pdf');
        } else {
            http_response_code(404);
            echo "<h1 style='padding-top: 50; font-size:40px;text-align: center;
            color: indianred;
            min-height: 500px;
            background-color: #f0f0f0;
            border: 2px solid #dbdbdb;'>Server Error 404 - Page Not Found!</h1>";
        }


    }





    public function OldgeneratePDF(Request $request)
    {

        

        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);

        //$encryptedPdfPath = storage_path('app/encrypted_pdf.pdf');
        $qrData = 'ABB78B24F1417E861E7C240E72603881';
        echo $qrData;

        echo "<br>";
        echo "<br>";

        $decryptedContent1= GibberishAES::dec($qrData, \Config::get('constant.EStamp_Salt'));
        // return $decryptedContent1;
        print_r($decryptedContent1);

        echo $decryptedContent1;
        die();

        $result= StudentTable::where('key',$qrData)->where('status','=', 1)->first();
        
        // echo "<pre>";
        // print_r($result->certificate_filename);
        // echo "</pre>";
        // die();

        $certName = $result->certificate_filename;
        $encryptedPdfPath = public_path().'/'.$subdomain[0].'/backend/pdf_file/'.$certName;

        $pdf_content = file_get_contents($encryptedPdfPath);
        $decryptedContent= GibberishAES::dec($pdf_content, \Config::get('constant.EStamp_Salt'));


        return response($decryptedContent)->header('Content-Type', 'application/pdf');

        // $decryptedPdfPath = public_path().'/test/'.$certName;
        // file_put_contents($decryptedPdfPath, $decryptedContent);

        // Return a response or redirect to the encrypted PDF
        // echo $decryptedPdfPath;

        //return response()->file($decryptedPdfPath);

        // return view($decryptedPdfPath);
        // return response()->download($decryptedPdfPath, $certName);

    }

    // function microLine($text, $width, $microFontSize, $subdomain ){

    // }

    function microLine($text, $w, $fontSize, $subdomain)
    {
        $str=strtoupper($text);							
        // $str = preg_replace('/\s+/', '', $str); 
        $textArray = imagettfbbox($fontSize, 0,public_path().'/'.$subdomain.'/backend/canvas/fonts/Arial.TTF', $str);
        $strWidth = ($textArray[2] - $textArray[0]);
        // $strHeight = $textArray[6] - $textArray[1] / 5;
        $strHeight = $textArray[6] - $textArray[1] / 1.4;
                            
                            
        $width=$w;
        // $latestWidth = round($width*3.2);
        $latestWidth = round($width*3.7795280352161);
         //Updated by Mandar
        $microlinestr=$str;
        $microlinestrLength=strlen($microlinestr);
        //width per character
        $microLinecharacterWd =$strWidth/$microlinestrLength;
        //Required no of characters required in string to match width
        $microlinestrCharReq=$latestWidth/$microLinecharacterWd;
        $microlinestrCharReq=round($microlinestrCharReq);
        //No of time string should repeated
        $repeateMicrolineStrCount=$latestWidth/$strWidth;
        $repeateMicrolineStrCount=round($repeateMicrolineStrCount)+1;
        //Repeatation of string 
        $microlinestrRep = str_repeat($microlinestr, $repeateMicrolineStrCount);                            
        //Cut string in required characters (final string)
        $arrayEnrollment = substr($microlinestrRep,0,$microlinestrCharReq);	   

        return $arrayEnrollment;
        
    }

    public function microLineV1($pdfBig,$text,$width) {
        // $text = "DHANA LAXMI"; // Your text
        $widthMM = $width; // Required width in mm
        $widthPt = $widthMM * 2.83465; // Convert mm to pt

        // Get string width in TCPDF
        $strWidth = $pdfBig->GetStringWidth($text);

        // Ensure at least one repetition
        $repeatCount = max(1, ceil($widthPt / $strWidth));

        // Generate repeated string
        $finalText = str_repeat($text, $repeatCount);
        return $finalText;
    }


    public function waterMarkText($pdfBig,$x,$y,$text) {
        $startColor = [253, 220, 190];   // Light Peach
        $middleColor = [192, 226, 202];  // Light Green
        $endColor = [253, 220, 190]; 

        $textLength = strlen($text);
        $x = $x;
        $y = $y;
        //clipping
        $xClip=5;
        $yClip=$y-0.5;
        $wClip=137.8;
        $hClip=5;
        // Start clipping.      
        // $pdf->StartTransform();
        $pdfBig->StartTransform();

        // // Draw clipping rectangle to match html cell.
        // $pdf->Rect($xClip, $yClip, $wClip, $hClip, 'CNZ');
        $pdfBig->Rect($xClip, $yClip, $wClip, $hClip, 'CNZ');
        for ($i = 0; $i < $textLength; $i++) {
            $char = $text[$i];
            if ($i < $textLength / 2) { // First half of the text
                $progress = $i / ($textLength / 2 - 1); //progress from 0 to 1
                if($textLength == 1){$progress = 1;} //handle single character string.

                $r = $startColor[0] + ($middleColor[0] - $startColor[0]) * $progress;
                $g = $startColor[1] + ($middleColor[1] - $startColor[1]) * $progress;
                $b = $startColor[2] + ($middleColor[2] - $startColor[2]) * $progress;
            } else { // Second half of the text
                $progress = ($i - $textLength / 2) / ($textLength / 2 - 1);
                if($textLength == 1){$progress = 1;}//handle single character string.
                $r = $middleColor[0] + ($endColor[0] - $middleColor[0]) * $progress;
                $g = $middleColor[1] + ($endColor[1] - $middleColor[1]) * $progress;
                $b = $middleColor[2] + ($endColor[2] - $middleColor[2]) * $progress;
            }
            
            $pdfBig->SetTextColor($r, $g, $b);
            $pdfBig->Text($x, $y, $char);
            $charWidth = $pdfBig->GetStringWidth($char);
            $x += $charWidth;
        }
        $pdfBig->StopTransform();
        //stop clipping
        return $pdfBig;

    }

    public function testPdf(){
        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);
        
        
        // $ghostImgArr = array();
        // $pdfBig = new TCPDF('P', 'mm', array('148', '210'), true, 'UTF-8', false);

        // $pdfBig->SetCreator(PDF_CREATOR);
        // // $pdfBig->SetCreator('TCPDF');
        // $pdfBig->SetAuthor('TCPDF');
        // $pdfBig->SetTitle('Certificate');
        // $pdfBig->SetSubject('');

        // // remove default header/footer
        // $pdfBig->setPrintHeader(false);
        // $pdfBig->setPrintFooter(false);
        // $pdfBig->SetAutoPageBreak(false, 0);

        // // add spot colors
        // $pdfBig->AddSpotColor('Spot Red', 30, 100, 90, 10);        // For Invisible
        // $pdfBig->AddSpotColor('Spot Dark Green', 100, 50, 80, 45); // clear text on bottom red and in clear text logo

        // $arial = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Arial.TTF', 'TrueTypeUnicode', '', 96);
        // $arialb = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Arialb.TTF', 'TrueTypeUnicode', '', 96);
    
        // $palatino = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\PALA.TTF', 'TrueTypeUnicode', '', 96);
        // $palatinob = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\PALAB.TTF', 'TrueTypeUnicode', '', 96);
        // $palatinobi = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\PALABI.TTF', 'TrueTypeUnicode', '', 96);
        // $times = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\times.TTF', 'TrueTypeUnicode', '', 96);
        // $Mandali_Regular = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\NotoSansTeluguRegular.ttf', 'TrueTypeUnicode', '', 96);
    
        // $teluguFont = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\telugu\Gidugu.ttf', 'TrueTypeUnicode', '', 96);
        
        // $pdfBig->AddPage();
    


        // // $teluguText = 'ధన లక్ష్మి';
        // $pdfBig->SetXY(30, 50);
        
        // $teluguText = mb_convert_encoding('ధన లక్ష్మ', 'UTF-8', 'auto');
        // $pdfBig->SetFont($teluguFont, "",32, false); 

        // $pdfBig->MultiCell(100, 10, $teluguText, 0, 'L', 0, 1, '', '', true);
        // // $pdfBig->writeHTML($teluguText, true, 0, true, 0);

        // $pdfBig->Output('sample.pdf', 'I');   

        // die();
        // //set background image
        // $template_img_generate = public_path().'\\'.$subdomain[0].'\backend\canvas\bg_images\\0764 Telangana Ration Card Digital ID Card_BG.jpg';   
            
        // $pdfBig->setCellPaddings( $left = '0', $top = '', $right = '', $bottom = '');
        // $pdfBig->Image($template_img_generate, 0, 0, '148', '210', "JPG", '', 'R', true);      
        // $pdfBig->setPageMark(); 


        // // First Watermark
        

        // $widthText = '138';
        // $watermakrFontSize = 6.355;

        // $firstLine = '365371585680 ';

        // // $topWatermarktext = $this->microLine($firstLine, $widthText, $watermakrFontSize, $subdomain[0]);
        // $topWatermarktext = $this->microLineV1($pdfBig,$firstLine,69);
        
        // $pdfBig->SetFont('arial', '', 6.355);
        // $startColor = [253, 220, 190];   // Light Peach
        // $middleColor = [192, 226, 202];  // Light Green
        // $endColor = [253, 220, 190];     // Light Peach Again
        // $text = $topWatermarktext;
        // $textLength = strlen($text);
        // $x = 5;
        // $y = 9.5;
        // //clipping
        // $xClip=5;
        // $yClip=9.4;
        // $wClip=137.8;
        // $hClip=5;
        // // Start clipping.      
        // // $pdf->StartTransform();
        // $pdfBig->StartTransform();

        // // // Draw clipping rectangle to match html cell.
        // // $pdf->Rect($xClip, $yClip, $wClip, $hClip, 'CNZ');
        // $pdfBig->Rect($xClip, $yClip, $wClip, $hClip, 'CNZ');
        // for ($i = 0; $i < $textLength; $i++) {
        //     $char = $text[$i];
        //     if ($i < $textLength / 2) { // First half of the text
        //         $progress = $i / ($textLength / 2 - 1); //progress from 0 to 1
        //         if($textLength == 1){$progress = 1;} //handle single character string.

        //         $r = $startColor[0] + ($middleColor[0] - $startColor[0]) * $progress;
        //         $g = $startColor[1] + ($middleColor[1] - $startColor[1]) * $progress;
        //         $b = $startColor[2] + ($middleColor[2] - $startColor[2]) * $progress;
        //     } else { // Second half of the text
        //         $progress = ($i - $textLength / 2) / ($textLength / 2 - 1);
        //         if($textLength == 1){$progress = 1;}//handle single character string.
        //         $r = $middleColor[0] + ($endColor[0] - $middleColor[0]) * $progress;
        //         $g = $middleColor[1] + ($endColor[1] - $middleColor[1]) * $progress;
        //         $b = $middleColor[2] + ($endColor[2] - $middleColor[2]) * $progress;
        //     }
            
        //     $pdfBig->SetTextColor($r, $g, $b);
        //     $pdfBig->Text($x, $y, $char);
        //     $charWidth = $pdfBig->GetStringWidth($char);
        //     $x += $charWidth;
        // }
        // $pdfBig->StopTransform();
        // // Stop clipping.
        // //////////////////////////////////////////
        
        // // Second Watermark
        // $widthText = '138';
        // $watermakrFontSize = 6.355;

        // $firstLine = '365371585680 ';

        // $secondWatermarktext = $this->microLine($firstLine, $widthText, $watermakrFontSize, $subdomain[0]);
        // $pdfBig->SetFont('arial', '', 6.355);
        // // $startColor = [253, 220, 190];   // Light Peach
        // // $middleColor = [192, 226, 202];  // Light Green
        // // $endColor = [253, 220, 190];     // Light Peach Again
        // $text = $secondWatermarktext;

        // // $result = $this->waterMarkText($pdfBig,5,163.5,$text);



        // // Third Watermark
        // $widthText = '138';
        // $watermakrFontSize = 6.355;

        // $firstLine = 'DHANA LAXMI ';

        // $secondWatermarktext = $this->microLine($firstLine, $widthText, $watermakrFontSize, $subdomain[0]);
        // $pdfBig->SetFont('arial', '', 6.355);
        
        // $text = $secondWatermarktext;

        // // $result = $this->waterMarkText($pdfBig,5,166.1,$text);




        // // Third Watermark
        // $widthText = '138';
        // $watermakrFontSize = 6.355;

        // $firstLine = '1450360 ';

        // $secondWatermarktext = $this->microLine($firstLine, $widthText, $watermakrFontSize, $subdomain[0]);
        // $pdfBig->SetFont('arial', '', 6.355);
        // $text = $secondWatermarktext;
        // // $result = $this->waterMarkText($pdfBig,5,168.7,$text);





        // // Third Watermark
        // // $widthText = '138';
        // // $watermakrFontSize = 6.355;

        // // $firstLine = 'DHANA LAXMI';

        // // $secondWatermarktext = $this->microLine($firstLine, $widthText, $watermakrFontSize, $subdomain[0]);
        // // $pdfBig->SetFont('arial', '', 6.355);
        // // // $startColor = [253, 220, 190];   // Light Peach
        // // // $middleColor = [192, 226, 202];  // Light Green
        // // // $endColor = [253, 220, 190];     // Light Peach Again
        // // $text = $secondWatermarktext;

        // // $result = $this->waterMarkText($pdfBig,5,163.5,$text);

        


        // // //  Third Watermakr
        // // Define gradient colors (RGB)
        // $text = "365371585680 ";
        // // //Microline
        // // $str=strtoupper($text);							
        // // // $str = preg_replace('/\s+/', '', $str); 
        // // $textArray = imagettfbbox(6.4, 0, public_path().'/backend/fonts/Arial.ttf', $str);
        // // $strWidth = ($textArray[2] - $textArray[0]);
        // // $strHeight = $textArray[6] - $textArray[1] / 5;
        // // $width=139;
        // // $latestWidth =$width;
        // // // $latestWidth = round($width*4.13);
        // // //Updated by Mandar
        // // $microlinestr=$str;
        // // $microlinestrLength=strlen($microlinestr);
        // // //width per character
        // // $microLinecharacterWd =$strWidth/$microlinestrLength;
        // // //Required no of characters required in string to match width
        // // $microlinestrCharReq=$latestWidth/$microLinecharacterWd;
        // // $microlinestrCharReq=round($microlinestrCharReq);
        // // //No of time string should repeated
        // // $repeateMicrolineStrCount=$latestWidth/$strWidth;
        // // $repeateMicrolineStrCount=round($repeateMicrolineStrCount)+1;
        // // //Repeatation of string 
        // // $microlinestrRep = str_repeat($microlinestr, $repeateMicrolineStrCount);                            
        // // //Cut string in required characters (final string)
        // // $arrayEnrollment1 = substr($microlinestrRep,0,$microlinestrCharReq);	   
        
        // // $text = "GOVERNMENT OF TELANGANA ";
        // // $text = "DHANA LAXMI"; // Your text
        // // $widthMM = 48; // Required width in mm
        // // $widthPt = $widthMM * 2.83465; // Convert mm to pt

        // // // Get string width in TCPDF
        // // $strWidth = $pdfBig->GetStringWidth($text);

        // // // Ensure at least one repetition
        // // $repeatCount = max(1, ceil($widthPt / $strWidth));

        // // // Generate repeated string
        // // $finalText = str_repeat($text, $repeatCount);



        // // $pdfBig->SetFont('arial', '', 6.355);

        // // // $startColor = array(255, 0, 0); // Red
        // // // $middleColor = array(255, 255, 0); // Yellow
        // // // $endColor = array(0, 0, 255); // Blue\

        // // $startColor = [253, 220, 190];   // Light Peach
        // // $middleColor = [192, 226, 202];  // Light Green
        // // $endColor = [253, 220, 190];     // Light Peach Again


        // // $text = $finalText;
        // // $textLength = strlen($text);
        // // $x = 5;
        // // $y = 168;
        // // for ($i = 0; $i < $textLength; $i++) {
        // //     $char = $text[$i];

            
        // //     if ($i < $textLength / 2) { // First half of the text
        // //         $progress = $i / ($textLength / 2 - 1); //progress from 0 to 1
        // //         if($textLength == 1){$progress = 1;} //handle single character string.

        // //         $r = $startColor[0] + ($middleColor[0] - $startColor[0]) * $progress;
        // //         $g = $startColor[1] + ($middleColor[1] - $startColor[1]) * $progress;
        // //         $b = $startColor[2] + ($middleColor[2] - $startColor[2]) * $progress;
        // //     } else { // Second half of the text
        // //         $progress = ($i - $textLength / 2) / ($textLength / 2 - 1);
        // //         if($textLength == 1){$progress = 1;}//handle single character string.
        // //         $r = $middleColor[0] + ($endColor[0] - $middleColor[0]) * $progress;
        // //         $g = $middleColor[1] + ($endColor[1] - $middleColor[1]) * $progress;
        // //         $b = $middleColor[2] + ($endColor[2] - $middleColor[2]) * $progress;
        // //     }
            
        // //     $pdfBig->SetTextColor($r, $g, $b);
        // //     // $pdfBig->setFontSpacing(0.01);
        // //     $pdfBig->Text($x, $y, $char);
        // //     // $pdfBig->SetXY($x, $y);
            
        // //     // $pdfBig->MultiCell(0, 0, $char, 0, "L", 0, 0, '', '', true, 0, true);
        // //     // $pdfBig->Cell(2, 0, $char, 0, false, 'L');	
        // //     $charWidth = $pdfBig->GetStringWidth($char);
        // //     $x += $charWidth;
        // // }



        // // $pdfBig->MultiCell(138, 5, $arrayEnrollment, 0, "L", 0, 0, '', '', true, 0, true);
        

        // // echo $arrayEnrollment;
        // // die();
        
        // // $x = 10; // Start position
        // // $y = 60;
        // // $width = 190; // Total width of text
        // // $textLength = strlen($arrayEnrollment);

        
        
        // // for ($i = 0; $i < $textLength; $i++) {
        // //     // Determine position ratio
        // //     $ratio = $i / ($textLength - 1);

        // //     // Blend colors for the first half
        // //     if ($ratio < 0.5) {
        // //         $blendRatio = $ratio / 0.5;
        // //         $r = $startColor[0] + ($middleColor[0] - $startColor[0]) * $blendRatio;
        // //         $g = $startColor[1] + ($middleColor[1] - $startColor[1]) * $blendRatio;
        // //         $b = $startColor[2] + ($middleColor[2] - $startColor[2]) * $blendRatio;
        // //     } 
        // //     // Blend colors for the second half
        // //     else {
        // //         $blendRatio = ($ratio - 0.5) / 0.5;
        // //         $r = $middleColor[0] + ($endColor[0] - $middleColor[0]) * $blendRatio;
        // //         $g = $middleColor[1] + ($endColor[1] - $middleColor[1]) * $blendRatio;
        // //         $b = $middleColor[2] + ($endColor[2] - $middleColor[2]) * $blendRatio;
        // //     }
        // //     $pdfBig->SetXY($x, $y);
        // //     // Set the text color
        // //     $pdfBig->SetTextColor($r, $g, $b);
        // //     // Print each letter separately
        // //     // $pdfBig->Cell(1.5, 0, $arrayEnrollment[$i], 0, 0, 'L');
        // //     $pdfBig->MultiCell(4, 5, $arrayEnrollment[$i], 1, "L", 0, 0, '', '', true, 0, true);

        // //     $x = $x+1.6;
        // // }

        // $pdfBig->Output('sample.pdf', 'I');   
        // die();


        $inputFileType = 'Xlsx';
        $target_path = public_path().'/'.$subdomain[0].'/backend/sample_excel';
        $fullpath = $target_path.'/mitaoe_sample_excel_update.xlsx';

        
        $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
        /**  Load $inputFileName to a Spreadsheet Object  **/
    
        if(!file_exists($fullpath)) {
            echo "file not found";
            die();  
        }
        $objPHPExcel1 = $objReader->load($fullpath);
        $sheet1 = $objPHPExcel1->getSheet(0);
        $highestColumn1 = $sheet1->getHighestColumn();
        $highestRow1 = $sheet1->getHighestDataRow();
        
        $rowData1 = $sheet1->rangeToArray('A1:' . $highestColumn1 . $highestRow1, NULL, TRUE, FALSE);
        unset($rowData1[0]);

        // $sheet2 = $objPHPExcel1->getSheet(1);
        // $rowData2=$sheet2->toArray();
        // unset($rowData2[0]);
        // $rowData2=array_values($rowData2);
        $studentDataOrg = $rowData1;


        $subjectDataOrg =$rowData2;
        // $batchSize = 7; // Define how many records per batch
        // $chunks = array_chunk($rowData1, $batchSize); // Split the array into chunks of 6
        

        $pdfBig = new TCPDF('P', 'mm', array('210', '297'), true, 'UTF-8', false);
        $pdfBig->SetCreator(PDF_CREATOR);
        $pdfBig->SetAuthor('TCPDF');
        $pdfBig->SetTitle('Certificate');
        $pdfBig->SetSubject('');

        // remove default header/footer
        $pdfBig->setPrintHeader(false);
        $pdfBig->setPrintFooter(false);
        $pdfBig->SetAutoPageBreak(false, 0);
        
        // add spot colors
        $pdfBig->AddSpotColor('Spot Red', 30, 100, 90, 10);        // For Invisible
        $pdfBig->AddSpotColor('Spot Dark Green', 100, 50, 80, 45); // clear text on bottom red and in clear text logo
        $pdfBig->AddSpotColor('Spot Light Yellow', 0, 0, 100, 0);
        //set fonts
        // $arial = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Arial.TTF', 'TrueTypeUnicode', '', 96);
        // $arialb = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Arialb.TTF', 'TrueTypeUnicode', '', 96);
        // $arialNarrow = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\arialn.TTF', 'TrueTypeUnicode', '', 96);
        // $arialNarrowB = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\ARIALNB.TTF', 'TrueTypeUnicode', '', 96);
        // $times = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Times-New-Roman.ttf', 'TrueTypeUnicode', '', 96);
        // $timesb = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Times-New-Roman-Bold.ttf', 'TrueTypeUnicode', '', 96);
        // $oef = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\old-english-five.ttf', 'TrueTypeUnicode', '', 96);

        $arial = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\Arial.ttf', 'TrueTypeUnicode', '', 96);
        $arialb = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\ArialB.ttf', 'TrueTypeUnicode', '', 96);
        $arialNarrow = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\arialn.ttf', 'TrueTypeUnicode', '', 96);
        $arialNarrowB = TCPDF_FONTS::addTTFfont(public_path().'\\'.$subdomain[0].'\backend\canvas\fonts\ARIALNB.TTF', 'TrueTypeUnicode', '', 96);



        $subjectsArr = array();
        if($subjectDataOrg) {
            foreach ($subjectDataOrg as $element) {
                $subjectsArr[$element[0]][] = $element;
            }
        }

        foreach($studentDataOrg as $studentData) {
            $pdfBig->AddPage();
            $high_res_bg="BG_Front.jpg"; // BG_Front.jpg
            $low_res_bg="BG_Front.jpg";
            
            
            //set background image
            $template_img_generate = public_path().'\\'.$subdomain[0].'\backend\canvas\bg_images\\'.$high_res_bg;           
            $pdfBig->Image($template_img_generate, 0, 0, '210', '297', "JPG", '', 'R', true);
            $pdfBig->setPageMark(); 	


                    

            
            // $unique_id=trim($studentData[5]);
            
            // $ORG_NAME = trim($studentData[0]);
            // $ACADEMIC_COURSE_ID = trim($studentData[1]);
            // $COURSE_NAME = trim($studentData[2]);
            // $STREAM = trim($studentData[3]);
            // $SESSION = trim($studentData[4]);
            // $REGN_NO = trim($studentData[5]);
            // $RROLL = trim($studentData[6]);
            // $CNAME = trim($studentData[7]);
            // $GENDER = trim($studentData[8]);
            // $DOB = trim($studentData[9]);
            // $FNAME = trim($studentData[10]);
            // $MNAME = trim($studentData[11]);
            // $PHOTO = trim($studentData[12]);
            // $MRKS_REC_STATUS = trim($studentData[13]);
            // $RESULT = trim($studentData[14]);
            // $YEAR = trim($studentData[15]);
            // $CSV_MONTH = trim($studentData[16]);
            // $MONTH = trim($studentData[17]);
            // $PERCENT = trim($studentData[18]);
            // $DOI = trim($studentData[19]);
            // $CERT_NO = trim($studentData[20]);
            // $SEM = trim($studentData[21]);
            // $TOT = trim($studentData[22]);
            // $TOT_MIN = trim($studentData[23]);
            // $TOT_MRKS = trim($studentData[24]);
            // $TOT_TH_MAX = trim($studentData[25]);
            // $TOT_TH_MIN = trim($studentData[26]);
            // $TOT_TH_MRKS = trim($studentData[27]);
            // $TOT_PR_MAX = trim($studentData[28]);
            // $TOT_PR_MIN = trim($studentData[29]);
            // $TOT_PR_MRKS = trim($studentData[30]);
            // $TOT_CE_MAX = trim($studentData[31]);
            // $TOT_CE_MIN = trim($studentData[32]);
            // $TOT_CE_MRKS = trim($studentData[33]);
            // $TOT_VV_MAX = trim($studentData[34]);
            // $TOT_VV_MIN = trim($studentData[35]);
            // $TOT_VV_MRKS = trim($studentData[36]);
            // $TOT_CREDIT = trim($studentData[37]);
            // $TOT_CREDIT_POINTS = trim($studentData[38]);
            // $TOT_GRADE_POINTS = trim($studentData[39]);
            // $PREV_TOT_MRKS = trim($studentData[40]);
            // $GRAND_TOT_MAX = trim($studentData[41]);
            // $GRAND_TOT_MIN = trim($studentData[42]);
            // $GRAND_TOT_MRKS = trim($studentData[43]);
            // $GRAND_TOT_CREDIT = trim($studentData[44]);
            // $CGPA = trim($studentData[45]);
            // $REMARKS = trim($studentData[46]);
            // $SGPA = trim($studentData[47]);
            // $ABC_ACCOUNT_ID = trim($studentData[48]);
            // $TERM_TYPE = trim($studentData[49]);
            // $TOT_GRADE = trim($studentData[50]);
            
            // $AADHAAR_NAME = trim($studentData[51]);
            // $ADMISSION_YEAR = trim($studentData[52]);

            

            // // echo "<pre>";
            // // echo $REGN_NO;
            // // echo "<br>";
            // // echo "<br>";
            // // print_r($subjectsArr);
            // // die();
            // // $left_pos=10.5;
            // // $left_pos_two=112;
            // $subjectsData=$subjectsArr[$REGN_NO];



            // $date_font_size = '11';
            // $date_nox = 11;
            // $date_noy = 42;
            // $date_nostr = 'DRAFT '.date('d-m-Y H:i:s');
            // $pdfBig->SetFont($arialb, '', $date_font_size, '', false);
            // $pdfBig->SetTextColor(192,192,192);
            // $pdfBig->SetXY($date_nox, $date_noy);
            // $pdfBig->Cell(0, 0, $date_nostr, 0, false, 'L');


            // // rgba(126,57,49,255)
            // $pdfBig->SetTextColor(126,57,49);
            // $pdfBig->SetFont($arialb, '', 15.2, '', false); 
            // $pdfBig->SetXY(10, 39);
            // $pdfBig->MultiCell(190, 10, 'GRADE CARD', 0, "C", 0, 0, '', '', true, 0, true);


            // // Set fill color to light gray
            // // $pdfBig->SetFillColor(230, 230, 230);

            // // Set border color to red
            // $pdfBig->SetDrawColor(126,57,49); // RGB Red

            // // Optional: Set border thickness
            // $pdfBig->SetLineWidth(0.4);

            // // Draw a rounded rectangle with no fill (transparent inside)
            // $x = 16;
            // $y = 46.5;
            // $w = 178;
            // $h = 38;
            // $r = 3  ; // corner radius

            // // 'D' = draw only (no fill)
            // $pdfBig->RoundedRect($x, $y, $w, $h, $r, '1111', 'D');

            // // Profile Picture
            // $folderPath = public_path($subdomain[0] . '/backend/templates/100/');
            
            // $profile_path_org = $folderPath . $PHOTO;            
            // if(\File::exists($profile_path_org)){
            //     //set profile image   
            //     $profilex = 170;
            //     $profiley = 47.5;
            //     $profileWidth = 23;
            //     $profileHeight = 32;
            //     // $pdf->image($profile_path_org,$profilex,$profiley,$profileWidth,$profileHeight,"",'','L',true,3600);
            //     // $pdf->setPageMark();
            //     $pdfBig->image($profile_path_org,$profilex,$profiley,$profileWidth,$profileHeight,"",'','L',true,3600);  
            //     $pdfBig->setPageMark();
            // }

            // //////////////////////
            // $pdfBig->SetTextColor(126,57,49);
            // $pdfBig->SetFont($arialb, '', 9.6, '', false); 
            // $pdfBig->SetXY(21, 49);
            // $pdfBig->MultiCell(55, 0, 'STATEMENT OF GRADES FOR', 0, "L", 0, 0, '', '', true, 0, true);

            // $headingStr = $ADMISSION_YEAR.' '.$ACADEMIC_COURSE_ID.' EXAMINATION '.$CSV_MONTH.' '.$YEAR ;
            // $pdfBig->SetTextColor(0,0,0);
            // $pdfBig->SetXY($pdfBig->getX(), 49);
            // $pdfBig->MultiCell(100, 0, $headingStr, 0, "L", 0, 0, '', '', true, 0, true);

            // //////////////////////
            // $pdfBig->SetTextColor(126,57,49);
            // $pdfBig->SetFont($arialb, '', 9.6, '', false); 
            // $pdfBig->SetXY(21, 55);
            // $pdfBig->MultiCell(37, 0, 'SEAT NO', 0, "L", 0, 0, '', '', true, 0, true);


            // $pdfBig->SetTextColor(0,0,0);
            // $pdfBig->SetXY($pdfBig->getX(), 55);
            // $pdfBig->MultiCell(56, 0, $RROLL, 0, "L", 0, 0, '', '', true, 0, true);

            // $pdfBig->SetTextColor(126,57,49);
            // $pdfBig->SetFont($arialb, '', 9.6, '', false); 
            // $pdfBig->SetXY($pdfBig->getX(), 55);
            // $pdfBig->MultiCell(32, 0, 'PERM REG. NO.', 0, "L", 0, 0, '', '', true, 0, true);

            // $pdfBig->SetTextColor(0,0,0);
            // $pdfBig->SetXY($pdfBig->getX(), 55);
            // $pdfBig->MultiCell(26, 0, $REGN_NO, 0, "L", 0, 0, '', '', true, 0, true);

            // //////////////////////
            // $pdfBig->SetTextColor(126,57,49);
            // $pdfBig->SetFont($arialb, '', 9.6, '', false); 
            // $pdfBig->SetXY(21, 61);
            // $pdfBig->MultiCell(37, 0, 'NAME OF STUDENT', 0, "L", 0, 0, '', '', true, 0, true);

            // $pdfBig->SetTextColor(0,0,0);
            // $pdfBig->SetXY($pdfBig->getX(), 61);
            // $pdfBig->MultiCell(111, 0, $CNAME, 0, "L", 0, 0, '', '', true, 0, true);

            // //////////////////////
            // $pdfBig->SetTextColor(126,57,49);
            // $pdfBig->SetFont($arialb, '', 9.6, '', false); 
            // $pdfBig->SetXY(21, 67);
            // $pdfBig->MultiCell(37, 0, 'MOTHER NAME', 0, "L", 0, 0, '', '', true, 0, true);

            // $pdfBig->SetTextColor(0,0,0);
            // $pdfBig->SetXY($pdfBig->getX(), 67);
            // $pdfBig->MultiCell(111, 0, $MNAME, 0, "L", 0, 0, '', '', true, 0, true);



            // //////////////////////
            // $pdfBig->SetTextColor(126,57,49);
            // $pdfBig->SetFont($arialb, '', 9.6, '', false); 
            // $pdfBig->SetXY(21, 73);
            // $pdfBig->MultiCell(37, 0, 'PROGRAMME', 0, "L", 0, 0, '', '', true, 0, true);


            // $pdfBig->SetTextColor(0,0,0);
            // $pdfBig->SetXY($pdfBig->getX(), 73);
            // $pdfBig->MultiCell(19, 0, $ACADEMIC_COURSE_ID, 0, "L", 0, 0, '', '', true, 0, true);

            // $pdfBig->SetTextColor(126,57,49);
            // $pdfBig->SetFont($arialb, '', 9.6, '', false); 
            // $pdfBig->SetXY($pdfBig->getX(), 73);
            // $pdfBig->MultiCell(16, 0, 'YEAR', 0, "L", 0, 0, '', '', true, 0, true);

            // $pdfBig->SetTextColor(0,0,0);
            // $pdfBig->SetXY($pdfBig->getX(), 73);
            // $pdfBig->MultiCell(25, 0, $ADMISSION_YEAR, 0, "L", 0, 0, '', '', true, 0, true);


            // $pdfBig->SetTextColor(126,57,49);
            // $pdfBig->SetFont($arialb, '', 9.6, '', false); 
            // $pdfBig->SetXY($pdfBig->getX(), 73);
            // $pdfBig->MultiCell(32, 0, 'ACADEMIC YEAR', 0, "L", 0, 0, '', '', true, 0, true);

            // $pdfBig->SetTextColor(0,0,0);
            // $pdfBig->SetXY($pdfBig->getX(), 73);
            // $pdfBig->MultiCell(20, 0, $SESSION, 0, "L", 0, 0, '', '', true, 0, true);


            // //////////////////////
            // $pdfBig->SetTextColor(126,57,49);
            // $pdfBig->SetFont($arialb, '', 9.6, '', false); 
            // $pdfBig->SetXY(21, 79);
            // $pdfBig->MultiCell(37, 0, 'BRANCH', 0, "L", 0, 0, '', '', true, 0, true);

            // $pdfBig->SetTextColor(0,0,0);
            // $pdfBig->SetXY($pdfBig->getX(), 79);
            // $pdfBig->MultiCell(111, 0, $STREAM, 0, "L", 0, 0, '', '', true, 0, true);



            // //Separate semesters 
            // $subjects = array();
            // foreach ($subjectsData as $element) {
            //     $subjects[$element[1]][] = $element;
            // }
            // ksort($subjects);

            // // echo "<pre>";
            // // print_r($subjects);
            // // die();
            // // SUBJECT DATA
            // $pdfBig->SetDrawColor(126,57,49); // RGB Red

            // // Optional: Set border thickness
            // $pdfBig->SetLineWidth(0.4);

            // // Draw a rounded rectangle with no fill (transparent inside)
            // $x = 16;
            // $y = 88;
            // $w = 178;
            // $h = 143;
            // $r = 3  ; // corner radius

            // // 'D' = draw only (no fill)
            // $pdfBig->RoundedRect($x, $y, $w, $h, $r, '1111', 'D');

            // $tableY = 90;
            // $pdfBig->SetTextColor(126,57,49);
            // $pdfBig->SetFont($arialb, '', 9.6, '', false); 
            // $pdfBig->SetXY(18, $tableY);
            // $pdfBig->MultiCell(30, 0, 'CODE', 0, "C", 0, 0, '', '', true, 0, true);

            // // $pdfBig->SetTextColor(0,0,0);
            // $pdfBig->SetXY($pdfBig->getX(), $tableY);
            // $pdfBig->MultiCell(100, 0, 'COURSE NAME', 0, "C", 0, 0, '', '', true, 0, true);


            // $pdfBig->SetXY($pdfBig->getX(), $tableY);
            // $pdfBig->MultiCell(27, 0, 'CREDITS', 0, "C", 0, 0, '', '', true, 0, true);

            // $pdfBig->SetXY($pdfBig->getX(), $tableY);
            // $pdfBig->MultiCell(18, 0, 'GRADE', 0, "C", 0, 0, '', '', true, 0, true);


            // $tableY = $tableY+8;

            // foreach($subjects as $sem => $sem_array) {
            //     $pdfBig->SetTextColor(126,57,49);
            //     $pdfBig->SetFont($arialb, '', 9.6, '', false); 
            //     $pdfBig->SetXY(20, $tableY);
            //     $pdfBig->MultiCell(40, 0, 'SEMESTER:'.trim($sem), 0, "L", 0, 0, '', '', true, 0, true);

                
            //     $tableY = $tableY + 6;
            //     // echo "<pre>";
            //     // print_r($sem_array);
            //     // die();
            //     foreach($sem_array as $key => $val){

            //         $pdfBig->SetTextColor(0,0,0);
            //         $pdfBig->SetFont($arialb, '', 9.6, '', false); 
            //         $pdfBig->SetXY(18, $tableY);
            //         $pdfBig->MultiCell(28, 0, $val[3], 0, "C", 0, 0, '', '', true, 0, true);

            //         // $pdfBig->SetTextColor(0,0,0);
            //         $pdfBig->SetXY($pdfBig->getX(), $tableY);
            //         $pdfBig->MultiCell(110, 0, $val[2], 0, "L", 0, 0, '', '', true, 0, true);


            //         $pdfBig->SetXY($pdfBig->getX(), $tableY);
            //         $pdfBig->MultiCell(22, 0, $val[20], 0, "C", 0, 0, '', '', true, 0, true);

            //         $pdfBig->SetXY($pdfBig->getX(), $tableY);
            //         $pdfBig->MultiCell(12, 0, $val[18], 0, "C", 0, 0, '', '', true, 0, true);

            //         $tableY = $tableY+6;

            //     }
            // }

            // // $i1 = 52;
            // // $i2 = 51;
            // // $i3 = 69;
            // // $i4 = 67;
            // // $i5 = 68;
            // // $sem_grade_point = 0;
            // // for ($i=1; $i < 12; $i++) { 
                

            // //     $courseCode = trim($studentData[$i1]);
            // //     $courseName = trim($studentData[$i2]);
            // //     $credits = trim($studentData[$i3]);
            // //     $grades = trim($studentData[$i4]);
                
            // //     $gradesPoint = trim($studentData[$i5]);

            // //     $sem_grade_point += $gradesPoint;

            // //     $pdfBig->SetTextColor(0,0,0);
            // //     $pdfBig->SetFont($arialb, '', 9.6, '', false); 
            // //     $pdfBig->SetXY(18, $tableY);
            // //     $pdfBig->MultiCell(28, 0, $courseCode, 0, "C", 0, 0, '', '', true, 0, true);

            // //     // $pdfBig->SetTextColor(0,0,0);
            // //     $pdfBig->SetXY($pdfBig->getX(), $tableY);
            // //     $pdfBig->MultiCell(110, 0, $courseName, 0, "L", 0, 0, '', '', true, 0, true);


            // //     $pdfBig->SetXY($pdfBig->getX(), $tableY);
            // //     $pdfBig->MultiCell(22, 0, $credits, 0, "C", 0, 0, '', '', true, 0, true);

            // //     $pdfBig->SetXY($pdfBig->getX(), $tableY);
            // //     $pdfBig->MultiCell(12, 0, $grades, 0, "C", 0, 0, '', '', true, 0, true);

            // //     $i1 = $i1 + $subjectIteration;
            // //     $i2 = $i2 + $subjectIteration;
            // //     $i3 = $i3 + $subjectIteration;
            // //     $i4 = $i4 + $subjectIteration;
            // //     $i5 = $i5 + $subjectIteration;

            // //     $tableY = $tableY+6;

            // // }



            
            // // subject end
            

            // $pdfBig->SetDrawColor(126,57,49); // RGB Red

            // // Optional: Set border thickness
            // $pdfBig->SetLineWidth(0.4);

            // // Draw a rounded rectangle with no fill (transparent inside)
            // $x = 16;
            // $y = 233;
            // $w = 178;
            // $h = 21;
            // $r = 3  ; // corner radius

            // // 'D' = draw only (no fill)
            // $pdfBig->RoundedRect($x, $y, $w, $h, $r, '1111', 'D');

            // $tableY = 233;
            // $pdfBig->setCellPaddings( $left = '', $top = '1.3', $right = '', $bottom = '');

            // $pdfBig->SetTextColor(126,57,49);
            // $pdfBig->SetFont($arialb, '', 9.6, '', false); 
            // $pdfBig->SetXY(18, $tableY);
            // $pdfBig->MultiCell(89, 7, 'CURRENT SEMESTER PERFORMANCE', 'R', "C", 0, 0, '', '', true, 0, true);

            // $pdfBig->SetTextColor(126,57,49);
            // $pdfBig->SetFont($arialb, '', 9.6, '', false); 
            // $pdfBig->SetXY($pdfBig->getX(), $tableY);
            // $pdfBig->MultiCell(89, 7, 'CUMILATIVE PERFORMANCE', 0, "C", 0, 0, '', '', true, 0, true);


            // $pdfBig->ln();

            // $pdfBig->SetTextColor(126,57,49);
            // $pdfBig->SetFont($arialb, '', 9.6, '', false); 
            // $pdfBig->SetXY(16, $pdfBig->getY() );
            // $pdfBig->MultiCell(29, 7, 'CREDITS', 'TR', "C", 0, 0, '', '', true, 0, true);

            // $pdfBig->SetTextColor(126,57,49);
            // $pdfBig->SetFont($arialb, '', 9.6, '', false); 
            // $pdfBig->SetXY($pdfBig->getX(), $pdfBig->getY() );
            // $pdfBig->MultiCell(33, 7, 'GRADE POINTS', 'TR', "C", 0, 0, '', '', true, 0, true);

            // $pdfBig->SetTextColor(126,57,49);
            // $pdfBig->SetFont($arialb, '', 9.6, '', false); 
            // $pdfBig->SetXY($pdfBig->getX(), $pdfBig->getY() );
            // $pdfBig->MultiCell(29, 7, 'SGPA', 'TR', "C", 0, 0, '', '', true, 0, true);



            // $pdfBig->SetTextColor(126,57,49);
            // $pdfBig->SetFont($arialb, '', 9.6, '', false); 
            // $pdfBig->SetXY($pdfBig->getX(), $pdfBig->getY() );
            // $pdfBig->MultiCell(29, 7, 'CREDITS', 'TR', "C", 0, 0, '', '', true, 0, true);

            // $pdfBig->SetTextColor(126,57,49);
            // $pdfBig->SetFont($arialb, '', 9.6, '', false); 
            // $pdfBig->SetXY($pdfBig->getX(), $pdfBig->getY() );
            // $pdfBig->MultiCell(33, 7, 'GRADE POINTS', 'TR', "C", 0, 0, '', '', true, 0, true);

            // $pdfBig->SetTextColor(126,57,49);
            // $pdfBig->SetFont($arialb, '', 9.6, '', false); 
            // $pdfBig->SetXY($pdfBig->getX(), $pdfBig->getY() );
            // $pdfBig->MultiCell(25, 7, 'CGPA', 'T', "C", 0, 0, '', '', true, 0, true);



            // $pdfBig->ln();

            // $pdfBig->SetTextColor(0,0,0);
            // $pdfBig->SetFont($arialb, '', 9.6, '', false); 
            // $pdfBig->SetXY(16, $pdfBig->getY() );
            // $pdfBig->MultiCell(29, 7, $TOT_CREDIT, 'TR', "C", 0, 0, '', '', true, 0, true);

            // $pdfBig->SetTextColor(0,0,0);
            // $pdfBig->SetFont($arialb, '', 9.6, '', false); 
            // $pdfBig->SetXY($pdfBig->getX(), $pdfBig->getY() );
            // $pdfBig->MultiCell(33, 7, $sem_grade_point, 'TR', "C", 0, 0, '', '', true, 0, true);

            // $pdfBig->SetTextColor(0,0,0);
            // $pdfBig->SetFont($arialb, '', 9.6, '', false); 
            // $pdfBig->SetXY($pdfBig->getX(), $pdfBig->getY() );
            // $pdfBig->MultiCell(29, 7, number_format($SGPA,2), 'TR', "C", 0, 0, '', '', true, 0, true);



            // $pdfBig->SetTextColor(0,0,0);
            // $pdfBig->SetFont($arialb, '', 9.6, '', false); 
            // $pdfBig->SetXY($pdfBig->getX(), $pdfBig->getY() );
            // $pdfBig->MultiCell(29, 7, $TOT_CREDIT_POINTS, 'TR', "C", 0, 0, '', '', true, 0, true);

            // $pdfBig->SetTextColor(0,0,0);
            // $pdfBig->SetFont($arialb, '', 9.6, '', false); 
            // $pdfBig->SetXY($pdfBig->getX(), $pdfBig->getY() );
            // $pdfBig->MultiCell(33, 7, $TOT_GRADE_POINTS, 'TR', "C", 0, 0, '', '', true, 0, true);

            // $pdfBig->SetTextColor(0,0,0);
            // $pdfBig->SetFont($arialb, '', 9.6, '', false); 
            // $pdfBig->SetXY($pdfBig->getX(), $pdfBig->getY() );
            // $pdfBig->MultiCell(25, 7, number_format($CGPA,2), 'T', "C", 0, 0, '', '', true, 0, true);

            // $pdfBig->setCellPaddings( $left = '', $top = '0', $right = '', $bottom = '');
            // $pdfBig->ln();
            // $pdfBig->SetTextColor(126,57,49);
            // $pdfBig->SetFont($arialb, '', 9.6, '', false); 
            // $pdfBig->SetXY(15, $pdfBig->getY() +2 );
            // $pdfBig->MultiCell(29, 0, 'PLACE : PUNE', 0, "L", 0, 0, '', '', true, 0, true);

            // $pdfBig->ln();
            // $pdfBig->SetTextColor(126,57,49);
            // $pdfBig->SetFont($arialb, '', 9.6, '', false); 
            // $pdfBig->SetXY(15, $pdfBig->getY());
            // $pdfBig->MultiCell(40, 7, 'DATE : 28 AUG 2024', 0, "L", 0, 0, '', '', true, 0, true);



            // $serial_no=$GUID=$studentData[5];
            // $dt = date("_ymdHis");
            // $str=$GUID.$dt;
            // $encryptedString = strtoupper(md5($str));
            

            // $codeContents = $CNAME.", ".$RROLL;
            
            //     $codeContents .="\n\n";
            //     $codeData =CoreHelper::getBCVerificationUrl(base64_encode(strtoupper(md5($str))));
            //     $codeContents .=$codeData;
            
            // $codeContents .="\n\n".strtoupper(md5($str));

            // $qr_code_path = public_path().'\\'.$subdomain[0].'\backend\canvas\images\qr\/'.$encryptedString.'.png';
            // $qrCodex = 15;
            // $qrCodey = 265;
            // $qrCodeWidth =20;
            // $qrCodeHeight =20;
            // $ecc = 'L';
            // $pixel_Size = 1;
            // $frame_Size = 1;  

            // // \PHPQRCode\QRcode::png($codeContents, $qr_code_path, $ecc, $pixel_Size, $frame_Size);
            // \QrCode::size(75.6)
            //     ->backgroundColor(255, 255, 0)
            //     ->format('png')
            //     ->generate($codeContents, $qr_code_path);

            // $pdfBig->Image($qr_code_path, $qrCodex, $qrCodey, $qrCodeWidth,  $qrCodeHeight, '', '', '', false, 600);
            // $pdfBig->setPageMark();

            // /////////////////////////////////////////////////////////////////////////
            
            // //1D Barcode
            // $style1Da = array(
            //     'position' => '',
            //     'align' => 'C',
            //     'stretch' => true,
            //     'fitwidth' => true,
            //     'cellfitalign' => '',
            //     'border' => false,
            //     'hpadding' => 'auto',
            //     'vpadding' => 'auto',
            //     'fgcolor' => array(0,0,0),
            //     'bgcolor' => false, //array(255,255,255),
            //     'text' => true,
            //     'font' => 'helvetica',
            //     'fontsize' => 9,
            //     'stretchtext' => 7
            // ); 
            
            // $barcodex = 12;
            // $barcodey = 267;
            // $barcodeWidth = 56;
            // $barodeHeight = 13;
            // // $pdf->SetAlpha(1);
            // // $pdf->write1DBarcode(trim($print_serial_no), 'C39', $barcodex, $barcodey, $barcodeWidth, $barodeHeight, 0.4, $style1Da, 'N');
            // $pdfBig->SetAlpha(1);
            // $pdfBig->write1DBarcode(trim('PN/2025/25'), 'C39', $barcodex, $barcodey, $barcodeWidth, $barodeHeight, 0.4, $style1Da, 'N');

            // // Start pdfBig
            // $pdfBig->SetFont($arial, 'B', 10, '', false);
            // $pdfBig->SetXY(15, 54);
            // $pdfBig->Cell(0, 10, 'Reg. No. F-419 (Bom)', 0, false, 'C');


            // $pdfBig->SetFont($OLD_ENGL_mt, '', 30, '', false);
            // $pdfBig->SetXY(15, 70);
            // // $pdfBig->Cell(0, 10, '<span style="text-decoration:underline">Certificate</span>', 0, false, 'C');
            // $pdfBig->MultiCell(180, 20, '<span style="text-decoration:underline">Certificate</span>', 0, "C", 0, 0, '', '', true, 0, true);
            // $pdfBig->ln();

            // $pdfBig->SetTextColor(0,0,0);
            // $pdfBig->SetFont($MTCORSVA, '', 16, '', false);
            // $pdfBig->SetXY(15, $pdfBig->getY());
            // $pdfBig->MultiCell(180, 5, 'Certificate No : '.$certificate_no, 0, "L", 0, 0, '', '', true, 0, true);
            // $pdfBig->ln();

            // $pdfBig->SetFont($MTCORSVA, '', 16, '', false);
            // $pdfBig->SetXY(15, $pdfBig->getY());
            // $pdfBig->MultiCell(180, 15, 'Date of Issue : '.$issue_date, 0, "L", 0, 0, '', '', true, 0, true);
            // $pdfBig->ln();

            // $lineSpacing = 15;
            // // Set dotted border style
            // $style = array(
            //     'width' => 0.5,       // Line width
            //     'dash' => '0,4',      // Small dots (0 width line, 4 space)
            //     'color' => array(0, 0, 0) // Black color
            // );
            // $pdfBig->SetLineStyle($style);

            // $pdfBig->SetFont($MTCORSVA, '', 18, '', false);
            // $pdfBig->SetXY(15, $pdfBig->getY());
            // $pdfBig->MultiCell(55, 7, 'This &nbsp;is &nbsp;to &nbsp;certify &nbsp;that', 0, "J", 0, 0, '', '', true, 0, true);
            
            // $pdfBig->SetTextColor(249,50,60);
            // $pdfBig->SetFont($Times_New_RomanBI, '', 18, '', false);
            // $pdfBig->SetXY($pdfBig->getX(), $pdfBig->getY()-2);
            // $pdfBig->MultiCell(123, 7, $candidate_name, 'B', "C", 0, 0, '', '', true, 0, true);
            // $y = $pdfBig->getY()+$lineSpacing;
            

            // $pdfBig->SetTextColor(0,0,0);
            // $pdfBig->SetFont($MTCORSVA, '', 18, '', false);
            // $pdfBig->SetXY(15, $y);
            // $pdfBig->MultiCell(25, 7, 'D/o &nbsp; Shri', 0, "J", 0, 0, '', '', true, 0, true);

            // $pdfBig->SetTextColor(0,0,0);
            // $pdfBig->SetFont($Times_New_RomanBI, '', 18, '', false);
            // $pdfBig->SetXY($pdfBig->getX()-1,  $y-2);
            // $pdfBig->MultiCell(95, 7, $guardian_name, 'B', "C", 0, 0, '', '', true, 0, true);


            // $pdfBig->SetTextColor(0,0,0);
            // $pdfBig->SetFont($MTCORSVA, '', 18, '', false);
            // $pdfBig->SetXY($pdfBig->getX(),  $y);
            // $pdfBig->MultiCell(74, 7, 'has successfully completed', '', "L", 0, 0, '', '', true, 0, true);

            // $y = $pdfBig->getY()+$lineSpacing;
            // $pdfBig->SetFont($MTCORSVA, '', 18, '', false);
            // $pdfBig->SetXY(15, $y);
            // $pdfBig->MultiCell(38, 7, 'the &nbsp;training &nbsp;in', 0, "J", 0, 0, '', '', true, 0, true);
            
            // $pdfBig->SetTextColor(249,50,60);
            // $pdfBig->SetFont($Times_New_RomanBI, '', 18, '', false);
            // $pdfBig->SetXY($pdfBig->getX()-1, $y-2);
            // $pdfBig->MultiCell(141, 7, $course_name, 'B', "C", 0, 0, '', '', true, 0, true);


            // $y = $pdfBig->getY()+$lineSpacing;
            // $pdfBig->SetTextColor(0,0,0);
            // $pdfBig->SetFont($MTCORSVA, '', 18, '', false);
            // $pdfBig->SetXY(15, $y);
            // $pdfBig->MultiCell(17, 7, 'Sector', 0, "J", 0, 0, '', '', true, 0, true);
            
            // $pdfBig->SetTextColor(0,0,0);
            // $pdfBig->SetFont($Times_New_RomanBI, '', 18, '', false);
            // $pdfBig->SetXY($pdfBig->getX()-1, $y-2);
            // $pdfBig->MultiCell(162, 7, $sector_name, 'B', "C", 0, 0, '', '', true, 0, true);


            // $y = $pdfBig->getY()+$lineSpacing;
            // $pdfBig->SetTextColor(0,0,0);
            // $pdfBig->SetFont($MTCORSVA, '', 18, '', false);
            // $pdfBig->SetXY(15, $y);
            // $pdfBig->MultiCell(13, 7, 'from', 0, "J", 0, 0, '', '', true, 0, true);
            
            // $pdfBig->SetTextColor(0,0,0);
            // $pdfBig->SetFont($Times_New_RomanBI, '', 18, '', false);
            // $pdfBig->SetXY($pdfBig->getX()-1, $y-2);
            // $pdfBig->MultiCell(80.4, 7, $start_date, 'B', "C", 0, 0, '', '', true, 0, true);

            // $pdfBig->SetTextColor(0,0,0);
            // $pdfBig->SetFont($MTCORSVA, '', 18, '', false);
            // $pdfBig->SetXY($pdfBig->getX()-1, $y);
            // $pdfBig->MultiCell(7.1, 7, 'to', 0, "J", 0, 0, '', '', true, 0, true);

            // $pdfBig->SetTextColor(0,0,0);
            // $pdfBig->SetFont($Times_New_RomanBI, '', 18, '', false);
            // $pdfBig->SetXY($pdfBig->getX()-1, $y-2);
            // $pdfBig->MultiCell(80.5, 7, $end_date, 'B', "C", 0, 0, '', '', true, 0, true);
            
            // $y = $pdfBig->getY()+$lineSpacing;
            // $pdfBig->SetTextColor(0,0,0);
            // $pdfBig->SetFont($MTCORSVA, '', 18, '', false);
            // $pdfBig->SetXY(15, $y);
            // $pdfBig->MultiCell(27, 7, 'at location', 0, "J", 0, 0, '', '', true, 0, true);
            
            // $pdfBig->SetTextColor(0,0,0);
            // $pdfBig->SetFont($Times_New_RomanBI, '', 18, '', false);
            // $pdfBig->SetXY($pdfBig->getX()-1, $y-2);
            // $pdfBig->MultiCell(152, 7, $center_location, 'B', "C", 0, 0, '', '', true, 0, true);




            // $pdfBig->SetTextColor(0,0,0);
            // $pdfBig->SetFont($Times_New_Normal, '', 15, '', false);
            // $pdfBig->SetXY(15, 225);
            // $pdfBig->MultiCell(60, 7, 'President/ Trustee/ CEO', 0, "L", 0, 0, '', '', true, 0, true);



            // $pdfBig->SetTextColor(0,0,0);
            // $pdfBig->SetFont($Times_New_Normal, '', 15, '', false);
            // $pdfBig->SetXY(148, 225);
            // $pdfBig->MultiCell(60, 7, 'Manager/Partner', 0, "L", 0, 0, '', '', true, 0, true);

            // $pdfBig->SetTextColor(0,0,0);
            // $pdfBig->SetFont($Times_New_Normal, '', 8, '', false);
            // $pdfBig->SetXY(72, 234);
            // $pdfBig->MultiCell(69, 7, '75 - 100=“A+” grade, 60 - 74=“A” grade,', 0, "C", 0, 0, '', '', true, 0, true);


            // $pdfBig->SetTextColor(0,0,0);
            // $pdfBig->SetFont($Times_New_Normal, '', 8, '', false);
            // $pdfBig->SetXY(72, 238);
            // $pdfBig->MultiCell(69, 7, '50 - 59=“B” grade, 35 - 49=“C” grade', 0, "C", 0, 0, '', '', true, 0, true);




            // End pdfBig
            // $serial_no=$GUID=$unique_id;
            // //qr code    
            // $dt = date("_ymdHis");
            // $str=$GUID.$dt;
            // $codeContents = $QR_Output."\n\n".strtoupper(md5($str));
            // $encryptedString = strtoupper(md5($str));
            // $qr_code_path = public_path().'\\'.$subdomain[0].'\backend\canvas\images\qr\/'.$encryptedString.'.png';
            // $qrCodex = 95; //
            // $qrCodey = 210; //
            // $qrCodeWidth =20;
            // $qrCodeHeight = 20;
            // $ecc = 'L';
            // $pixel_Size = 1;
            // $frame_Size = 1;  
            // // \PHPQRCode\QRcode::png($codeContents, $qr_code_path, $ecc, $pixel_Size, $frame_Size);
            // \QrCode::size(75.6)
            //     ->backgroundColor(255, 255, 0)
            //     ->format('png')
            //     ->generate($codeContents, $qr_code_path);

            // $pdfBig->Image($qr_code_path, $qrCodex, $qrCodey, $qrCodeWidth,  $qrCodeHeight, 'png', '', true, false); 
            // $pdfBig->setPageMark();

            // // Ghost image
			// $nameOrg=$candidate_name;
            // $nameOrg = str_replace('.','',$nameOrg);
            // $ghost_font_size = '12';
            // $ghostImagex = 141;
            // $ghostImagey = 232;
            // $ghostImageWidth = 39.405983333;
            // $ghostImageHeight = 8;
            // $name = substr(str_replace(' ','',strtoupper($nameOrg)), 0, 6);
            // $tmpDir = $this->createTemp(public_path().'\backend\images\ghosttemp\temp');
            // $w = $this->CreateMessage($tmpDir, $name ,$ghost_font_size,'');			
            // $pdfBig->Image("$tmpDir/" . $name."".$ghost_font_size.".png", $ghostImagex, $ghostImagey, $w, $ghostImageHeight, "PNG", '', 'L', true, 3600);
            // $pdfBig->setPageMark();

            // // //Microline
            // $str = $nameOrg;
            // $str = strtoupper(preg_replace('/\s+/', '', $str)); 			
            // $microlinestr=$str;
            // $pdfBig->SetFont($arial, '', 1.3, '', false);
            // $pdfBig->SetTextColor(0, 0, 0);
            // $pdfBig->SetXY(95, 209);        
            // $pdfBig->Cell(20, 0, $microlinestr, 0, false, 'C');
            
            // $x= 30;
            // $y = 16;
            // $font_size=11;

            // $serial_no_print = '00001';
            // //if($previewPdf!=1){
            //     //$str = str_pad($card_serial_no, 6, '0', STR_PAD_LEFT);
            // //}else{
            //     $str = str_pad($serial_no_print, 5, '0', STR_PAD_LEFT);   
            // //}             
            // $strArr = str_split($str);
            // $x= 30;
            // $y = 16;
            // $font_size=11;
            
            // //if($previewPdf!=1){
            //     //$str = str_pad($card_serial_no, 6, '0', STR_PAD_LEFT);
            // //}else{
            //     $str = str_pad($serial_no_print, 5, '0', STR_PAD_LEFT);   
            // //}             
            // $strArr = str_split($str);
            // $x_org=$x;
            // $y_org=$y;
            // $font_size_org=$font_size;
            // $i =0;
            // $j=0;
            // $y=$y+4.5;
            // $z=0;
            // foreach ($strArr as $character) {
            //     // $pdf->SetFont($verdana,0, $font_size, '', false);
            //     // $pdf->SetXY($x, $y);

            //     $pdfBig->SetFont($verdana,0, $font_size, '', false);
            //     $pdfBig->SetXY($x, $y);
                
            //     // $pdf->Cell(0, 0, $character, 0, $ln=0,  'L', 0, '', 0, false, 'B', 'B');
            //     $pdfBig->Cell(0, 0, $character, 0, $ln=0,  'L', 0, '', 0, false, 'B', 'B');
            //     $i++;
            //     $x=$x+2.8; 
            //     // if($i>2){
            //         $font_size=$font_size+1;   
            //     // }
            // } 


            // $pdfBig->SetFont($KrutiDev106, '', 15, '', false); 
            // $pdfBig->SetXY(31,263);   
            // $pdfBig->MultiCell(90, 10, 'vf/k"Bkrk', 0, "L", 0, 0, '', '', true, 0, true);


            // $pdfBig->SetFont($arial, '', 13, '', false); 
            // $pdfBig->SetXY(32,269);    
            // $pdfBig->MultiCell(90, 10, 'Dean', 0, "L", 0, 0, '', '', true, 0, true);
            

            

            // $pdfBig->SetFont($KrutiDev106, '', 15, '', false); 
            // $pdfBig->SetXY(84,263);   
            // $pdfBig->MultiCell(90, 10, 'dk;Zdkjh funs\'kd', 0, "L", 0, 0, '', '', true, 0, true);


            // $pdfBig->SetFont($arial, '', 13, '', false); 
            // $pdfBig->SetXY(80,269);    
            // $pdfBig->MultiCell(90, 10, 'Executive Director', 0, "L", 0, 0, '', '', true, 0, true);



    
            // $pdfBig->SetFont($KrutiDev106, '', 15, '', false); 
            // $pdfBig->SetXY(151,263);   
            // $pdfBig->MultiCell(90, 10, 'v/;{k', 0, "L", 0, 0, '', '', true, 0, true);


            // $pdfBig->SetFont($arial, '', 13, '', false); 
            // $pdfBig->SetXY(146,269);    
            // $pdfBig->MultiCell(90, 10, 'President', 0, "L", 0, 0, '', '', true, 0, true);

            // $serial_no =trim($studentData[0]);
            // //Dean Sign
            
            // $sign1 = public_path().'\\'.$subdomain[0].'\backend\canvas\images\Dean_Signature.png';
            // $sign1_x = 20;
            // $sign1_y = 245;
            // $sign1_Width = 31.75;
            // $sign1_Height = 9.79;

            // $upload_sign1_org = $sign1;
            // $pathInfo = pathinfo($sign1);
            // $sign1 = public_path().'\\'.$subdomain[0].'\backend\\templates\\100\\'.$pathInfo['filename']."_$serial_no.".$pathInfo['extension'];
            // \File::copy($upload_sign1_org,$sign1); 
            // $pdfBig->image($sign1,$sign1_x,$sign1_y,$sign1_Width,$sign1_Height,"",'','L',true,3600);
            // $pdfBig->setPageMark();
            // $pdf->image($sign1,$sign1_x,$sign1_y,$sign1_Width,$sign1_Height,"",'','L',true,3600);
            // $pdf->setPageMark();




            //The Executive Director Sign
            // $sign2 = public_path().'\\'.$subdomain[0].'\backend\canvas\images\The_Executive_Director_Sign.png';
            // $sign2_x = 84;
            // $sign2_y = 245;
            // $sign2_Width = 31.75;
            // $sign2_Height = 9.79;

            // $upload_sign2_org = $sign2;
            // $pathInfo = pathinfo($sign2);
            // $sign2 = public_path().'\\'.$subdomain[0].'\backend\\templates\\100\\'.$pathInfo['filename']."_$serial_no.".$pathInfo['extension'];
            // \File::copy($upload_sign2_org,$sign2); 

            // $pdfBig->image($sign2,$sign2_x,$sign2_y,$sign2_Width,$sign2_Height,"",'','L',true,3600);
            // $pdfBig->setPageMark();
            // // $pdf->image($sign2,$sign2_x,$sign2_y,$sign2_Width,$sign2_Height,"",'','L',true,3600);
            // // $pdf->setPageMark();



            // //President Sign
            // $sign3 = public_path().'\\'.$subdomain[0].'\backend\canvas\images\President_Sign.png';
            // $sign3_x = 151;
            // $sign3_y = 245;
            // $sign3_Width = 31.75;
            // $sign3_Height = 9.79;

            // $upload_sign3_org = $sign3;
            // $pathInfo = pathinfo($sign3);
            // $sign3 = public_path().'\\'.$subdomain[0].'\backend\\templates\\100\\'.$pathInfo['filename']."_$serial_no.".$pathInfo['extension'];
            // \File::copy($upload_sign3_org,$sign3); 

            // $pdfBig->image($sign3,$sign3_x,$sign3_y,$sign3_Width,$sign3_Height,"",'','L',true,3600);
            // $pdfBig->setPageMark();
            // $pdf->image($sign3,$sign3_x,$sign3_y,$sign3_Width,$sign3_Height,"",'','L',true,3600);
            // $pdf->setPageMark();

            // echo "<pre>";
            // print_r($studentData);

            // $folderPath = public_path($subdomain[0] . '/backend/templates/100/');
            // $extensions = ['jpg', 'png', 'jpeg'];
            // $profile_path_org = '';

            // foreach ($extensions as $ext) {
            //     $filePath = $folderPath . trim($studentData[7]) . '.' . $ext;
            //     echo $filePath;
            //     echo "<br>";
            //     if (\File::exists($filePath)) {
            //         $profile_path_org = $filePath;
            //         break; // Stop looping once a file is found
            //     }
            // }

            // echo $profile_path_org;
            

            // if(\File::exists($profile_path_org)){
            //     //set profile image   
            //     $profilex = 171;
            //     $profiley = 72;
            //     $profileWidth = 20;
            //     $profileHeight = 25;
            //     $pdf->image($profile_path_org,$profilex,$profiley,$profileWidth,$profileHeight,"",'','L',true,3600);
            //     $pdfBig->image($profile_path_org,$profilex,$profiley,$profileWidth,$profileHeight,"",'','L',true,3600);  
            //     $pdf->setPageMark();
            //     $pdfBig->setPageMark();
            // }

        }
        // die();
        // @unlink($sign1);
        //         @unlink($sign2);
        //         @unlink($sign3);
        $pdfBig->Output('sample.pdf', 'I');   
        
            
        // $unique_id = trim($studentData[0]);
        // $student_id = trim($studentData[1]);
        // $hall_ticket = trim($studentData[1]);
        // $memo_no = trim($studentData[2]);
        // $appar_id = trim($studentData[3]);
        // $serial_no = trim($studentData[4]);
        // $examination = trim($studentData[5]);
        // $month_year = trim($studentData[6]);
        // $branch = trim($studentData[7]); 
        // $candidate_name = trim($studentData[8]);
        // $father_name =  trim($studentData[9]);
        // $mother_name =  trim($studentData[10]);

        // $total_internal_marks = trim($studentData[107]);
        // $total_external_marks = trim($studentData[108]);
        // $total_marks_100 = trim($studentData[109]);
        // $total_credits = trim($studentData[110]);

        // $total_subject = trim($studentData[111]);
        // $total_appered = trim($studentData[112]);
        // $total_passed = trim($studentData[113]);
        
        // $agreegate_in_word =  trim($studentData[114]);
        // $sgpa =  trim($studentData[115]);
        // $cgpa =  trim($studentData[116]);
        // $date_of_issue =  trim($studentData[117]);

        

        // $studentData[2]='ANKITA PRIYADARSANI MAHANANDA JENA';
        // $studentData[3] = 'PRIYADARSANI MAHANANDA JENA';
        // $studentData[4] = 'Basic Computer Skills';
        // $studentData[5] = 'ITERS';
        // $studentData[6] = 'A';
        // $studentData[7] = 'Bandra LDC';
        // $studentData[8] = '02/10/2024';
        // $studentData[9] = '10/12/2024';
        // $x=18;
        // $y=80;
        // $pdfBig->SetTextColor(0,0,0);
        // $pdfBig->SetFont($Times_New_Roman, 'BI', 23, '', false);
        // $pdfBig->SetXY($x, $y);
        // $pdfBig->Cell(0, 10, 'Awarded to', 0, false, 'C');
        
        // // Measure the text width at the initial font size
        // $font_size1 = 25;
        // $width1 = 180;
        // $pdfBig->SetFont($Times_New_Roman, 'B', 25, '', false);
        // $textWidth1 = $pdfBig->GetStringWidth($studentData[2]);

        // // Automatically adjust font size to fit the cell width
        // if ($textWidth1 > $width1) {
        //     $scalingFactor1 = $width1 / $textWidth1; // Calculate scaling factor
        //     $adjustedFontSize1 = floor($font_size1 * $scalingFactor1); // Scale down font size
        // } else {
        //     $adjustedFontSize1 = $font_size1; // No adjustment needed
        // }


        // $pdfBig->SetTextColor(255, 0, 0);
        // $pdfBig->SetFont($Times_New_Roman, 'B', $adjustedFontSize1, '', false);
        // $pdfBig->SetXY(15, $y+10);
        // $pdfBig->MultiCell(180, 10, $studentData[2], 0, "C", 0, 0, '', '', true, 0, true);
        // // $pdfBig->Cell(0, 10, $studentData[2], 0, false, 'C');   
        // $pdfBig->SetTextColor(0,0,0);
        
        // $pdfBig->SetFont($MTCORSVA, '', 23, '', false);
        // // Measure the text width at the initial font size
        // $font_size = 20;
        // $width = 180;
        // $textWidth = $pdfBig->GetStringWidth('S/o,D/o,W/o, Guardian '.$studentData[3]);

        // // Automatically adjust font size to fit the cell width
        // if ($textWidth > $width) {
        //     $scalingFactor = $width / $textWidth; // Calculate scaling factor
        //     $adjustedFontSize = floor($font_size * $scalingFactor); // Scale down font size
        // } else {
        //     $adjustedFontSize = $font_size; // No adjustment needed
        // }

        // $pdfBig->SetFont($Times_New_Roman, 'BI', $adjustedFontSize, '', false);
        // $pdfBig->SetXY(15,$y + 23);        
        // $pdfBig->MultiCell(180, 11, '<span style="font-family:'.$MTCORSVA.'"> S/o,D/o,W/o, Guardian </span>'.$studentData[3], 0, "C", 0, 0, '', '', true, 0, true);


        // Set the adjusted font size
        //$pdfBig->SetFont($fonts_array[$extra_fields],$bold, $adjustedFontSize);

        
        
        // $text = 'S/o,D/o,W/o, Guardian ' . $studentData[3] ;
        // $totalWidth = $pdfBig->GetStringWidth($text); 
        // $pageWidth = $pdfBig->GetPageWidth(); 
        // $centerX = ($pageWidth - $totalWidth) / 2; 
        // $pdfBig->SetXY(15, $y + 23); 
        // $pdfBig->SetFont($MTCORSVA, '', 23, '', false);
        // $pdfBig->Write(0, 'S/o,D/o,W/o, Guardian '); 
        // $pdfBig->SetFont($Times_New_Roman, 'BI', $adjustedFontSize, '', false);
        // $pdfBig->Write(0, $studentData[3]);
        

        // $totalWidth1 = $pdfBig->GetStringWidth('S/o,D/o,W/o, Guardian'); 
        // $totalWidth2 = $pdfBig->GetStringWidth($studentData[3]); 

        // echo $totalWidth1;
        // echo "<br>";
        // echo $totalWidth2;
        // echo "<br>";
        // echo $totalWidth1+$totalWidth2;

        // if()

        // $pdfBig->SetFont($MTCORSVA, '', 23, '', false);
        // $pdfBig->SetXY(15,$y + 23);        
        // $pdfBig->MultiCell(72, 11, 'S/o,D/o,W/o, Guardian', 1, "L", 0, 0, '', '', true, 0, true);

        // $pdfBig->SetFont($Times_New_Roman, 'BI', 20, '', false);
        // $pdfBig->SetXY($pdfBig->getX(),$y + 23);        
        // $pdfBig->MultiCell(108, 11, $studentData[3], 1, "L", 0, 0, '', '', true, 0, true);



        // $pdfBig->SetXY($x, $y+33);
        // $pdfBig->SetFont($MTCORSVA, '', 23, '', false);
        // $pdfBig->SetXY($x, $y+33);
        // $pdfBig->Cell(0, 10, 'for the course of', 0, false, 'C');
        // $pdfBig->SetFont($Times_New_Roman, 'BI', 20, '', false);
        // $pdfBig->SetXY($x, $y+43);
        // $pdfBig->Cell(0, 10, $studentData[4], 0, false, 'C');
        
        
        // $text = 'in ' . $studentData[5] . ' Secter'; 
        // $totalWidth = $pdfBig->GetStringWidth($text); 
        // $pageWidth = $pdfBig->GetPageWidth(); 
        // $centerX = ($pageWidth - $totalWidth) / 2; 
        // $pdfBig->SetXY($centerX, $y + 53); 
        // $pdfBig->SetFont($MTCORSVA, '', 23, '', false);
        // $pdfBig->Write(0, 'in ');
        // $pdfBig->SetFont($Times_New_Roman, 'BI', 20, '', false);
        // $pdfBig->Write(0, $studentData[5] . ' ');
        // $pdfBig->SetFont($MTCORSVA, '', 23, '', false);
        // $pdfBig->Write(0, 'Secter');


        // $text = 'with Grade ' . $studentData[6] ;
        // $pdfBig->SetFont($Times_New_Roman, 'BI', 20, '', false); 
        // $totalWidth = $pdfBig->GetStringWidth($text); 
        // $pageWidth = $pdfBig->GetPageWidth(); 
        // $centerX = ($pageWidth - $totalWidth) / 2; 
        // $pdfBig->SetXY($centerX, $y + 63);
        // $pdfBig->SetFont($MTCORSVA, '', 23, '', false);
        // $pdfBig->Write(0, 'with Grade  ');
        // $pdfBig->SetFont($Times_New_Roman, 'BI', 20, '', false);
        // $pdfBig->Write(0, $studentData[6] . ' ');

        // $pdfBig->SetXY($x, $y+74);
        // $pdfBig->SetFont($MTCORSVA, '', 23, '', false);
        // $pdfBig->Cell(0, 10, 'Conducted at', 0, false, 'C');

        // $text = 'Center - Location ' . $studentData[7] ;
        // $pdfBig->SetFont($Times_New_Roman, 'BI', 20, '', false); 
        // $totalWidth = $pdfBig->GetStringWidth($text); 
        // $pageWidth = $pdfBig->GetPageWidth(); 
        // $centerX = ($pageWidth - $totalWidth) / 2; 
        // $pdfBig->SetXY($centerX, $y + 84);
        // $pdfBig->SetFont($MTCORSVA, '', 23, '', false);
        // $pdfBig->Write(0, 'Center - Location ');
        // $pdfBig->SetFont($Times_New_Roman, 'BI', 20, '', false);
        // $pdfBig->Write(0, $studentData[7] . ' ');

        // $text = 'from ' . $studentData[8] .' to '.$studentData[9];
        // $pdfBig->SetFont($Times_New_Roman, 'BI', 20, '', false); 
        // $totalWidth = $pdfBig->GetStringWidth($text); 
        // $pageWidth = $pdfBig->GetPageWidth(); 
        // $centerX = ($pageWidth - $totalWidth) / 2; 
        // $pdfBig->SetXY($centerX, $y + 94);
        // $pdfBig->SetFont($MTCORSVA, '', 23, '', false);
        // $pdfBig->Write(0, 'from ');
        // $pdfBig->SetFont($Times_New_Roman, 'BI', 20, '', false);
        // $pdfBig->Write(0, $studentData[8] . ' ');
        // $pdfBig->SetFont($MTCORSVA, '', 23, '', false);
        // $pdfBig->Write(0, ' to ');
        // $pdfBig->SetFont($Times_New_Roman, 'BI', 20, '', false);
        // $pdfBig->Write(0, $studentData[9] . ' ');

        // $pdfBig->SetFont($MTCORSVA, '', 15, '', false);
        // $pdfBig->SetXY(20, $y+165);
        // $pdfBig->Cell(0, 0, 'Cerificate No: '.$studentData[0], 0, false, 'L');
        // $pdfBig->SetXY(140, $y+165);
        // $pdfBig->Cell(0, 0, 'Date of issue: '.$studentData[1], 0, false, 'L');

        // $pdfBig->SetFont($Times_New_Normal, '', 8, '', false);
        // $pdfBig->SetXY(10, $y+164);
        // $pdfBig->Cell(0, 0, '75 - 100 = "A+" grade, 60 - 74 = "A" grade,', 0, false, 'C');
        // $pdfBig->SetXY(10, $y+169);
        // $pdfBig->Cell(0, 0, '50 - 59 = "B" grade, 35 - 49 = "C" Grade,', 0, false, 'C');



        // $pdfBig->SetFont('helvetica', '', 16);
        // $pdfBig->SetXY(50, 50);
        
        // // Start transformation
        // $pdfBig->StartTransform();
        // $pdfBig->ScaleY(150, 50, 50); // 150% vertical scaling at X=50, Y=50
        
        // $pdfBig->Cell(50, 10, "Stretched Text", 0, 1, 'C');
        
        // // End transformation
        // $pdfBig->StopTransform();
        

        
        
        // $str = 'Dummy Text';
        // $angle = 45;
        // $line_gap = 20;

        // $font_color_extra = '000000';
        // $security_line = '';
        // for($d = 0; $d < 15; $d++)
        //     $security_line .= $str . ' ';

        
        
        // $pdfBig->SetOverprint(true, true, 0);

        // $uv_percentage= 15;

        
        // $rgb_opacity=$uv_percentage/100;
        // $pdfBig->SetAlpha($rgb_opacity);
        // $pdfBig->SetTextColor(0, 0, 0);                                        
            
        
        
        // $pdfBig->SetFont($arialn, 'B', 10);

        // if (210 < 297){
        //     $pdfWidth = 210;
        //     $pdfHeight = 297;
        // }else{
        //     $pdfWidth = 210;
        //     $pdfHeight = 297;
        // }
        // for ($i=0; $i < $pdfHeight; $i+=$line_gap) {                                    
        //     $pdfBig->SetXY(0,$i);
        //     $pdfBig->StartTransform();  
        //     $pdfBig->Rotate(45);
        //     $pdfBig->Cell(0, 0, $security_line, 0, false, 'L');
        //     $pdfBig->StopTransform();
        // }
        // for ($j=0; $j < $pdfWidth; $j+=$line_gap) {                                    
        //     $pdfBig->SetXY($j+5,$pdfHeight);
        //     $pdfBig->StartTransform();  
        //     $pdfBig->Rotate(45);
        //     $pdfBig->Cell(0, 0, $security_line, 0, false, 'L');
        //     $pdfBig->StopTransform();
        // }
        
        // $pdfBig->SetOverprint(false, false, 0);
        // $pdfBig->SetAlpha(1);

        // Running Below Done
        // $security_line = '';
        // for($d = 0; $d < 15; $d++)
        //     $security_line .= $str . ' ';

        
        // $pdfWidth = 210;
        // $pdfHeight = 297;
        // $j_increased=5;
        // $line_gap =10;
        // $text_align = 'L';
        
        // $pdfBig->SetOverprint(true, true, 0);
        // $uv_percentage= 15;
        // $pdfBig->SetTextColor(0, 0, 0, $uv_percentage, false, '');

        // $rgb_opacity=$uv_percentage/100;
        // // $pdfBig->SetAlpha($rgb_opacity);
        // // $pdfBig->SetTextColor(0, 0, 0); 
        // $pdfBig->SetFont($times, 'B', 12);

       
        // for ($i=0; $i <= $pdfHeight; $i+=$line_gap) {
        //     $pdfBig->SetXY(0,$i);
        //     $pdfBig->StartTransform();  
        //     $pdfBig->Rotate($angle);
        //     $pdfBig->Cell(0, 0, $security_line, 0, false, $text_align);
        //     $pdfBig->StopTransform();
        // }

        
        // for ($j=0; $j < $pdfWidth; $j+=$line_gap) {
        //     //$pdfBig->SetXY($j+5,$pdfHeight);
        //     $pdfBig->SetXY($j+$j_increased,$pdfHeight);
        //     $pdfBig->StartTransform();  
        //     $pdfBig->Rotate($angle);
        //     $pdfBig->Cell(0, 0, $security_line, 0, false, $text_align);
        //     $pdfBig->StopTransform();
        // }
        // $pdfBig->SetOverprint(false, false, 0);

        // Running Upeprr Done

        // $pdfBig->SetAlpha(1);


        // Set transparency
        // $pdfBig->SetAlpha(0.1);
        // $pdfBig->SetAlpha(1);

        // Define watermark text

        // $watermarkText = 'Raj Kamal Kumawat Raj Kamal Kumawat Raj Kamal Kumawat Raj Kamal Kumawat';
        // $chrPerLine = 100; // Equivalent to your logic
        // $repeat_txt = str_repeat($watermarkText, $chrPerLine);


        // // Get page dimensions
        // $pageWidth = $pdfBig->getPageWidth();
        // $pageHeight = $pdfBig->getPageHeight();


        // // Calculate text width
        // $textWidth = $pdfBig->GetStringWidth($repeat_txt);

        // // Define spacing
        // $xSpacing = $textWidth * 1.5; // Adjust horizontal spacing
        // $ySpacing = 10; // Adjust vertical spacing

        // // Loop to repeat watermark
        // for ($y = 0; $y < $pageHeight; $y += $ySpacing) {
        //     for ($x = -$textWidth; $x < $pageWidth + $textWidth; $x += $xSpacing) {
        //         echo $y;
        //         echo "<bR>";
        //         // echo $x;
        //         // echo "<bR>";
        //         // echo $y;
        //         // echo "<bR>";
        //         // echo "<bR>";

        //         $pdfBig->StartTransform();  
        //         $pdfBig->Rotate(45); // Rotate around center
        //         $pdfBig->SetFont($times, 'B', 12);
        //         $pdfBig->Text($x, $y, $repeat_txt);
        //         $pdfBig->StopTransform();
        //     }
        // }

        // $x = 0;
        // $y = 0;
        // for ($y = 0; $y < 290; $y += 10) {
            
        //     $pdfBig->SetFont($BookmanOldStyle_N, '', 10, '', false); 
        //     $pdfBig->SetXY($x,$y);        
        //     $pdfBig->MultiCell(210, 10, $repeat_txt, 0, "L", 0, 0, '', '', true, 0, true);

        //     // $y = $y+10;
        // }


        // Reset transparency
        // $pdfBig->SetAlpha(1);




        // $pdfBig->Output('sample.pdf', 'I');   
    }

    public function convertExcelDate($dateFromExcel)
    {
        if (is_numeric($dateFromExcel)) {
            // Handle Excel date format
            try {
                $excelDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateFromExcel);
                return Carbon::instance($excelDate)->format('d-m-Y'); // Returns DD-MM-YYYY
            } catch (\Throwable $th) {
                echo "Excel Date Conversion Error: " . $th->getMessage() . " for value: " . $dateFromExcel;
                \Log::error("Excel Date Conversion Error: " . $th->getMessage() . " for value: " . $dateFromExcel);
                return null;
            }
        } else {
            // Handle normal string date format
            try {
                return Carbon::parse($dateFromExcel)->format('d-m-Y'); // Returns DD-MM-YYYY
            } catch (\Throwable $th) {
                echo "String Date Parsing Error: " . $th->getMessage() . " for value: " . $dateFromExcel;
                \Log::error("String Date Parsing Error: " . $th->getMessage() . " for value: " . $dateFromExcel);
                return null;
            }
        }
    }

    public function createTemp($path)
    {
        //create ghost image folder
        $tmp = date("ymdHis");
        
        $tmpname = tempnam($path, $tmp);
        //unlink($tmpname);
        //mkdir($tmpname);
        if (file_exists($tmpname)) {
         unlink($tmpname);
        }
        mkdir($tmpname, 0777);
        return $tmpname;
    }


    public function CreateMessage($tmpDir, $name = "",$font_size,$print_color)
    {
        if($name == "")
            return;
        $name = strtoupper($name);
        // Create character image
        if($font_size == 15 || $font_size == "15"){


            $AlphaPosArray = array(
                "A" => array(0, 825),
                "B" => array(825, 840),
                "C" => array(1665, 824),
                "D" => array(2489, 856),
                "E" => array(3345, 872),
                "F" => array(4217, 760),
                "G" => array(4977, 848),
                "H" => array(5825, 896),
                "I" => array(6721, 728),
                "J" => array(7449, 864),
                "K" => array(8313, 840),
                "L" => array(9153, 817),
                "M" => array(9970, 920),
                "N" => array(10890, 728),
                "O" => array(11618, 944),
                "P" => array(12562, 736),
                "Q" => array(13298, 920),
                "R" => array(14218, 840),
                "S" => array(15058, 824),
                "T" => array(15882, 816),
                "U" => array(16698, 800),
                "V" => array(17498, 841),
                "W" => array(18339, 864),
                "X" => array(19203, 800),
                "Y" => array(20003, 824),
                "Z" => array(20827, 876)
            );

            $filename = public_path()."/backend/canvas/ghost_images/F15_H14_W504.png";

            $charsImage = imagecreatefrompng($filename);
            $size = getimagesize($filename);
            // Create Backgoround image
            $filename   = public_path()."/backend/canvas/ghost_images/alpha_GHOST.png";
            $bgImage = imagecreatefrompng($filename);
            $currentX = 0;
            $len = strlen($name);
            
            for($i = 0; $i < $len; $i++) {
                $value = $name[$i];
                if(!array_key_exists($value, $AlphaPosArray))
                    continue;
                $X = $AlphaPosArray[$value][0];
                $W = $AlphaPosArray[$value][1];
                
                imagecopymerge($bgImage, $charsImage, $currentX, 0, $X, 0, $W, $size[1], 100);
                $currentX += $W;
            }

            $rect = array("x" => 0, "y" => 0, "width" => $currentX, "height" => $size[1]);
            $im = imagecrop($bgImage, $rect);
            
            imagepng($im, "$tmpDir/" . $name."".$font_size.".png");
            imagedestroy($bgImage);
            imagedestroy($charsImage);
            return round((14 * $currentX)/ $size[1]);

        }else if($font_size == 12){

            $AlphaPosArray = array(
                "A" => array(0, 849),
                "B" => array(849, 864),
                "C" => array(1713, 840),
                "D" => array(2553, 792),
                "E" => array(3345, 872),
                "F" => array(4217, 776),
                "G" => array(4993, 832),
                "H" => array(5825, 880),
                "I" => array(6705, 744),
                "J" => array(7449, 804),
                "K" => array(8273, 928),
                "L" => array(9201, 776),
                "M" => array(9977, 920),
                "N" => array(10897, 744),
                "O" => array(11641, 864),
                "P" => array(12505, 808),
                "Q" => array(13313, 804),
                "R" => array(14117, 904),
                "S" => array(15021, 832),
                "T" => array(15853, 816),
                "U" => array(16669, 824),
                "V" => array(17493, 800),
                "W" => array(18293, 909),
                "X" => array(19202, 800),
                "Y" => array(20002, 840),
                "Z" => array(20842, 792)
            
            );
                
                $filename = public_path()."/backend/canvas/ghost_images/F12_H8_W288.png";
            $charsImage = imagecreatefrompng($filename);
            $size = getimagesize($filename);
            // Create Backgoround image
            $filename   = public_path()."/backend/canvas/ghost_images/alpha_GHOST.png";
            $bgImage = imagecreatefrompng($filename);
            $currentX = 0;
            $len = strlen($name);
            
            for($i = 0; $i < $len; $i++) {
                $value = $name[$i];
                if(!array_key_exists($value, $AlphaPosArray))
                    continue;
                $X = $AlphaPosArray[$value][0];
                $W = $AlphaPosArray[$value][1];
                
                imagecopymerge($bgImage, $charsImage, $currentX, 0, $X, 0, $W, $size[1], 100);
                $currentX += $W;
            }

            $rect = array("x" => 0, "y" => 0, "width" => $currentX, "height" => $size[1]);
            $im = imagecrop($bgImage, $rect);
            
            imagepng($im, "$tmpDir/" . $name."".$font_size.".png");
            imagedestroy($bgImage);
            imagedestroy($charsImage);
            return round((8 * $currentX)/ $size[1]);

        }else if($font_size == "10" || $font_size == 10){
            $AlphaPosArray = array(
                "A" => array(0, 700),
                "B" => array(700, 757),
                "C" => array(1457, 704),
                "D" => array(2161, 712),
                "E" => array(2873, 672),
                "F" => array(3545, 664),
                "G" => array(4209, 752),
                "H" => array(4961, 744),
                "I" => array(5705, 616),
                "J" => array(6321, 736),
                "K" => array(7057, 784),
                "L" => array(7841, 673),
                "M" => array(8514, 752),
                "N" => array(9266, 640),
                "O" => array(9906, 760),
                "P" => array(10666, 664),
                "Q" => array(11330, 736),
                "R" => array(12066, 712),
                "S" => array(12778, 664),
                "T" => array(13442, 723),
                "U" => array(14165, 696),
                "V" => array(14861, 696),
                "W" => array(15557, 745),
                "X" => array(16302, 680),
                "Y" => array(16982, 728),
                "Z" => array(17710, 680)
                
            );
            
            $filename = public_path()."/backend/canvas/ghost_images/F10_H5_W180.png";
            $charsImage = imagecreatefrompng($filename);
            $size = getimagesize($filename);
            // Create Backgoround image
            $filename   = public_path()."/backend/canvas/ghost_images/alpha_GHOST.png";
            $bgImage = imagecreatefrompng($filename);
            $currentX = 0;
            $len = strlen($name);
            
            for($i = 0; $i < $len; $i++) {
                $value = $name[$i];
                if(!array_key_exists($value, $AlphaPosArray))
                    continue;
                $X = $AlphaPosArray[$value][0];
                $W = $AlphaPosArray[$value][1];
                imagecopymerge($bgImage, $charsImage, $currentX, 0, $X, 0, $W, $size[1], 100);
                $currentX += $W;
            }
            
            $rect = array("x" => 0, "y" => 0, "width" => $currentX, "height" => $size[1]);
            $im = imagecrop($bgImage, $rect);
           
            imagepng($im, "$tmpDir/" . $name."".$font_size.".png");
            imagedestroy($bgImage);
            imagedestroy($charsImage);
            return round((5 * $currentX)/ $size[1]);

        }else if($font_size == 11){

            $AlphaPosArray = array(
                "A" => array(0, 833),
                "B" => array(833, 872),
                "C" => array(1705, 800),
                "D" => array(2505, 888),
                "E" => array(3393, 856),
                "F" => array(4249, 760),
                "G" => array(5009, 856),
                "H" => array(5865, 896),
                "I" => array(6761, 744),
                "J" => array(7505, 832),
                "K" => array(8337, 887),
                "L" => array(9224, 760),
                "M" => array(9984, 920),
                "N" => array(10904, 789),
                "O" => array(11693, 896),
                "P" => array(12589, 776),
                "Q" => array(13365, 904),
                "R" => array(14269, 784),
                "S" => array(15053, 872),
                "T" => array(15925, 776),
                "U" => array(16701, 832),
                "V" => array(17533, 824),
                "W" => array(18357, 872),
                "X" => array(19229, 806),
                "Y" => array(20035, 832),
                "Z" => array(20867, 848)
            
            );
                
                $filename = public_path()."/backend/canvas/ghost_images/F11_H7_W250.png";
            $charsImage = imagecreatefrompng($filename);
            $size = getimagesize($filename);
            // Create Backgoround image
            $filename   = public_path()."/backend/canvas/ghost_images/alpha_GHOST.png";
            $bgImage = imagecreatefrompng($filename);
            $currentX = 0;
            $len = strlen($name);
            
            for($i = 0; $i < $len; $i++) {
                $value = $name[$i];
                if(!array_key_exists($value, $AlphaPosArray))
                    continue;
                $X = $AlphaPosArray[$value][0];
                $W = $AlphaPosArray[$value][1];
                
                imagecopymerge($bgImage, $charsImage, $currentX, 0, $X, 0, $W, $size[1], 100);
                $currentX += $W;
            }

            $rect = array("x" => 0, "y" => 0, "width" => $currentX, "height" => $size[1]);
            $im = imagecrop($bgImage, $rect);
            

            imagepng($im, "$tmpDir/" . $name."".$font_size.".png");
            imagedestroy($bgImage);
            imagedestroy($charsImage);
            return round((7 * $currentX)/ $size[1]);

        }else if($font_size == "13" || $font_size == 13){

            $AlphaPosArray = array(
                "A" => array(0, 865),
                "B" => array(865, 792),
                "C" => array(1657, 856),
                "D" => array(2513, 888),
                "E" => array(3401, 768),
                "F" => array(4169, 864),
                "G" => array(5033, 824),
                "H" => array(5857, 896),
                "I" => array(6753, 784),
                "J" => array(7537, 808),
                "K" => array(8345, 877),
                "L" => array(9222, 664),
                "M" => array(9886, 976),
                "N" => array(10862, 832),
                "O" => array(11694, 856),
                "P" => array(12550, 776),
                "Q" => array(13326, 896),
                "R" => array(14222, 816),
                "S" => array(15038, 784),
                "T" => array(15822, 816),
                "U" => array(16638, 840),
                "V" => array(17478, 794),
                "W" => array(18272, 920),
                "X" => array(19192, 808),
                "Y" => array(20000, 880),
                "Z" => array(20880, 800)
            
            );

                
            $filename = public_path()."/backend/canvas/ghost_images/F13_H10_W360.png";
            $charsImage = imagecreatefrompng($filename);
            $size = getimagesize($filename);
            // Create Backgoround image
            $filename   = public_path()."/backend/canvas/ghost_images/alpha_GHOST.png";
            $bgImage = imagecreatefrompng($filename);
            $currentX = 0;
            $len = strlen($name);
            
            for($i = 0; $i < $len; $i++) {
                $value = $name[$i];
                if(!array_key_exists($value, $AlphaPosArray))
                    continue;
                $X = $AlphaPosArray[$value][0];
                $W = $AlphaPosArray[$value][1];
                
                imagecopymerge($bgImage, $charsImage, $currentX, 0, $X, 0, $W, $size[1], 100);
                $currentX += $W;
            }

            $rect = array("x" => 0, "y" => 0, "width" => $currentX, "height" => $size[1]);
            // dd($rect);
            $im = imagecrop($bgImage, $rect);
            
            imagepng($im, "$tmpDir/" . $name."".$font_size.".png");
            imagedestroy($bgImage);
            imagedestroy($charsImage);
            return round((10 * $currentX)/ $size[1]);

        }else if($font_size == "14" || $font_size == 14){

            $AlphaPosArray = array(
                "A" => array(0, 833),
                "B" => array(833, 872),
                "C" => array(1705, 856),
                "D" => array(2561, 832),
                "E" => array(3393, 832),
                "F" => array(4225, 736),
                "G" => array(4961, 892),
                "H" => array(5853, 940),
                "I" => array(6793, 736),
                "J" => array(7529, 792),
                "K" => array(8321, 848),
                "L" => array(9169, 746),
                "M" => array(9915, 1024),
                "N" => array(10939, 744),
                "O" => array(11683, 864),
                "P" => array(12547, 792),
                "Q" => array(13339, 848),
                "R" => array(14187, 872),
                "S" => array(15059, 808),
                "T" => array(15867, 824),
                "U" => array(16691, 872),
                "V" => array(17563, 736),
                "W" => array(18299, 897),
                "X" => array(19196, 808),
                "Y" => array(20004, 880),
                "Z" => array(80884, 808)
            
            );
                
                $filename = public_path()."/backend/canvas/ghost_images/F14_H12_W432.png";
            $charsImage = imagecreatefrompng($filename);
            $size = getimagesize($filename);
            // Create Backgoround image
            $filename   = public_path()."/backend/canvas/ghost_images/alpha_GHOST.png";
            $bgImage = imagecreatefrompng($filename);
            $currentX = 0;
            $len = strlen($name);
            
            for($i = 0; $i < $len; $i++) {
                $value = $name[$i];
                if(!array_key_exists($value, $AlphaPosArray))
                    continue;
                $X = $AlphaPosArray[$value][0];
                $W = $AlphaPosArray[$value][1];
                
                imagecopymerge($bgImage, $charsImage, $currentX, 0, $X, 0, $W, $size[1], 100);
                $currentX += $W;
            }

            $rect = array("x" => 0, "y" => 0, "width" => $currentX, "height" => $size[1]);
            $im = imagecrop($bgImage, $rect);
            
            imagepng($im, "$tmpDir/" . $name."".$font_size.".png");
            imagedestroy($bgImage);
            imagedestroy($charsImage);
            return round((12 * $currentX)/ $size[1]);

        }else{
            $AlphaPosArray = array(
                "A" => array(0, 944),
                "B" => array(943, 944),
                "C" => array(1980, 944),
                "D" => array(2923, 944),
                "E" => array(3897, 944),
                "F" => array(4840, 753),
                "G" => array(5657, 943),
                "H" => array(6694, 881),
                "I" => array(7668, 504),
                "J" => array(8265, 692),
                "K" => array(9020, 881),
                "L" => array(9899, 944),
                "M" => array(10842, 944),
                "N" => array(11974, 724),
                "O" => array(12916, 850),
                "P" => array(13859, 850),
                "Q" => array(14802, 880),
                "R" => array(15776, 944),
                "S" => array(16719, 880),
                "T" => array(17599, 880),
                "U" => array(18479, 880),
                "V" => array(19485, 880),
                "W" => array(20396, 1038),
                "X" => array(21465, 944),
                "Y" => array(22407, 880),
                "Z" => array(23287, 880)
            );  

            $filename = public_path()."/backend/canvas/ghost_images/ALPHA_GHOST.png";
            $charsImage = imagecreatefrompng($filename);
            $size = getimagesize($filename);

            // Create Backgoround image
            $filename   = public_path()."/backend/canvas/ghost_images/alpha_GHOST.png";
            $bgImage = imagecreatefrompng($filename);
            $currentX = 0;
            $len = strlen($name);
            
            for($i = 0; $i < $len; $i++) {
                $value = $name[$i];
                if(!array_key_exists($value, $AlphaPosArray))
                    continue;
                $X = $AlphaPosArray[$value][0];
                $W = $AlphaPosArray[$value][1];
                imagecopymerge($bgImage, $charsImage, $currentX, 0, $X, 0, $W, $size[1], 100);
                $currentX += $W;
            }

            $rect = array("x" => 0, "y" => 0, "width" => $currentX, "height" => $size[1]);
            $im = imagecrop($bgImage, $rect);
            
            imagepng($im, "$tmpDir/" . $name."".$font_size.".png");
            imagedestroy($bgImage);
            imagedestroy($charsImage);
            return round((10 * $currentX)/ $size[1]);
        }
    }


    public function testMPDF(){
        $domain = \Request::getHost();
        
        $subdomain = explode('.', $domain);
        $ghostImgArr = array();
        $pdf = new \Mpdf\Mpdf(['orientation' => 'P', 'mode' => 'utf-8', 'format' => ['210', '297']]);
        $pdf->SetCreator('seqr'); //PDF_CREATOR
        $pdf->SetAuthor('MPDF');
        $pdf->SetTitle('Certificate');
        $pdf->SetSubject('');
        // remove default header/footer
        $pdf->setHeader(false);
        $pdf->setFooter(false);
        $pdf->SetAutoPageBreak(false, 0);  
        $pdf->AddPage();
        $template_img_generate = public_path().'\\'.$subdomain[0].'\backend\canvas\bg_images\LNCT_Degree_certificate_BG.jpg';
       
        $pdf->Image($template_img_generate, 0, 0, '210', '297', "JPG", '', 'R', true);
       // $pdf->setPageMark();

        $pdf->SetXY(12,85);        
        $pdf->MultiCell(129, 7, 'आवदेक का पता', 0, 'L');  
        $pdf->Output();

    }

    public function testMDomPDF(){

        $data = [
            'title' => 'Welcome to CodeSolutionStuff.com',
            'date' => date('m/d/Y')
        ];
          
        // $pdf = DPDF::loadHTML('htmlText');
        $pdf = PDF::loadView('pdf.course', $data);
    
        return $pdf->download('codesolutionstuff.pdf');
    }

}
