<?php

namespace App\Http\Controllers\verify;

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
use App\models\User;
use PaytmWallet;
use Tzsk\Payu\Facade\Payment;
use Session;
use App\models\SystemConfig;
use App\models\SiteDocuments;
use Illuminate\Support\Facades\DB;
use Helper;
use Illuminate\Support\Facades\Auth;
use Config,URL;

class ManagePaymentController extends Controller
{
    
    //Omniware Payment Gateway
    public function omniwarePayment(Request $request){
    	$domain = \Request::getHost();
	    $subdomain = explode('.', $domain);
    
    	if(isset($request['key_payment'])){

    		if (isset($request['payment_from']) && $request['payment_from'] == "mobile") {

                if (isset($request['UID']) && !empty($request['UID'])) {

                    $user = User::select('fullname','email_id','mobile_no','site_id')->where('id',$request['UID'])->first();

                    
                    if (!$user) {
                        echo '<h2>User Not Found!</h2>';
			            exit;
                    }

                    $paramList['EMAIL'] = $user['email_id'];
                    $paramList['MSISDN'] = $user['mobile_no'];

                    $paramList['ORDER_ID'] = 'SeQR_OW_OCVR_' . $request['UID'] . '_' . strtotime("now");
                    $paramList["CUST_ID"] = $request['UID'];
                    $paramList["INDUSTRY_TYPE_ID"] = 'Retail';
                    $paramList["CHANNEL_ID"] = 'WEB';
                    $paramList['CALLBACK_URL'] = "";

                    $mobile_number = $user['mobile_no'];
                    $user_id = $request['UID'];
                    $email_id = $user['email_id'];
                    $payment_from = 'mobile';
                    $name=$user['fullname'];             
                    $site_id=$user['site_id'];  

                    $studentData = StudentTable::where('key',$request['key_payment'])->first();
                    
                    if (!$studentData) {
                        echo '<h2>Key Not Exist!</h2>';
			            exit;
                    }

                }

    		}else{


    			$mobile_number = Auth::guard('webuser')->user()->mobile_no;
		        $user_id = Auth::guard('webuser')->user()->id;
		        $email_id = Auth::guard('webuser')->user()->email_id;
		        $name = Auth::guard('webuser')->user()->fullname;
    			$session = $request->session()->all();
    			$payment_from = 'web';
    			
				$mobile_number = Auth::guard('webuser')->user()->mobile_no;
        		$user_id = Auth::guard('webuser')->user()->id;
        		$email_id = Auth::guard('webuser')->user()->email_id;
                $site_id= Auth::guard('webuser')->user()->site_id;

        		if($request['key_payment']=="OCV-R-1831"){
        			$user_id =20;
        			$mobile_number="9892630464";
        			$email_id="dev12@scube.net.in";
        			$name="Mandar";
        		}
				
				$paramList['EMAIL'] = $email_id;
				$paramList['MSISDN'] = $mobile_number;
				$paramList["CUST_ID"] = $user_id;
				$paramList["INDUSTRY_TYPE_ID"] = 'Retail';
				$paramList["CHANNEL_ID"] = 'WEB';
				$paramList['ORDER_ID'] = 'SeQR_PU_' . $user_id . '_' . strtotime("now");
    		}
    	}else{
			echo '<h2>Access Forbidden!</h2>';
			exit;
			
    	}
    	
        //if($subdomain[0]=="demo"){
    		$sql = PaymentGateway::select("payment_gateway.merchant_key", "payment_gateway.salt", "payment_gateway.test_merchant_key", "payment_gateway.test_salt","payment_gateway_config.pg_id", "payment_gateway_config.amount", "payment_gateway_config.crendential")
    		->leftjoin('payment_gateway_config','payment_gateway_config.pg_id','payment_gateway.id')
    		->where('payment_gateway.pg_name','Omniware')
    		->where('payment_gateway.status',1)
    		->where('payment_gateway.publish',1)
    		->first();
    		$amount = $sql['amount'];
    	//}
       
    	
    	$ORDER_ID = 'SeQR_OW_' . 1 . '_' . strtotime("now");  
    	
        /**************************************************************/
        if(empty($user_id)){
            //print_r(Auth::guard('webuser')->user());
                $user_id= Auth::guard('webuser')->user()->user_id;
            //exit;
        }
        
        $trans_id_ref = $ORDER_ID;
        $user_id = $user_id;
        $student_key = $request['key_payment'];
        $transaction_date = date('Y-m-d H:i:s');

            

        $arr = explode('_', $trans_id_ref);
        if (isset($arr[1])) {
            if ($arr[1] == 'PT') {
                $pgid = 1;
            }
            if ($arr[1] == 'PU') {
                $pgid = 2;
            }
            if ($arr[1] == 'ST') {
                $pgid = 5;
            }
            if ($arr[1] == 'OW') {
                $pgid = 11;
            }
        }

        $transactions = new Transactions;
        $transactions->pay_gateway_id = $pgid;
        $transactions->trans_id_ref = $trans_id_ref;
        $transactions->amount = $amount;
        $transactions->user_id = $user_id;
        $transactions->student_key = $student_key;
        $transactions->site_id = $site_id;
        $transactions->created_at = $transaction_date;
        $transactions->trans_status = 0;
        $transactions->save();

        $last_id = $transactions->id;
        /*********************************************************/


        if(!empty($last_id)){

            $payment_key = \Session::put('payment_key',$request['key_payment']);
            \Session::put('user_id',$user_id);

            if($subdomain[0]=="demo"){

                $omniWareData=array(
                    'txnid' => $ORDER_ID,
                    'user' => $user_id,
                    'mobile_number' => $mobile_number,
                    'name' => $name,
                    'email' => $email_id,
                    'site_id' => $site_id,
                    'amount' => $amount,
                    'product_info'=>$request['key_payment'],
                    'return_url' =>$this->getOmniwarePaymentSuccessUrl($payment_from,$request['key_payment']),
                    'platform'=>$payment_from

                );
                
                return $this->redirectToOmniwarePayment($omniWareData);

            }
        }else{
            return false;
        }
    


    }

