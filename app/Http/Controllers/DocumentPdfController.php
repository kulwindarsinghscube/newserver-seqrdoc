<?php

namespace App\Http\Controllers;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


use App\models\StudentTable;
use App\models\SbStudentTable;
//use App\models\Site;

use App\Helpers\CoreHelper;
use DB;
use Storage;
use App\Utility\GibberishAES;

class DocumentPdfController extends Controller
{
    public function generate($serial_no){
        
        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);
        
        $serial = base64_encode($serial_no);

        // echo $serial;
        echo "http://".$subdomain[0].".seqrdoclocal.com/document/".$serial;


    
    }


    public function showPdf($serial_no)
    {
        

        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);
        $SrNo = base64_decode($serial_no);

        $filename = $SrNo.'.pdf';
        // $result= StudentTable::where('key',$qrData)->where('status','=', 1)->first();

        $file_path = public_path().'/'.$subdomain[0].'/backend/pdf_file/'.$filename;


        $data = [];
        if(file_exists($file_path)) {
            // $file_path = str_replace('/','\\')

            $file_path = 'https://'.$subdomain[0].'.seqrdoc.com'.'/'.$subdomain[0].'/backend/pdf_file/'.$filename;

            $data['pdfUrl'] = $file_path;
        } else {
            $pdfNotFoundFile = public_path().'/pdf-not-found.pdf';
            $data['pdfUrl'] = $pdfNotFoundFile;
        }

        return view('document.preview_index',compact('data'));


    }


}

