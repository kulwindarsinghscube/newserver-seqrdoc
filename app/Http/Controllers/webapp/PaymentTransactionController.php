<?php

namespace App\Http\Controllers\webapp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
// use App\Events\TransactionsEvent;
// use Event;

use App\models\raisoni\DocumentRateMaster;
use App\models\raisoni\VerificationRequests;
use App\models\raisoni\VerificationDocuments;
use App\models\raisoni\ScanningRequests;
use App\models\PaymentGateway;
use App\models\PaymentGatewayConfig;
use App\models\User;
use App\models\Transactions;
use App\Jobs\SendMailJob;
use PaytmWallet;
use Auth;
use DB,Config,URL;
use App\Helpers\CoreHelper;
use Illuminate\Support\Facades\Log;
use App\models\raisoni\DegreeMaster;

class PaymentTransactionController extends Controller
{
    // public function addTransaction(Request $request){
    // 	$payment_params = $request->all();

         
    // 	if(isset($payment_params['trans_id_ref']) && isset($payment_params['trans_id_gateway']) && isset($payment_params['payment_mode']) && isset($payment_params['amount']) && isset($payment_params['additional']) && isset($payment_params['user_id']) && isset($payment_params['student_key'])){

            
    // 		$event = Event::dispatch(new TransactionsEvent($payment_params));
    // 		 dd($event);
    //    		return response()->json(['data'=>$event]);	
    	
    // 	}else{
    // 		$message = array('service' => 'Transaction', 'message' => 'Data params missing', 'status' => false);
    // 	}
    // 	return $message;
	// }

	 public function sanitizeVar($data) {
		$data = trim($data);
		$data = stripslashes($data);
		$data = htmlspecialchars($data);
		return $data;
	}