    private function redirectToOmniwarePayment($omniWareData) {
        $site_id = $omniWareData['site_id'];
        $paymentGateway = PaymentGatewayNew::where('pg_name','omniware')->where('site_id',$site_id)->first();
		return view('verify.payment.new_omniware.omniwaremoney',[
            'omniWareData'=> $omniWareData,
            'paymentGateway'=> $paymentGateway
        ]);

	}

    public function getOmniwarePaymentSuccessUrl($deviceType,$key_payment){

    	$isQrCodeverification = 0;
    	$studentData = StudentTable::where('key',$key_payment)->first();
    	
    	if ($studentData) {
			$isQrCodeverification = 1;
		}
		
		if ($isQrCodeverification == 1) {

			if ($deviceType == "web") {
				$url = "/verify/payment/omniware/response-success-qr?key=".$key_payment;
			} else {
				$url = "/verify/payment/omniware/response-success-mobile-qr?key=".$key_payment;
			}
		} else {
			if ($deviceType == "web") {
				$url = "/verify/payment/omniware/response-success?key=".$key_payment;
			} else {
				$url = "/verify/payment/omniware/response-success-mobile?key=".$key_payment;
			}
		}
		
		$url = url($url);
		
		return $url;
    }

    public function omniwareResponseSuccessMobile(Request $request){

    	$transaction = PaytmWallet::with('receive');
    	$session_key = \Session::get('payment_key'); 
        $inputInfo = $transaction->response();
    	$request_number = $request['key'];
    	
    	$verification_requests = VerificationRequests::select('student_name','user_id')->where('request_number',$request_number)->first();
        $student_name = $verification_requests['student_name'];
        $user_id = $verification_requests['user_id'];

    	return view('verify.payment.omniware_success_mobile',compact('inputInfo','session_key','verification_requests','request_number','student_name','user_id'));
    }

