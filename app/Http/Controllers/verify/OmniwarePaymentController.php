<?php

namespace App\Http\Controllers\verify;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\models\Site;
use App\models\Transactions;
use App\models\SbTransactions;
use App\models\PrintingDetail;
use App\models\SbPrintingDetail;
use Illuminate\Support\Facades\Auth;
use App\Utility\ApiSecurityLayer;
use Illuminate\Support\Facades\DB;
use App\models\ApiTracker;
use App\models\User;
use App\models\raisoni\VerificationRequests;
use App\models\raisoni\VerificationDocuments;
use PaytmWallet;
use App\models\PaymentGateway;
use App\models\StudentTable;
use Config,URL;
use Session;

class OmniwarePaymentController extends Controller
{


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


                    // print_r($site_id);
                    // die();

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

    private function redirectToOmniwarePayment($omniWareData){	
       
		return view('verify.payment.omniware.omniwaremoney',compact('omniWareData'));

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
    	return view('verify.payment.omniware.omniware_success_mobile_qr',compact('inputInfo','session_key','request_number','user_id'));

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
        return view('verify.payment.omniware.omniwareStatus',compact('inputInfo','session_key','user_id'));


    }

    
    

}
