<?php

namespace App\Http\Controllers\webapp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\models\StudentTable;
use App\models\Site;
use App\models\SbStudentTable;
use App\models\StudentHistory;
use App\models\Transactions;
use App\models\SbTransactions;
use App\models\PaymentGateway;
use App\models\PaymentGatewayNew;
use App\models\ScannedHistory;
use PaytmWallet;
use Tzsk\Payu\Facade\Payment;
use Session;
use App\models\pdf2pdf\TemplateMaster;
use App\models\textVerificationData;
use App\models\SystemConfig;
use App\models\SiteDocuments;
use Illuminate\Support\Facades\DB;
use Helper;
class DashboardV1Controller extends Controller
{
    // show scan UI
    public function dashboard(Request $request){
        error_reporting(E_ALL);
        ini_set('display_errors', '1');
        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);
        if($subdomain[0] == 'demo') {
            $user = \Auth::guard('webuser')->user();
            if($user->id == 90 || $user->id == '90') {
                return view('webapp.dashboard.showV1');
            }
        }
    	return view('webapp.dashboard.showV1');

    }

  

    public function storeV1(Request $request)
    {

        $auth_site_id=\Auth::guard('webuser')->user()->site_id;

        $get_file_aws_local_flag = SystemConfig::select('file_aws_local')->where('site_id',$auth_site_id)->first();
        
        $systemConfig = SystemConfig::where('site_id',$auth_site_id)->first()->toArray();
        $domain = \Request::getHost();
        $subdomain = explode('.', $domain);
        $awsS3Instances = \Config::get('constant.awsS3Instances');

        if($get_file_aws_local_flag->file_aws_local == '1'){
            if(isset($systemConfig['sandboxing']) && $systemConfig['sandboxing'] == 1){
                $path = \Config::get('constant.amazone_path').$subdomain[0].'/backend/pdf_file/sandbox';
            }
            else{
                $path = \Config::get('constant.amazone_path').$subdomain[0].'/backend/pdf_file';
            }
        }
        else{
            if(isset($systemConfig['sandboxing']) && $systemConfig['sandboxing'] == 1){
                $path = \Config::get('constant.local_base_path').$subdomain[0].'/backend/pdf_file/sandbox';
            }
            else{
               
                if(in_array($subdomain[0], $awsS3Instances)){ 
                    $path = \Config::get('constant.s3bucket_base_url').$subdomain[0]."/backend/pdf_file";
                    
                }elseif($subdomain[0]=='saiu' || $subdomain[0]=='superflux'){
                    $path = 'https://'.$subdomain[0].'.seqrdoc.com/';
                }else{
                    $path = 'https://'.$subdomain[0].'.seqrdoc.com/api/pdf/replace_serial_no/1/1';
                    
                }
                       
                
                
            }
        }
        
        if(isset($_POST['qrcode'])){
            
            $key = $_POST['qrcode'];
            $user_id = \Auth::guard('webuser')->user()->id;
            $site_id = \Auth::guard('webuser')->user()->site_id;
            $update_scan = $_POST['update_scan'];
            
            if(strstr($key, "\n")){

                $lines = explode("\n", $key);
                for($i=0;$i<sizeof($lines)-2;$i++){

                    $data[] =  $lines[$i];
                }
                $data_info = implode('<br>',$data);
                $key = (end($lines));
                $flag1 = 1;
                  
            }else{

                $key = $_POST['qrcode'];
                $flag1 = 0;
            }
            //echo $key;
            $flag = 1;
            $student_count = StudentTable::where(['key'=>$key])->count();

            /*print_r($key);
            print_r($student_count);*/
            $sb_student_count = SbStudentTable::where(['key'=>$key])->count();
            //Session::put('payment_key',$key);

            
            
            if($student_count > 0){
                

                $student = StudentTable::where(['key'=>$key,'publish'=>1])
                ->orderBy('id', 'DESC')
                ->first();
               
                if($student['status'] == '0'){

                    $flag = 0;
                    $html = '';
                    $html .= '<div class="alert alert-danger">';
                    $html .= '<strong>Error</strong>';
                    $html .= 'You scanned a correct QR code';
                    $html .= '<i class="fa fa-qrcode fa-fw theme"></i>';
                    $html .= 'but the document is not active & valid any longer.';
                    $html .= 'Please <a href="'.route('webapp.dashboard').'">click here</a>';
                    $html .= 'to scan again.';
                    $html .= '</div>';

                    $message = $html;
                }else{


                    $isPdf2Pdf = false;
                    $isVerificationType = false;

                    if($subdomain[0] =='po') {
                        if($student['template_type'] == 1) {
                            $isPdf2Pdf = true;
                        }

                        if($isPdf2Pdf) {
                            $pdf2pdfTemplateData = TemplateMaster::select('template_name','verification_type','active_text_verification')->where(['id'=>$student['template_id']])->first();

                            if($pdf2pdfTemplateData['verification_type'] == 1) {
                                $isVerificationType = true;
                            }
                        }
                    }

                    

                    $transaction = Transactions::where(['student_key'=>$key,
                        'user_id'=>$user_id,
                        'publish'=>1,
                        'trans_status'=>1
                        ])->count();
                    
                    $domain = \Request::getHost();
                    $siteData =Site::where(['site_url'=>$domain])->first();
                    $noPaymentGateway=0;
                    if($domain=="wilson.seqrdoc.com"){
                        
                        if($siteData['start_date']>="2023-07-24"){
                            $noPaymentGateway=1;
                        }
                    }


                    if($transaction > 0  || $subdomain[0]=="test" || $subdomain[0]=="vesasc" || $subdomain[0]=="lnctbhopal"|| $subdomain[0]=="surana"|| $subdomain[0]=="srit"||$subdomain[0]=="mitwpu"|| $subdomain[0]=="sangamuni" || $noPaymentGateway==1){
                    	// if($subdomain[0]=="demo"){
                        

                        if(in_array($subdomain[0], $awsS3Instances)){ 
                            $pathPDF=$path.'/'.$student["certificate_filename"]; 
                        }elseif($subdomain[0]=='saiu'){
                            $merging_type=$student['merging_type']; 
                            if($merging_type=='Pre'){
                                $pathPDF = $path.$subdomain[0]."/backend/pdf_file/".$student['certificate_filename'];
                            }else{
                                $directoryUrlBackward=\Config::get('constant.PDF2PDF_DirUrlBack');
                                $pyscript = $directoryUrlBackward."Python_files\\custom_proverify_bg.py";
                                $cmd = "$pyscript $subdomain[0] $key 2>&1";
                                exec($cmd, $output, $return);
                                $pathPDF = $path.$subdomain[0]."/backend/pdf_file/verification_output/".$student['certificate_filename'];
                            }
                        }elseif($subdomain[0]=='superflux'){
                            $pathPDF = $path.$subdomain[0]."/backend/pdf_file/".$student['certificate_filename'];
                            
                        }else{
                            $pathPDF=str_replace("replace_serial_no",$student['serial_no'],$path);
                        }

                            
                    	// }else{
                    	// 	$pathPDF=$path.'/'.$student["certificate_filename"];
                    	// }	
                        if($subdomain[0] =='po' &&  $isVerificationType) {
                            $active_text_verification = $pdf2pdfTemplateData['active_text_verification'];
                            $encrypt_data = textVerificationData::where('student_id', $student['id'])->first();
 
                            if (!empty($encrypt_data)) {
                                $verificationTextDataArray = $this->verificationTextData($encrypt_data,$active_text_verification);
                            }

                            $html .='<div class="col-lg-offset-4 col-lg-4 col-md-offset-4 col-md-4 col-sm-12">
                            
                                <div class="row" style="margin: auto;border: 2px solid #dbdbdb;">
                                    <div class="col-lg-12 col-md-12 col-sm-12" style="background-color: #cecece;padding: 10px;color: #000;margin-bottom: 10px;font-size: 17px;">
                                        <b>Document ID : '.$student['serial_no'].'</b>
                                    </div>
                                </div>
                                    
                                <div class="row" style="margin: auto;border: 2px solid #dbdbdb;">
                                <div class="col-lg-12 col-md-12 col-sm-12 text-center" style="background-color: orange;padding: 10px;color: #fff;margin-bottom: 10px;font-size: 17px;">
                                    <b>Verified Data</b>
                                </div>';

                                // print_r($verificationTextDataArray);
                            if($verificationTextDataArray){
                                foreach($verificationTextDataArray as $textData){
                                    $html .='    
                                    <div class="col-lg-12 col-md-1 col-sm-12 text-center">
                                        <div class="card" style="margin: auto;" id="name">
                                          <div class="card-header" style="  word-wrap: break-word;background-color: #0052cc;color: #fff;">'.$textData['name'].'</div>
                                          <ul class="list-group list-group-flush">
                                            <li class="list-group-item" style="  word-wrap: break-word;"><b>'.$textData['decrypted_value'].'</b></li>
                                          </ul>
                                        </div>
                                    </div>';
                                }
                            } else {
                                $html .='<h2>No Data Found</h>';
                            }
                            



                            $html .='</div>
                            </div>';

                        } else {

                            if($flag1 == 1){

                                
                                    $data = [
                                        'success' => '200',
                                        'serial_no' => $student['serial_no'],
                                        'pdfUrl' => $pathPDF
                                    ];

                                    // $html= $data;
                                

                            }else{
                                
                                    $data = [
                                        'success' => '200',
                                        'serial_no' => $student['serial_no'],
                                        'pdfUrl' => $pathPDF
                                    ];

                                    // $html= $data;

                                
                            }
                        }           

                    }else{


                        $flag = 1;


                        if($subdomain[0]=="raisoni") {

                            $selectColumns = ['payment_gateway.pg_name','payment_gateway_config.pg_id','payment_gateway_config.pg_status','payment_gateway_config.amount'];    
                            $paymentList = PaymentGateway::select($selectColumns)
                                ->leftjoin('payment_gateway_config','payment_gateway_config.pg_id','payment_gateway.id')
                                ->where(['payment_gateway_config.pg_status'=>1,'payment_gateway.status'=>1,'payment_gateway.publish'=>1,'site_id'=>$site_id])
                                ->get()
                                ->toArray();
                        
                        } else {
                            
                            $selectColumns = ['payment_gateway_new.pg_name','payment_gateway_new.merchant_key','payment_gateway_new.test_merchant_key','payment_gateway_new_config.pg_id','payment_gateway_new_config.pg_status','payment_gateway_new_config.amount'];
                            $paymentList = PaymentGatewayNew::select($selectColumns)
                                ->leftjoin('payment_gateway_new_config','payment_gateway_new_config.pg_id','payment_gateway_new.id')
                                ->where(['payment_gateway_new_config.pg_status'=>1,'payment_gateway_new.status'=>1,'payment_gateway_new.publish'=>1,'site_id'=>$site_id])
                                ->get()
                                ->toArray();

                        }
                        


                        // $selectColumns = ['payment_gateway.pg_name','payment_gateway_config.pg_id','payment_gateway_config.pg_status','payment_gateway_config.amount'];
                        // $paymentList = PaymentGateway::select($selectColumns)
                        //     ->leftjoin('payment_gateway_config','payment_gateway_config.pg_id','payment_gateway.id')
                        //     ->where(['payment_gateway_config.pg_status'=>1,'payment_gateway.status'=>1,'payment_gateway.publish'=>1,'site_id'=>$site_id])
                        //     ->get()
                        //     ->toArray();
                        $student_compact = $student->toArray();
                        $html = '';
                        $html .= '<div class="panel panel-info">';
                        $html .= '<div class="panel-heading"><b>Student Information</b></div>';
                        $html .= '<div class="panel-body">';
                       

                        


                        if($paymentList){
                            foreach ($paymentList as $payment_key => $value) {
                                
                                $pg_id = $value['pg_id'];
                                
                                if($value['pg_status'] == 1){

                                    $transaction_count = Transactions::where(['student_key'=>$key,'user_id'=>$user_id,'publish'=>1,'trans_status'=>1])->count();
                                   
                                    // $transaction_count = Transactions::where(['student_key'=>$key,'publish'=>1,'trans_status'=>1])->count();

                                    $payment_status = 'false';
                                    
                                    if($transaction_count > 0){
                                        $payment_status = 'true';
                                    }
                                }

                                if($payment_status == 'true'){
                                        
                                    	if(in_array($subdomain[0], $awsS3Instances)){ 
                                            $pathPDF=$path.'/'.$student["certificate_filename"]; 
                                        }elseif($subdomain[0]=='saiu'){
                                            $merging_type=$student['merging_type']; 
                                            if($merging_type=='Pre'){
                                                $pathPDF = $path.$subdomain[0]."/backend/pdf_file/".$student['certificate_filename'];
                                            }else{
                                                $directoryUrlBackward=\Config::get('constant.PDF2PDF_DirUrlBack');
                                                $pyscript = $directoryUrlBackward."Python_files\\custom_proverify_bg.py";
                                                $cmd = "$pyscript $subdomain[0] $key 2>&1";
                                                exec($cmd, $output, $return);
                                                $pathPDF = $path.$subdomain[0]."/backend/pdf_file/verification_output/".$student['certificate_filename'];
                                            }
                                        }elseif($subdomain[0]=='superflux'){
                                            $pathPDF = $path.$subdomain[0]."/backend/pdf_file/".$student['certificate_filename'];
                                            
                                        }else{
                                            $pathPDF=str_replace("replace_serial_no",$student['serial_no'],$path);
                                        }

                                        if($subdomain[0] =='po' &&  $isVerificationType) {
                                            $active_text_verification = $pdf2pdfTemplateData['active_text_verification'];
                                            $encrypt_data = textVerificationData::where('student_id', $student['id'])->first();
                 
                                            if (!empty($encrypt_data)) {
                                                $verificationTextDataArray = $this->verificationTextData($encrypt_data,$active_text_verification);
                                            }

                                            $html .='<div class="col-lg-8 col-md-8 col-sm-12">
                                            <div class="row" style="margin: auto;border: 2px solid #dbdbdb;">
                                                <div class="col-lg-12 col-md-12 col-sm-12 text-center" style="background-color: orange;padding: 10px;color: #fff;margin-bottom: 10px;font-size: 17px;">
                                                    <b>DATA</b>
                                                </div>';
                                            if($verificationTextDataArray){
                                                foreach($verificationTextDataArray as $textData){
                                                    $html .='    
                                                    <div class="col-lg-12 col-md-1 col-sm-12 text-center">
                                                        <div class="card" style="margin: auto;" id="name">
                                                          <div class="card-header" style="  word-wrap: break-word;background-color: #0052cc;color: #fff;">'.$textData['name'].'</div>
                                                          <ul class="list-group list-group-flush">
                                                            <li class="list-group-item" style="  word-wrap: break-word;"><b>'.$textData['decrypted_value'].'</b></li>
                                                          </ul>
                                                        </div>
                                                    </div>';
                                                }
                                            } else {
                                                $html .='<h2>No Data Found</h>';
                                            }

                                        } else {

                                            if($flag1 == 1){

                                                $data = [
                                                    'success' => '200',
                                                    'serial_no' => $student['serial_no'],
                                                    'pdfUrl' => $pathPDF
                                                ];

                                                // $html= $data;

                                            }else{

                                                $data = [
                                                    'success' => '200',
                                                    'serial_no' => $student['serial_no'],
                                                    'pdfUrl' => $pathPDF
                                                ];

                                                // $html= $data;
                                            }  
                                        }       
                                            
                                }else{

                                    $amount = $value['amount'];
                                    $pg_name = strtolower($value['pg_name']);
                                    
                                    if(!isset($student['student_name']) || empty($student['student_name']) || $student['student_name'] == null){
                                        $student['student_name'] = 'Test Student';
                                    }


                                    if($subdomain[0]=="raisoni") {
                                        
                                        if($pg_name == "paytm"){
                                            $png = "paytm_btn.png";
                                            $url = url('webapp/paytm/'.$key.'/'.$amount.'/'.$student['student_name']);
                                        }
                                        if($pg_name == "PayuBiz"){
                                            $png = "payu_btn.png";
                                            $url = url('webapp/payuGiz/'.$key.'/'.$amount.'/'.$student['student_name']);
                                        }
                                        if($pg_name == "PayUmoney"){

                                            $png = "PayU_btn.png";
                                            $url = url('webapp/payuGiz/'.$key.'/'.$amount.'/'.$student['student_name']);
                                        }
                                        if($pg_name == "instamojo"){

                                            $png = "instamojologo_btn.png";
                                            $url = url('webapp/instamojo/'.$key.'/'.$amount.'/'.$student['student_name']);
                                        }

                                        if($pg_name == "easypay"){

                                            $png = "easypay_icici_btn.png";
                                            $url = url('webapp/easypay/'.$key.'/'.$amount.'/'.$student['student_name']);
                                        }

                                        if($subdomain[0]=="pjlce"){
                                            if($pg_name == "omniware"){

                                                $png = "omniware_btn.png";
                                                $url = url('webapp/omniware/'.$key.'/'.$amount.'/'.$student['student_name']);
                                            }
                                        }
                                        
                                    }elseif($subdomain[0] == 'superflux') {

                                        if($pg_name == "paystack"){
                                            $png = "paystack_btn.png";
                                            $url = url('webapp/paystack/'.$key.'/'.$amount.'/'.$student['student_name']);
                                        }
                                    } else {

                                        if($pg_name == "paytm"){
                                            $png = "paytm_btn.png";
                                            $url = url('webapp/rohit-paytm/'.$key.'/'.$amount.'/'.$student['student_name'].'/'.$pg_id);
                                        }
                                        if($pg_name == "payubiz"){
                                            $png = "payu_btn.png";
                                            $url = url('webapp/rohit-payubiz/'.$key.'/'.$amount.'/'.$student['student_name'].'/'.$pg_id);
                                        }
                                        if($pg_name == "payumoney"){

                                            $png = "payu_money_btn.jpg";
                                            $url = url('webapp/rohit-payumoney/'.$key.'/'.$amount.'/'.$student['student_name'].'/'.$pg_id);
                                        }

                                        if($pg_name == "instamojo"){

                                            $png = "instamojologo_btn.png";
                                            $url = url('webapp/rohit-instamojo/'.$key.'/'.$amount.'/'.$student['student_name'].'/'.$pg_id);
                                        }

                                        if($pg_name == "eazypay"){

                                            $png = "easypay_icici_btn.png";
                                            $url = url('webapp/rohit-eazypay/'.$key.'/'.$amount.'/'.$student['student_name'].'/'.$pg_id);
                                        }
                                        if($pg_name == "omniware"){

                                            $png = "omniware_btn.png";
                                            $url = url('webapp/rohit-omniware/'.$key.'/'.$amount.'/'.$student['student_name'].'/'.$pg_id);
                                        }
                                        if($pg_name == "mpesa"){

                                            $png = "mpesa.png";
                                            $url = url('webapp/mpesa/'.$key.'/'.$amount.'/'.$student['student_name'].'/'.$pg_id);
                                        }
                                    }
                                    


                                    $html .= '<div class="row"><div class="col-xs-12 text-center"><span class="alert alert-success mobile-res" style="border-left:4px solid;border-right:4px solid;">Make Payment of <b><span>&#8358;'.$amount.'</span></b> to view hidden data.</span><a href="'.$url.'" class="payment-url"><img src="'.\Config::get("constant.payment_image").'/'.$png.'" style="display:inline"></a></div></div><hr>';        
                                }
                            }

                        }else{
                            $certificate_filename=$student['certificate_filename'];
                            //$html .= '<h5>Data not found!</h5>';
                            
                            if(in_array($subdomain[0], $awsS3Instances)){ 
                                $pathPDF=$path.'/'.$student["certificate_filename"]; 
                            }elseif($subdomain[0]=='saiu'){
                                $merging_type=$student['merging_type']; 
                                if($merging_type=='Pre'){
                                    $pathPDF = $path.$subdomain[0]."/backend/pdf_file/".$student['certificate_filename'];
                                }else{
                                    $directoryUrlBackward=\Config::get('constant.PDF2PDF_DirUrlBack');
                                    $pyscript = $directoryUrlBackward."Python_files\\custom_proverify_bg.py";
                                    $cmd = "$pyscript $subdomain[0] $key 2>&1";
                                    exec($cmd, $output, $return);

                                    // print_r($output);

                                    $pathPDF = $path.$subdomain[0]."/backend/pdf_file/verification_output/".$student['certificate_filename'];
                                }
                            }elseif($subdomain[0]=='superflux'){
                                $pathPDF = $path.$subdomain[0]."/backend/pdf_file/".$student['certificate_filename'];
                                
                            }else{
                                $pathPDF=str_replace("replace_serial_no",$student['serial_no'],$path);
                            }	

                           
                            if($subdomain[0] =='po' &&  $isVerificationType) {

                                $active_text_verification = $pdf2pdfTemplateData['active_text_verification'];
                                $encrypt_data = textVerificationData::where('student_id', $student['id'])->first();
     
                                if (!empty($encrypt_data)) {
                                    $verificationTextDataArray = $this->verificationTextData($encrypt_data,$active_text_verification);
                                }

                                $html .='<div class="col-lg-8 col-md-8 col-sm-12">
                                <div class="row" style="margin: auto;border: 2px solid #dbdbdb;">
                                    <div class="col-lg-12 col-md-12 col-sm-12 text-center" style="background-color: orange;padding: 10px;color: #fff;margin-bottom: 10px;font-size: 17px;">
                                        <b>DATA</b>
                                    </div>';
                                if($verificationTextDataArray){
                                    foreach($verificationTextDataArray as $textData){
                                        $html .='    
                                        <div class="col-lg-12 col-md-1 col-sm-12 text-center">
                                            <div class="card" style="margin: auto;" id="name">
                                              <div class="card-header" style="  word-wrap: break-word;background-color: #0052cc;color: #fff;">'.$textData['name'].'</div>
                                              <ul class="list-group list-group-flush">
                                                <li class="list-group-item" style="  word-wrap: break-word;"><b>'.$textData['decrypted_value'].'</b></li>
                                              </ul>
                                            </div>
                                        </div>';
                                    }
                                } else {
                                    $html .='<h2>No Data Found</h>';
                                }
                                
                            } else {
                                if($flag1 == 1){
                                    $data = [
                                        'success' => '200',
                                        'serial_no' => $student['serial_no'],
                                        'pdfUrl' => $pathPDF
                                    ];
                                    // $html= $data;
                                }else{
                                    $data = [
                                        'success' => '200',
                                        'serial_no' => $student['serial_no'],
                                        'pdfUrl' => $pathPDF
                                    ];
                                }
                            }
                        }

                        $html .= '</div></div>';
                               
                    }
                    if($data) {
                        $message = $data;
                    } else {

                        $message = $html;
                    }

                    if($update_scan == 0){
                        StudentTable::where('key', '=', $key)
                                    ->update(array('scan_count' => `scan_count`+1));
                    }
                }

            }else if($sb_student_count > 0){
                
                $student = SbStudentTable::where(['key'=>$key])
                ->orderBy('id', 'DESC')
                ->first();
                
                if($student['status'] == '0'){

                    $flag = 0;
                    $html = '';
                    $html .= '<div class="alert alert-danger">';
                    $html .= '<strong>Error</strong>';
                    $html .= 'You scanned a correct QR code';
                    $html .= '<i class="fa fa-qrcode fa-fw theme"></i>';
                    $html .= 'but the document is not active & valid any longer.';
                    $html .= 'Please <a href="'.route('webapp.dashboard').'">click here</a>';
                    $html .= 'to scan again.';
                    $html .= '</div>';

                    $message = $html;
                }else{


                    $transaction = SbTransactions::where(['student_key'=>$key,
                        'user_id'=>$user_id,
                        'publish'=>1,
                        'trans_status'=>1
                        ])->count();
                    if($transaction > 0){
                        $html = '';
                        $html .= '<div class="panel panel-info">';
                        $html .= '<div class="panel-heading"><b>Student Information</b></div>';
                        $html .= '<div class="panel-body">';
                        $html .= '<div class="col-xs-6">
                                    <div claass="row">
                                        <div class="col-xs-5"><label for="info1">Serial No.</label></div>
                                        <div class="col-xs-1"><label for="info1">:</label></div>
                                        <div class="col-xs-6">'.$student['serial_no'].'</div>
                                    </div>
                                    <div claass="row">
                                        <div class="col-xs-6">
                                            <iframe src="'.$path.'/'.$student["certificate_filename"].'#toolbar=0" class="widthHeightDefault">
                                            </iframe>
                                        </div>
                                    </div>
                                </div>';
                        $html .=  '</div></div>';          
                                    

                    }else{


                        $flag = 1;

                        if($subdomain[0]=="raisoni") {

                            $selectColumns = ['payment_gateway.pg_name','payment_gateway_config.pg_id','payment_gateway_config.pg_status','payment_gateway_config.amount'];    
                            $paymentList = PaymentGateway::select($selectColumns)
                                ->leftjoin('payment_gateway_config','payment_gateway_config.pg_id','payment_gateway.id')
                                ->where(['payment_gateway_config.pg_status'=>1,'payment_gateway.status'=>1,'payment_gateway.publish'=>1,'site_id'=>$site_id])
                                ->get()
                                ->toArray();
                        
                        } else {
                            
                            $selectColumns = ['payment_gateway_new.pg_name','payment_gateway_new.merchant_key','payment_gateway_new.test_merchant_key','payment_gateway_new_config.pg_id','payment_gateway_new_config.pg_status','payment_gateway_new_config.amount'];
                            $paymentList = PaymentGatewayNew::select($selectColumns)
                                ->leftjoin('payment_gateway_new_config','payment_gateway_new_config.pg_id','payment_gateway_new.id')
                                ->where(['payment_gateway_new_config.pg_status'=>1,'payment_gateway_new.status'=>1,'payment_gateway_new.publish'=>1,'site_id'=>$site_id])
                                ->get()
                                ->toArray();

                        }

                        // $selectColumns = ['payment_gateway.pg_name','payment_gateway_config.pg_id','payment_gateway_config.pg_status','payment_gateway_config.amount'];
                        // $paymentList = PaymentGateway::select($selectColumns)
                        //     ->leftjoin('payment_gateway_config','payment_gateway_config.pg_id','payment_gateway.id')
                        //     ->where(['payment_gateway_config.pg_status'=>1,'payment_gateway.status'=>1,'payment_gateway.publish'=>1,'site_id'=>$site_id])
                        //     ->get()
                        //     ->toArray();
                        $student_compact = $student->toArray();
                        $html = '';
                        $html .= '<div class="panel panel-info">';
                        $html .= '<div class="panel-heading"><b>Student Information</b></div>';
                        $html .= '<div class="panel-body">';

                        foreach ($paymentList as $payment_key => $value) {
                            
                            $pg_id = $value['pg_id'];
                            
                            if($value['pg_status'] == 1){

                                $transaction_count = SbTransactions::where(['student_key'=>$key,'user_id'=>$user_id,'publish'=>1,'trans_status'=>1])->count();
                               
                                $payment_status = 'false';
                                
                                if($transaction_count > 0){
                                    $payment_status = 'true';
                                }
                            }

                            if($payment_status == 'true'){

                                $html .= '<div class="col-xs-6">
                                            <div class="row">
                                                <div class="col-xs-5"><label for="info1">Serial No.</label></div>
                                                <div class="col-xs-1"><label for="info1">:</label></div>
                                                <div class="col-xs-6">'.$student['serial_no'].'</div>
                                            </div>
                                            <div class="row">
                                                <div class="col-xs-5"><label for="info2">Student Name</label></div>
                                                <div class="col-xs-1"><label for="info2">:</label></div>
                                                <div class="col-xs-6">'.$student['student_name'].'</div>
                                            </div><hr>
                                            <div class="row">
                                                <div class="col-xs-12">
                                                    <iframe src="'.$path.'/'.$student['certificate_filaname'].'#toolbar=0" class="widthHeightDefault">
                                                    </iframe>
                                                </div>    
                                            </div>
                                        </div>';         
                                        
                            }else{

                                $amount = $value['amount'];
                                $pg_name = strtolower($value['pg_name']);
                                if($student['student_name'] == null || $student['student_name'] == 'null'){
                                    $student['student_name'] = 'Test Student';
                                }


                                

                                if($subdomain[0]=="raisoni") {

                                    if($pg_name == "paytm"){
                                        $png = "paytm_btn.png";
                                        $url = url('webapp/paytm/'.$key.'/'.$amount.'/'.$student['student_name']);
                                    }
                                    if($pg_name == "PayuBiz"){
                                        $png = "payu_btn.png";
                                        $url = url('webapp/payuGiz/'.$key.'/'.$amount.'/'.$student['student_name']);
                                    }
                                    if($pg_name == "PayUmoney"){

                                        $png = "PayU_btn.png";
                                        $url = url('webapp/payuGiz/'.$key.'/'.$amount.'/'.$student['student_name']);
                                    }
                                    if($pg_name == "instamojo"){

                                        $png = "instamojologo_btn.png";
                                        $url = url('webapp/instamojo/'.$key.'/'.$amount.'/'.$student['student_name']);
                                    }

                                    if($pg_name == "easypay"){

                                        $png = "easypay_icici_btn.png";
                                        $url = url('webapp/easypay/'.$key.'/'.$amount.'/'.$student['student_name']);
                                    }

                                    if($subdomain[0]=="pjlce"){
                                        if($pg_name == "omniware"){

                                            $png = "omniware_btn.png";
                                            $url = url('webapp/omniware/'.$key.'/'.$amount.'/'.$student['student_name']);
                                        }
                                    }
                                    // if($pg_name == "mpesa"){

                                    //     $png = "mpesa.png";
                                    //     $url = url('webapp/mpesa/'.$key.'/'.$amount.'/'.$student['student_name'].'/'.$pg_id);
                                    // }
                                }elseif($subdomain[0] == 'superflux') {

                                    if($pg_name == "paystack"){
                                        $png = "paystack_btn.png";
                                        $url = url('webapp/paystack/'.$key.'/'.$amount.'/'.$student['student_name']);
                                    }
                                }else {

                                    if($pg_name == "paytm"){
                                        $png = "paytm_btn.png";
                                        $url = url('webapp/rohit-paytm/'.$key.'/'.$amount.'/'.$student['student_name'].'/'.$pg_id);
                                    }
                                    if($pg_name == "payubiz"){
                                        $png = "payu_btn.png";
                                        $url = url('webapp/rohit-payubiz/'.$key.'/'.$amount.'/'.$student['student_name'].'/'.$pg_id);
                                    }
                                    if($pg_name == "payumoney"){
    
                                        $png = "payu_money_btn.jpg";
                                        $url = url('webapp/rohit-payumoney/'.$key.'/'.$amount.'/'.$student['student_name'].'/'.$pg_id);
                                    }
    
                                    if($pg_name == "instamojo"){
    
                                        $png = "instamojologo_btn.png";
                                        $url = url('webapp/rohit-instamojo/'.$key.'/'.$amount.'/'.$student['student_name'].'/'.$pg_id);
                                    }
    
                                    if($pg_name == "eazypay"){
    
                                        $png = "easypay_icici_btn.png";
                                        $url = url('webapp/rohit-eazypay/'.$key.'/'.$amount.'/'.$student['student_name'].'/'.$pg_id);
                                    }
                                    if($pg_name == "omniware"){
    
                                        $png = "omniware_btn.png";
                                        $url = url('webapp/rohit-omniware/'.$key.'/'.$amount.'/'.$student['student_name'].'/'.$pg_id);
                                    }
                                    if($pg_name == "mpesa"){

                                        $png = "mpesa.png";
                                        $url = url('webapp/mpesa/'.$key.'/'.$amount.'/'.$student['student_name'].'/'.$pg_id);
                                    }
                                    
                                }

                                $html .= '<div class="row"><div class="col-xs-12 text-center"><span class="alert alert-success" style="border-left:4px solid;border-right:4px solid;">Make Payment of <b>'.$amount.'<span>&#8358;</span></b> to view hidden data.</span><a href="'.$url.'" class="payment-url"><img src="'.\Config::get("constant.payment_image").'/'.$png.'" style="display:inline"></a></div></div><hr>';        
                            }
                        }

                        $html .= '</div></div>';
                             
                    }
                    $message = $html;
                    if($update_scan == 0){
                        SbStudentTable::where('key', '=', $key)
                                    ->update(array('scan_count' => `scan_count`+1));
                    }
                }
            }else{

                $flag = 2;
                $html = '';
                $html .= '<div class="panel panel-info"><div class="panel-heading"><b>Invalid QR</b></div><div class="panel-body">';
                $html .= '<div class="alert alert-danger">The QR code you scanned is not a Secured QR generated by this system. Kindly scan one of our Secured QR only.<a href="/webapp/dashboard"> Click here Scan Again</a></div></div></div>';
                $message = $html;
            }
            $datetime = date("Y-m-d H:i:s");
            $device_type = 'WebApp';
            $scanned_by = $user_id;
            $scan_result = $flag;

            if($update_scan == 0){

                $student_data = StudentTable::where(['key'=>$key])->first();
                
                $document_id = $student_data['serial_no'];
                $document_status = $student_data['status'];

                $scan_history = new ScannedHistory();
                $scan_history->date_time = $datetime;
                $scan_history->device_type = $device_type;
                $scan_history->scanned_data = $key;
                $scan_history->scan_by = $scanned_by;
                $scan_history->scan_result = $scan_result;
                $scan_history->document_id = $document_id;
                $scan_history->document_status = $document_status;
                $scan_history->site_id = $site_id;
                $scan_history->save();

                $dbName = 'seqr_demo';
        
                \DB::disconnect('mysql'); 
                
                \Config::set("database.connections.mysql", [
                    'driver'   => 'mysql',
                    'host'     => \Config::get('constant.SDB_HOST'),
                    'port' => \Config::get('constant.SDB_PORT'),
                    'database' => \Config::get('constant.SDB_NAME'),
                    'username' => \Config::get('constant.SDB_UN'),
                    'password' => \Config::get('constant.SDB_PW'),
                    "unix_socket" => "",
                    "charset" => "utf8mb4",
                    "collation" => "utf8mb4_unicode_ci",
                    "prefix" => "",
                    "prefix_indexes" => true,
                    "strict" => true,
                    "engine" => null,
                    "options" => []
                ]);
                \DB::reconnect();


                $scan_count = ScannedHistory::select('id')->where('site_id',$site_id)->get()->count();
                SiteDocuments::where('site_id',$site_id)->update(['total_scanned'=>$scan_count]);

                if($subdomain[0] == 'demo')
                {
                    $dbName = 'seqr_'.$subdomain[0];
                }else{

                    $dbName = 'seqr_d_'.$subdomain[0];
                }

                \DB::disconnect('mysql');     
                \Config::set("database.connections.mysql", [
                    'driver'   => 'mysql',
                    'host'     => \Config::get('constant.DB_HOST'),
                    "port" => \Config::get('constant.DB_PORT'),
                    'database' => $dbName,
                    'username' => \Config::get('constant.DB_UN'),
                    'password' => \Config::get('constant.DB_PW'),
                    "unix_socket" => "",
                    "charset" => "utf8mb4",
                    "collation" => "utf8mb4_unicode_ci",
                    "prefix" => "",
                    "prefix_indexes" => true,
                    "strict" => true,
                    "engine" => null,
                    "options" => []
                ]);
                \DB::reconnect();
                
                
            }

        }else{
            $message = 'Data params missing';
        }
        
        return $message;
    }

   


    public function sanitizeVar($sanitizeVar){

        $sanitizeVar = trim($sanitizeVar);
        $sanitizeVar = stripslashes($sanitizeVar);
        $sanitizeVar = htmlspecialchars($sanitizeVar);

        return $sanitizeVar;
    }



    public function verificationTextData($encrypt_data,$active_text_verification) {
        $decoded = base64_decode($encrypt_data['text']);
        $decryptedContent = json_decode($decoded, true);

        $result  = [];
        if (is_array($decryptedContent) && !empty($decryptedContent)) {
            $activeTextVerifications = json_decode($active_text_verification, true);

            $activeKey =  [];
            foreach($activeTextVerifications as $activeText ) {
                if($activeText['is_status'] == 1) {
                    $activeKey[] = $activeText['name'];
                }
            }

            if ($activeKey) {
                
                foreach ($decryptedContent as $decryptedItem) { 
                    $decryptedItem = json_decode($decryptedItem, true); 
                    
                    if (isset($decryptedItem['label']) && in_array($decryptedItem['label'], $activeKey)) {

                        $result[] = [
                            'id' => $decryptedItem['id'],
                            'name' => $decryptedItem['label'],
                            'is_status' => $decryptedItem['is_status'],
                            'decrypted_value' => $decryptedItem['value'] ?? null, // Add decrypted value if exists
                        ];
                    }
                }
                    
                // Return the final result array
                return $result;
            }
        }



        // Return an empty array if no data is found
        return [];

    }



}