    public function omniwareResponseSuccessMobileQR(Request $request){

    	$session_key = \Session::get('payment_key'); 
    	$user_id = \Session::get('user_id'); 
        $inputInfo = $_POST;
    	$request_number = $request['key'];

    	$user_id = $scanning_requests['user_id'];
        // echo "<pre>";
        // print_r($inputInfo);
        // echo "</pre>";
        // die();
    	return view('verify.payment.new_omniware.omniware_success_mobile_qr',compact('inputInfo','session_key','request_number','user_id'));

    }

    public function omniwarePaymentResponse(Request $request){
        if($_POST['response_code'] == 0 || $_POST['response_code'] == '0') {
            $transcation = Transactions::where('trans_id_ref',$_POST['order_id'])->where('trans_status',0)->update([
                'trans_status' => 1
            ]);
        }
        
        $user_id = \Auth::guard('webuser')->user()->id;
        $session_key = Session::get('payment_key');
        // echo $session_key; 
        $inputInfo = '';
        return view('verify.payment.new_omniware.omniwareStatus',compact('inputInfo','session_key','user_id'));
    }

    // PAYTM Payment Gateway
    public function paystackPayment(Request $request){
    	$domain = \Request::getHost();
	    $subdomain = explode('.', $domain);
    
    	if(isset($request['key_payment'])){

    		if (isset($request['payment_from']) && $request['payment_from'] == "mobile") {

                if (isset($request['UID']) && !empty($request['UID'])) {

                    $user = User::select('fullname','email_id','mobile_no','site_id')->where('id',$request['UID'])->first();                    
                    if (!$user) {
                        echo '<h2>User Not Found!</h2>';
			            exit;
                    }
                    $paramList['EMAIL'] = $user['email_id'];
                    $paramList['MSISDN'] = $user['mobile_no'];

                    $paramList['ORDER_ID'] = 'SeQR_PS_OCVR_' . $request['UID'] . '_' . strtotime("now");
                    $paramList["CUST_ID"] = $request['UID'];
                    $paramList["INDUSTRY_TYPE_ID"] = 'Retail';
                    $paramList["CHANNEL_ID"] = 'WEB';
                    $paramList['CALLBACK_URL'] = "";

                    $mobile_number = $user['mobile_no'];
                    $user_id = $request['UID'];
                    $email_id = $user['email_id'];
                    $payment_from = 'mobile';
                    $name=$user['fullname'];             
                    $site_id=$user['site_id'];  
                    $studentData = StudentTable::where('key',$request['key_payment'])->first();
                    if (!$studentData) {
                        echo '<h2>Key Not Exist!</h2>';
			            exit;
                    }
                }

    		}else{

    			$mobile_number = Auth::guard('webuser')->user()->mobile_no;
		        $user_id = Auth::guard('webuser')->user()->id;
		        $email_id = Auth::guard('webuser')->user()->email_id;
		        $name = Auth::guard('webuser')->user()->fullname;
    			$session = $request->session()->all();
    			$payment_from = 'web';
    			
				$mobile_number = Auth::guard('webuser')->user()->mobile_no;
        		$user_id = Auth::guard('webuser')->user()->id;
        		$email_id = Auth::guard('webuser')->user()->email_id;
                $site_id= Auth::guard('webuser')->user()->site_id;

        		if($request['key_payment']=="OCV-R-1831"){
        			$user_id =20;
        			$mobile_number="9892630464";
        			$email_id="dev12@scube.net.in";
        			$name="Mandar";
        		}
				
				$paramList['EMAIL'] = $email_id;
				$paramList['MSISDN'] = $mobile_number;
				$paramList["CUST_ID"] = $user_id;
				$paramList["INDUSTRY_TYPE_ID"] = 'Retail';
				$paramList["CHANNEL_ID"] = 'WEB';
				$paramList['ORDER_ID'] = 'SeQR_PS_' . $user_id . '_' . strtotime("now");
    		}
    	}else{
			echo '<h2>Access Forbidden!</h2>';
			exit;
			
    	}
    	
        //if($subdomain[0]=="demo"){
    		$sql = PaymentGateway::select("payment_gateway.merchant_key", "payment_gateway.salt", "payment_gateway.test_merchant_key", "payment_gateway.test_salt","payment_gateway_config.pg_id", "payment_gateway_config.amount", "payment_gateway_config.crendential")
    		->leftjoin('payment_gateway_config','payment_gateway_config.pg_id','payment_gateway.id')
    		->where('payment_gateway.pg_name','Omniware')
    		->where('payment_gateway.status',1)
    		->where('payment_gateway.publish',1)
    		->first();
    		$amount = $sql['amount'];
    	//}
       
    	
    	$ORDER_ID = 'SeQR_PS_' . 1 . '_' . strtotime("now");  
    	
        /**************************************************************/
        if(empty($user_id)){
            //print_r(Auth::guard('webuser')->user());
                $user_id= Auth::guard('webuser')->user()->user_id;
            //exit;
        }
        
        $trans_id_ref = $ORDER_ID;
        $user_id = $user_id;
        $student_key = $request['key_payment'];
        $transaction_date = date('Y-m-d H:i:s');

            

        $arr = explode('_', $trans_id_ref);
        if (isset($arr[1])) {
            if ($arr[1] == 'PT') {
                $pgid = 1;
            }
            if ($arr[1] == 'PU') {
                $pgid = 2;
            }
            if ($arr[1] == 'ST') {
                $pgid = 5;
            }
            if ($arr[1] == 'OW') {
                $pgid = 11;
            }
             if ($arr[1] == 'PS') {
                $pgid = 12;
            }
        }

        $transactions = new Transactions;
        $transactions->pay_gateway_id = $pgid;
        $transactions->trans_id_ref = $trans_id_ref;
        $transactions->amount = $amount;
        $transactions->user_id = $user_id;
        $transactions->student_key = $student_key;
        $transactions->site_id = $site_id;
        $transactions->created_at = $transaction_date;
        $transactions->trans_status = 0;
        $transactions->save();

        $last_id = $transactions->id;
        /*********************************************************/


        if(!empty($last_id)){

            $payment_key = \Session::put('payment_key',$request['key_payment']);
            \Session::put('user_id',$user_id);

            if($subdomain[0]=="superflux"){

                $paystackData=array(
                    'txnid' => $ORDER_ID,
                    'user' => $user_id,
                    'mobile_number' => $mobile_number,
                    'name' => $name,
                    'email' => $email_id,
                    'site_id' => $site_id,
                    'amount' => $amount,
                    'product_info'=>$request['key_payment'],
                    'return_url' =>$this->getPaystackPaymentSuccessUrl($payment_from,$request['key_payment']),
                    'platform'=>$payment_from

                );
                
                return $this->redirectToPaystackPayment($paystackData);
                
            }
        }else{
            return false;
        }
    


    }

