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
use App\models\PaymentGatewayNewConfig;

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
    		// $sql = PaymentGateway::select("payment_gateway.merchant_key", "payment_gateway.salt", "payment_gateway.test_merchant_key", "payment_gateway.test_salt","payment_gateway_config.pg_id", "payment_gateway_config.amount", "payment_gateway_config.crendential")
    		// ->leftjoin('payment_gateway_config','payment_gateway_config.pg_id','payment_gateway.id')
    		// ->where('payment_gateway.pg_name','Omniware')
    		// ->where('payment_gateway.status',1)
    		// ->where('payment_gateway.publish',1)
    		// ->first();
    		// $amount = $sql['amount'];
    	//}

        $sql = PaymentGatewayNew::select("payment_gateway_new.merchant_key", "payment_gateway_new.salt", "payment_gateway_new.test_merchant_key", "payment_gateway_new.test_salt","payment_gateway_new_config.pg_id", "payment_gateway_new_config.amount", "payment_gateway_new_config.crendential")
    		->leftjoin('payment_gateway_new_config','payment_gateway_new_config.pg_id','payment_gateway_new.id')
    		->where('payment_gateway_new.pg_name','paystack')
    		->where('payment_gateway_new.status',1)
    		->where('payment_gateway_new.publish',1)
    		->first();
    		$amount = $sql['amount'];
       
    	
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
             if ($arr[1] == 'PS') {
                $pgid = 1;
            }
        }

        $transactions = new Transactions;
        $transactions->pay_gateway_id = $pgid;
        $transactions->trans_id_ref = $trans_id_ref;
        $transactions->amount = $amount;
        // $transactions->payment_mode = 'paystack';
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

  private function redirectToPaystackPayment($paystackData)
{
    $payload = [
        'email'        => $paystackData['email'],
        'amount'       => $paystackData['amount'] * 100,
        'callback_url' => $paystackData['return_url'],
        'metadata'     => [
            'order_id'   => $paystackData['txnid'],
            'user_id'    => $paystackData['user'],
            'student_key'=> $paystackData['product_info'],
            'site_id'    => $paystackData['site_id']
        ]
    ];

    $ch = curl_init('https://api.paystack.co/transaction/initialize');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . env('PAYSTACK_SECRET_KEY'),
        'Content-Type: application/json'
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);

    if (!$result['status']) {
        echo '<h2>Unable to initialize Paystack</h2>';
        exit;
    }
    return redirect($result['data']['authorization_url']);
    // Pass authorization URL to Blade
    // return view('verify.payment.paystack.paystackPost', [
    //     'authorizationUrl' => $result['data']['authorization_url']
    // ]);
}


    // private function redirectToPaystackPayment($paystackData){
    //     $site_id = $paystackData['site_id'];
    //     $paymentGateway = PaymentGatewayNew::where('pg_name','paystack')->where('site_id',$site_id)->first();
	// 	return view('verify.payment.paystack.paystackPost',[
    //         'paystackData'=> $paystackData,
    //         'paymentGateway'=> $paymentGateway
    //     ]);
	// }

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
        $reference = $request->query('reference');

        if (!$reference) {
            return redirect()->route('webapp.dashboard')
                ->with('error', 'Payment reference missing');
        }

        // Verify transaction with Paystack
        $ch = curl_init('https://api.paystack.co/transaction/verify/' . $reference);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . env('PAYSTACK_SECRET_KEY'),
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);

    // if ($result['data']['status'] === 'success') 
        
    	$session_key = \Session::get('payment_key'); 
        // $inputInfo = $transaction->response();
        $txn = Transactions::where('trans_id_gateway', $result['data']['reference'])->where('student_key', $request_number)->first();
        
        $inputInfo = [
            'STATUS'        => $result['status'] == true ? 'TXN_SUCCESS' : 'TXN_FAILURE',
            'ORDERID'       => $txn->trans_id_ref,
            'TXNID'         => $txn->trans_id_gateway,
            'PAYMENTMODE'   => strtoupper($result['data']['channel']),
            'TXNAMOUNT'     => number_format($txn->amount, 2),
        ];
    	$request_number = $request['key'];
    	
    	$verification_requests = VerificationRequests::select('student_name','user_id')->where('request_number',$request_number)->first();
        $student_name = $verification_requests['student_name'];
        $user_id = $verification_requests['user_id'];

    	return view('verify.payment.paystack.paystack_success_mobile',compact('inputInfo','session_key','verification_requests','request_number','student_name','user_id'));
    }

    public function paystackResponseSuccessMobileQR(Request $request){
    $reference = $request->query('reference');

    if (!$reference) {
        return redirect()->route('webapp.dashboard')
            ->with('error', 'Payment reference missing');
    }

    // Verify transaction with Paystack
    $ch = curl_init('https://api.paystack.co/transaction/verify/' . $reference);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . env('PAYSTACK_SECRET_KEY'),
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);
    // dd($result);
    $session_key = \Session::get('payment_key'); 
    $user_id = \Session::get('user_id'); 
    // $inputInfo = $_POST;
    // echo "<pre>";
    //     print_r($inputInfo);
    //     echo "</pre>";
    //     die();
    $request_number = $request['key'];
    
    $txn = Transactions::where('student_key', $request_number)->where('user_id',$user_id)->where('trans_status',0)->first();//where('trans_id_gateway', $result['data']['reference'])->

    // dd($session_key);
        
        $inputInfo = [
            'STATUS'        => $result['status'] == true ? 'TXN_SUCCESS' : 'TXN_FAILURE',
            'ORDERID'       => $txn->trans_id_ref,
            'TXNID'         => $result['data']['reference'],
            'PAYMENTMODE'   => strtoupper($result['data']['channel']),
            'TXNAMOUNT'     => number_format($txn->amount, 2),
        ];
        // echo "<pre>";
        // print_r($inputInfo);
        // echo "</pre>";die();
    	// $user_id = $scanning_requests['user_id'];
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


