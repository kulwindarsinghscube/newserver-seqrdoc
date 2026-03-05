<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ApiTracker;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Collection;
use App\Exports\ApiTrakerExport;
use Mail;
use Session;
// use Excel;

use App\models\StudentTable;
//use Illuminate\Support\Facades\Mail;
use App\Exports\TemplateMasterExport;
use App\Jobs\SendMailJob;
use DB;

class ExcelExportController extends Controller
{


    public function export() {

        
        $directoryUrlForward="C:/inetpub/vhosts/seqrdoc.com/httpdocs/demo/public/blockchain_script/";
        $directoryUrlBackward="C:\\Inetpub\\vhosts\\seqrdoc.com\\httpdocs\\demo\\public\\blockchain_script\\";

        $txnHash = '';
        $pyscript = $directoryUrlBackward."Python_files\\export_excel_script.py";  
        $cmd =  "$pyscript $txnHash 2>&1";
        exec('C:/Users/Administrator/AppData/Local/Programs/Python/Python38/python.exe '.$cmd, $output, $return);

        print_r($output);
        die();
    }

    
    
    
}