    private function redirectToPaystackPayment($paystackData){
        $site_id = $paystackData['site_id'];
        $paymentGateway = PaymentGatewayNew::where('pg_name','paystack')->where('site_id',$site_id)->first();
		return view('verify.payment.paystack.paystackPost',[
            'paystackData'=> $paystackData,
            'paymentGateway'=> $paymentGateway
        ]);
	}

    public function getPaystackPaymentSuccessUrl($deviceType,$key_payment){

    	$isQrCodeverification = 0;
    	$studentData = StudentTable::where('key',$key_payment)->first();
    	
    	if ($studentData) {
			$isQrCodeverification = 1;
		}
		
		if ($isQrCodeverification == 1) {

			if ($deviceType == "web") {
				$url = "/verify/payment/new-paystack/response-success-qr?key=".$key_payment;
			} else {
				$url = "/verify/payment/new-paystack/response-success-mobile-qr?key=".$key_payment;
			}
		} else {
			if ($deviceType == "web") {
				$url = "/verify/payment/new-paystack/response-success?key=".$key_payment;
			} else {
				$url = "/verify/payment/new-paystack/response-success-mobile?key=".$key_payment;
			}
		}
		
		$url = url($url);
		
		return $url;
    }


    public function paystackResponseSuccessMobile(Request $request){

    	// $transaction = PaytmWallet::with('receive');
    	$session_key = \Session::get('payment_key'); 
        // $inputInfo = $transaction->response();
        $inputInfo = $_POST;
    	$request_number = $request['key'];
    	
    	$verification_requests = VerificationRequests::select('student_name','user_id')->where('request_number',$request_number)->first();
        $student_name = $verification_requests['student_name'];
        $user_id = $verification_requests['user_id'];

    	return view('verify.payment.paystack.paystack_success_mobile',compact('inputInfo','session_key','verification_requests','request_number','student_name','user_id'));
    }