	public function addTransaction(Request $request){

		$domain = \Request::getHost();
	    $subdomain = explode('.', $domain);
		$action = $request['action'];

		// dd($action);
		switch ($request['action']) {
			case 'create':
				
				if (isset($request['trans_id_ref']) && isset($request['trans_id_gateway']) && isset($request['payment_mode']) && isset($request['amount']) && isset($request['additional']) && isset($request['user_id']) && isset($request['student_key'])) {

					$trans_id_ref = $this->sanitizeVar($_POST['trans_id_ref']);
					$trans_id_gateway = $this->sanitizeVar($_POST['trans_id_gateway']);
					$payment_mode = $this->sanitizeVar($_POST['payment_mode']);
					$amount = $this->sanitizeVar($_POST['amount']);
					$additional = $this->sanitizeVar($_POST['additional']);
					$user_id = $this->sanitizeVar($_POST['user_id']);
					$student_key = $this->sanitizeVar($_POST['student_key']);
					$trans_status = $this->sanitizeVar($_POST['trans_status']);
					$transaction_date = date('Y-m-d H:i:s');

					if(empty($user_id)){
						//print_r(Auth::guard('webuser')->user());
						 $user_id= Auth::guard('webuser')->user()->user_id;


						//exit;
					}

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
						if ($arr[1] == 'PS') {
							$pgid = 1;
						}
					}

					if($subdomain[0]=="xyz"){

						$transactions = new Transactions;
						$transactions->pay_gateway_id = $pgid;
						$transactions->trans_id_ref = $trans_id_ref;
						$transactions->trans_id_gateway = $trans_id_gateway;
						$transactions->payment_mode = $payment_mode;
						$transactions->amount = $amount;
						$transactions->additional = $additional;
						$transactions->user_id = $user_id;
						$transactions->student_key = $student_key;
						$transactions->created_at = $transaction_date;
						$transactions->trans_status = $trans_status;
						$transactions->save();
					}else{
						$transactionExist = Transactions::where('trans_id_ref',$trans_id_ref)->where('user_id',$user_id)->where('trans_status',0)->first();
					
						// dd($transactionExist,$trans_id_ref);
						// if (!empty($transactionExist)) {

							Transactions::where('trans_id_ref',$trans_id_ref)->where('user_id',$user_id)->where('trans_status',0)->update(['trans_id_gateway'=>$trans_id_gateway,'payment_mode'=>$payment_mode,'amount'=>$amount,'additional'=>$additional,'trans_status'=>$trans_status,'updated_at'=>$transaction_date]);
						

						// }else{
							
							// $transactions = new Transactions;
							// $transactions->pay_gateway_id = $pgid;
							// $transactions->trans_id_ref = $trans_id_ref;
							// $transactions->trans_id_gateway = $trans_id_gateway;
							// $transactions->payment_mode = $payment_mode;
							// $transactions->amount = $amount;
							// $transactions->additional = $additional;
							// $transactions->user_id = $user_id;
							// $transactions->student_key = $student_key;
							// $transactions->created_at = $transaction_date;
							// $transactions->trans_status = $trans_status;
							// $transactions->save();	
						// }

					}
					$message = array('service' => 'Transaction', 'message' => 'Transaction inserted successfully', 'status' => true, 'trans_status' => $trans_status);

					if ($trans_status == '1') {
						

						$verification_requests = VerificationRequests::where('request_number',$student_key)->first();



						$scanning_requests = ScanningRequests::where('request_number',$student_key)->first();
						
						if (!empty($verification_requests)) {

							if(empty($user_id)){
								$user_id=$verification_requests->user_id;
							}
							VerificationRequests::where('request_number',$student_key)->update(['payment_status'=>'Paid']);

							$array = explode('_', $trans_id_ref);

							if ($array[1] == 'PU') {
								$gate = 'PAYU MONEY';
							} else if ($array[1] == 'PT') {
								$gate = 'PAYTM';
							} else if ($array[1] == 'ST') {
								$gate = 'SOLUTECH';
							}else if ($array[1] == 'PS') {
								$gate = 'PAYSTACK';
							}

							if ($array[1] == 'IAP') {
								$gate = 'Apple In-app purchase';
							}
							

							$users = User::where('id',$user_id)->first();

							$params["name"] = $users['fullname'];
							$params["email_id"] = $users['email_id'];
							$params["amount"] = $request["amount"];
							$params["app"] = $gate;
							$params["mobile"] = $users['mobile_no'];
							$params["trans_id"] = $trans_id_ref;
							$params["gateway_id"] = $trans_id_gateway;
							$params["mode"] = $payment_mode;
							$params["date"] = $transaction_date;
							$params["gateway"] = $gate;
							$params["key"] = $student_key;

							
							$requestData = DB::select( DB::raw("SELECT vr.*,b.branch_name_long,d.degree_name FROM verification_requests as vr
										INNER JOIN branch_master as b ON b.id=vr.branch
										INNER JOIN degree_master as d ON d.id=vr.degree
										where vr.request_number='" . $student_key . "'") );
							$requestData = json_decode(json_encode($requestData),true);
							$baseUrl=Config::get('constant.local_base_path').$subdomain[0].'/backend';
							if (!empty($requestData)) {

							
								$requestData = $requestData[0];
								$params["student_institute"] = $requestData['institute'];
								$params["student_name"] = $requestData['student_name'];
								$params["student_degree"] = $requestData['degree_name'];
								$params["student_branch"] = $requestData['branch_name_long'];
								$params["student_reg_no"] = $requestData['registration_no'];
								$params["passout_year"] = $requestData['passout_year'];
								$params["name_of_recruiter"] = $requestData['name_of_recruiter'];
								$params["offer_letter"] = (!empty($requestData['offer_letter'])) ? '<a href="' .$baseUrl.'/'.$requestData['offer_letter'] . '" target="_blank">Link</a>' : 'Not Uploded';
								$params["date_time_registraion"] = $requestData['created_date_time'];

								
								$requestDocuments = VerificationDocuments::where('request_id',$requestData['id'])->get()->toArray();
								$grade_card_files = '';
								$provisional_degree_files = '';
								$original_degree_files = '';
								$marksheet_files = '';
								$gradeFileCnt = 1;
								$provisionalDegreeCnt = 1;
								$originalDegreeCnt = 1;
								$marksheetCnt = 1;
								$gradeFileAmnt = 0;
								$provisionalDegreeAmnt = 0;
								$originalDegreeAmnt = 0;
								$marksheetAmnt = 0;

								if (!empty($requestDocuments)) {

									foreach ($requestDocuments as $readData) {
										switch ($readData['document_type']) {

										case 'Grade Card':
											$grade_card_files .= '<a href="' . $baseUrl.'/' . $readData['document_path'] . '" target="_blank">File' . $gradeFileCnt . '</a>&nbsp;&nbsp;';
											$gradeFileCnt++;
											$gradeFileAmnt = $gradeFileAmnt + $readData['document_price'];
											break;
										case 'Provisional Degree':
											$provisional_degree_files .= '<a href="' . $baseUrl.'/' . $readData['document_path'] . '" target="_blank">File' . $provisionalDegreeCnt . '</a>&nbsp;&nbsp;';
											$provisionalDegreeCnt++;
											$provisionalDegreeAmnt = $provisionalDegreeAmnt + $readData['document_price'];
											break;
										case 'Degree':
											$provisional_degree_files .= '<a href="' . $baseUrl.'/' . $readData['document_path'] . '" target="_blank">File' . $provisionalDegreeCnt . '</a>&nbsp;&nbsp;';
											$provisionalDegreeCnt++;
											$provisionalDegreeAmnt = $provisionalDegreeAmnt + $readData['document_price'];
											break;
										case 'Leaving Certificate':
											$original_degree_files .= '<a href="' . $baseUrl.'/' . $readData['document_path'] . '" target="_blank">File' . $originalDegreeCnt . '</a>&nbsp;&nbsp;';
											$originalDegreeCnt++;
											$originalDegreeAmnt = $originalDegreeAmnt + $readData['document_price'];
											break;
										case 'Marksheet':
											$marksheet_files .= '<a href="' . $baseUrl.'/' . $readData['document_path'] . '" target="_blank">File' . $marksheetCnt . '</a>&nbsp;&nbsp;';
											$marksheetCnt++;
											$marksheetAmnt = $marksheetAmnt + $readData['document_price'];
											break;

										default:
											$file = 'Not Found';
											break;
										}
									}
								}
								if (!empty($grade_card_files)) {
									$params["grade_card_files"] = $grade_card_files;
								} else {
									$params["grade_card_files"] = 'Not Uploaded';
								}
								$params["grade_card_amount"] = $gradeFileAmnt;

								if (!empty($provisional_degree_files)) {
									$params["provisional_degree_files"] = $provisional_degree_files;
								} else {
									$params["provisional_degree_files"] = 'Not Uploaded';
								}
								$params["provisional_degree_amount"] = $provisionalDegreeAmnt;

								if (!empty($original_degree_files)) {
									$params["original_degree_files"] = $original_degree_files;
								} else {
									$params["original_degree_files"] = 'Not Uploaded';
								}
								$params["original_degree_amount"] = $originalDegreeAmnt;

								if (!empty($marksheet_files)) {
									$params["marksheet_files"] = $marksheet_files;
								} else {
									$params["marksheet_files"] = 'Not Uploaded';
								}
								$params["marksheet_amount"] = $marksheetAmnt;
							}

							if($subdomain[0] == "galgotias"){
								$mail_view = 'mail.verify.transaction_galgotias';
							}else if($subdomain[0] == "monad"){
								$mail_view = 'mail.verify.transaction_monad';
							}else{
								$mail_view = 'mail.verify.transaction';
							}

							$params["subdomain"] = $subdomain[0];

							
							$mail_subject = '#' . $params['key'] . ' Candidate education details verification';
			                $user_email = $params['email_id'];
			                
			                
							$this->dispatch(new SendMailJob($mail_view,$user_email,$mail_subject,$params));


						}else if(!empty($scanning_requests)){

							

							ScanningRequests::where('request_number',$student_key)->update(['payment_status'=>'Paid']);


							$verification_requests = ScanningRequests::where('request_number',$student_key)->first();
							if(empty($user_id)){
								$user_id=$verification_requests->user_id;
							}


							if($subdomain[0]=="monad"){
							\DB::statement("SET SQL_MODE=''");
							$dataPdf = DB::select( DB::raw("SELECT sd.id, (CASE 
										        WHEN sd.is_valid = 0 THEN NULL
										        WHEN sd.is_valid = 1 THEN certificate_filename
										        ELSE NULL
										    END) AS certificate_filename, sd.is_valid,sd.document_key FROM scanned_documents as sd LEFT JOIN student_table as st ON sd.document_key=st.key OR (sd.document_key = st.serial_no AND st.status=1) where sd.request_id='" . $scanning_requests['id'] . "' GROUP BY sd.id") );
							
							}else{
							$dataPdf = DB::select( DB::raw("SELECT sd.id,st.certificate_filename FROM scanned_documents as sd
											INNER JOIN student_table as st ON sd.document_key=st.key
										where sd.request_id='" . $scanning_requests['id'] . "'") );	
							}
							
							$dataPdf = json_decode(json_encode($dataPdf),true);

							$message = array('service' => 'Transaction', 'message' => 'success', 'status' => true, 'showPdf' => true, 'dataPdf' => $dataPdf);

							$array = explode('_', $trans_id_ref);
							if ($array[1] == 'PU') {
								$gate = 'PAYU MONEY';
							} else if ($array[1] == 'PT') {
								$gate = 'PAYTM';
							} else if ($array[1] == 'ST') {
								$gate = 'SOLUTECH';
							}else if ($array[1] == 'PS') {
								$gate = 'PAYSTACK';
							}

							if ($array[1] == 'IAP') {
								$gate = 'Apple In-app purchase';
							}
							$users = User::where('id',$user_id)->first();

							$params["name"] = $users['fullname'];
							$params["email_id"] = $users['email_id'];
							$params["amount"] = $request["amount"];
							$params["app"] = $gate;
							$params["mobile"] = $users['mobile_no'];
							$params["trans_id"] = $trans_id_ref;
							$params["gateway_id"] = $trans_id_gateway;
							$params["mode"] = $payment_mode;
							$params["date"] = $transaction_date;
							$params["gateway"] = $gate;
							$params["key"] = $student_key;

							if($subdomain[0] == "galgotias"){
								$mail_view = 'mail.verify.success_scan_galgotias';
							}else if($subdomain[0] == "monad"){
								$mail_view = 'mail.verify.success_scan_monad';
							}else{
								$mail_view = 'mail.verify.success_scan';
							}
							$mail_subject = '#' . $params['key'] . ' Verification request fees payment received successfully ';
			                $user_email = $params['email_id'];

			               /* if($subdomain[0]=="demo"){
			                	print_r($subdomain[0]);
			                	print_r($users);
			                	exit;
			                }*/

							$this->dispatch(new SendMailJob($mail_view,$user_email,$mail_subject,$params));
							
						}

					}

				}
				break;
			
			default:
				# code...
				break;
		}
		echo json_encode($message);
	}

	
}