    public function paystackResponseSuccessMobileQR(Request $request){
        
    	$session_key = \Session::get('payment_key'); 
    	$user_id = \Session::get('user_id'); 
        $inputInfo = $_POST;
    	$request_number = $request['key'];
        // echo "<pre>";
        // print_r($inputInfo);
        // echo "</pre>";
    	$user_id = $scanning_requests['user_id'];
        return view('verify.payment.paystack.paystack_success_mobile_qr',compact('inputInfo','session_key','request_number','user_id'));

    }
    



    // add transcation
    public function addTransaction(Request $request){
        $payment_params = $request->all();

        //$payment_params   = $this->payment_params;
        $trans_id_ref     = $this->sanitizeVar($payment_params['trans_id_ref']);
        $trans_id_gateway = $this->sanitizeVar($payment_params['trans_id_gateway']);
        $payment_mode     = $this->sanitizeVar($payment_params['payment_mode']);
        $amount           = $this->sanitizeVar($payment_params['amount']);
        $additional       = $this->sanitizeVar($payment_params['additional']);
        $user_id          = $this->sanitizeVar($payment_params['user_id']);
        $student_key      = $this->sanitizeVar($payment_params['student_key']);
        $trans_status     = $this->sanitizeVar($payment_params['trans_status']);
        $transaction_date = date('Y-m-d H:i:s');

        $exp_transaction_id = explode('_', $trans_id_ref);
        
        if($exp_transaction_id[1] == 'PT')
        {
            $pgid = 1;
        }
        if($exp_transaction_id[1] == 'PU')
        {
            $pgid = 2;
        }
        if($exp_transaction_id[1] == 'IM')
        {
            $pgid = 10;
        }
        if($exp_transaction_id[1] == 'OW')
        {
            $pgid = 2;
        }

        $auth_site_id=\Auth::guard('webuser')->user()->site_id;
        
        $systemConfig = SystemConfig::where('site_id',$auth_site_id)->first();

        try {
            $site_id=null;
            $site_id=Helper::GetSiteId($_SERVER['SERVER_NAME']);
            if(isset($systemConfig['sandboxing']) && $systemConfig['sandboxing'] == 1){
                $transactions = new SbTransactions;
            }
            else{
                $transactions = new Transactions;
            }
            $transactions->pay_gateway_id = $pgid;    
            $transactions->trans_id_ref = $trans_id_ref;    
            $transactions->trans_id_gateway = $trans_id_gateway;    
            $transactions->payment_mode = $payment_mode;    
            $transactions->amount = $amount;    
            $transactions->additional = $additional;    
            $transactions->user_id = $user_id;    
            $transactions->student_key = $student_key;   
            $transactions->trans_status = $trans_status;
            $transactions->site_id = $site_id;    
            $transactions->publish = 1;    
            $transactions->save();

            Session::forget('payment_key');
            $message = array('service' => 'Transaction', 'message' => 'Transaction inserted successfully', 'status' => true, 'trans_status' => $trans_status);

        } catch (Exception $e) {

            $message = array('service' => 'Transaction', 'message' => $e->getMessage(), 'status' => false);
        }
        
        
        return $message;
    }

    public function sanitizeVar($sanitizeVar){

        $sanitizeVar = trim($sanitizeVar);
        $sanitizeVar = stripslashes($sanitizeVar);
        $sanitizeVar = htmlspecialchars($sanitizeVar);

        return $sanitizeVar;
    }



}


