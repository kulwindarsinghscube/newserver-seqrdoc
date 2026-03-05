<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\convocation\StudentVerified;
use App\Models\convodataverification\ConvoStudent;
use PaytmWallet;
use  App\Mail\convocation\PaymentConfirmation;
use App\Models\convodataverification\StudentTransaction;
use Illuminate\Support\Facades\Config; 
use Carbon\Carbon;
use App\Models\convodataverification\StudentTransactionTemp;
class VerifyPendingPayment extends Command
{
    protected $signature = 'payment:verify-status';
    protected $description = 'Send email notifications to students who have not received them yet';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        Log::info('Started verify payment status Command ');
        \DB::disconnect('mysql'); 
        \Config::set("database.connections.mysql", [
            'driver'   => 'mysql',
            'host'     => \Config::get('constant.DB_HOST'),
            "port" => \Config::get('constant.DB_PORT'),
            'database' => 'seqr_d_mitwpu',
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
        $mode = 'PROD';
        if($mode == "TEST"){
            Config::set('services.paytm-wallet.merchant_id', 'Resell00448805757124');
            Config::set('services.paytm-wallet.merchant_key', 'KXHUJH&Ywq9pUkkr');
            Config::set('services.paytm-wallet.merchant_website', 'DEFAULT');
            Config::set('services.paytm-wallet.channel', 'WEB');
            Config::set('services.paytm-wallet.industry_type', 'PrivateEducation');
            Config::set('services.paytm-wallet.env', 'local'); 
        }else{
            Config::set('services.paytm-wallet.merchant_id', 'Examin21953342873238');
            Config::set('services.paytm-wallet.merchant_key', 'sNmtRvwWj_QQZ93A');
            Config::set('services.paytm-wallet.merchant_website', 'DEFAULT');
            Config::set('services.paytm-wallet.channel', 'WEB');
            Config::set('services.paytm-wallet.industry_type', 'PrivateEducation');
            Config::set('services.paytm-wallet.env', 'production'); 
        }
         
        // Calculate the date 5 days ago
        $startDate = date("Y-m-d", strtotime("-30 days"));
        $endDate = date("Y-m-d");

        // Fetch transactions from the last 5 days with a status of 'PENDING'
        $transactions = StudentTransaction::whereDate('txn_date', '>=', $startDate)
             ->whereDate('txn_date', '<=', $endDate)
            ->where('status', 'PENDING')
            ->get();
            // dd($date,$transactions);
        // Current timestamp for updating the transactions
        $transaction_date = date('Y-m-d H:i:s');
    
       

        // Check if there are any transactions to process
        if ($transactions->isNotEmpty()) {
            foreach ($transactions as $readTransaction) {
                // Prepare and check the status for each transaction
                $status = PaytmWallet::with('status');
                $status->prepare(['order' => $readTransaction->order_id]);
                $status->check();
    
                $response = $status->response();
    
    
                // If the status check is successful and the status is not 'PENDING'
                if (!empty($response)) {
                    // Update the transaction record with the response details
                    // dd($response['STATUS']);
                    Log::info('Latest Status for '.@$readTransaction->order_id." is ".@$response['STATUS']);
                    $readTransaction->fill([
                        'txn_id' => $response['TXNID'],
                        'payment_mode' => $response['PAYMENTMODE'],
                        'txn_amount' => $response['TXNAMOUNT'],
                        'status' => $response['STATUS'],
                        'bank_txn_id' => $response['BANKTXNID'],
                        'updated_at' => $transaction_date,
                    ])->save(); 
                   
                    // If the transaction is successful, send a confirmation email to the student
                    if ($response['STATUS'] == 'TXN_SUCCESS') {
                        $student = ConvoStudent::where('is_printed',0)->where('id', $readTransaction->student_id)->first();
                        $student_transaction_temp = StudentTransactionTemp::where('order_id',$readTransaction->order_id)->first();
                        if(!empty($student_transaction_temp)){
                            $student_transaction_temp->status = $response['STATUS'];
                            $student_transaction_temp->save(); 
                        }
                        if (!empty($student)) {
                            // $paymentDetails = [
                            //     'txn_amount' => $readTransaction->amount, // Replace with actual field names
                            //     'txn_id' => $readTransaction->transaction_id, // Replace with actual field names
                            //     'bank_txn_id' => $readTransaction->bank_transaction_id, // Replace with actual field names
                            //     'txn_date' => $readTransaction->transaction_date, // Replace with actual field names
                            //     'status' => $readTransaction->status, // Replace with actual field names
                            //     'payment_mode' => $readTransaction->payment_mode, // Replace with actual field names
                            // ];
                            $txnDate = Carbon::parse($response['TXNDATE']); 
                            $paymentDetails = [
                                'txn_amount' => $response['TXNAMOUNT'],
                                'txn_id' => $response['TXNID'],
                                'bank_txn_id' => $response['BANKTXNID'],
                                'txn_date' => $txnDate, // Ensure this variable is set correctly
                                'status' => $response['STATUS'],
                                'payment_mode' => $response['PAYMENTMODE'],
                            ];
                            $update_data = [];
                            if ($student->status == 'student acknowledge all data as correct but payment & preview pdf approval is pending') {
                                $update_data['status'] = 'student acknowledge all data as correct, Payment is completed but preview pdf approval is pending';
                            }
                            if( $student->status == 'student re-acknowledged new data as correct but payment & preview pdf approval is pending'){
                                 $update_data['status'] = 'student re-acknowledged new data as correct, Payment is completed but preview pdf approval is pending';
                            }
                            if (!empty($update_data)) {
                                if ((!empty($readTransaction) && $readTransaction->changed_collection_mode == 0)) {

                                    ConvoStudent::where('is_printed',0)->where('id', $readTransaction->student_id)->update($update_data);
                                    Log::info("Updated Status to ".$update_data['status']);
                                }   
                                   
                            }

                            if((!empty($readTransaction) && $readTransaction->changed_collection_mode == 1)){
                              
                                $update_data_collection['collection_mode'] = "Attending Convocation";
                                ConvoStudent::where('is_printed',0)->where('id', $readTransaction->student_id)->update($update_data_collection);
                                Log::info("collection_mode Attending Convocation");
                            }
                            try { 
                                Mail::to($student->wpu_email_id)->send(new PaymentConfirmation($paymentDetails, $student));
                                Log::info("From Cron Pending to Success Status, Email sent payment confirmation email: $student->wpu_email_id");
                            } catch (\Exception $e) {
                                Log::error("From Cron Pending to Success Status, Failed to payment confirmation email  : $student->wpu_email_id " . $e->getMessage());
                            }
                        }
                    } 
                }
            }
        }

        $transactions_temp = StudentTransactionTemp::whereDate('txn_date', '>=', $startDate)
        ->whereDate('txn_date', '<=', $endDate)
        ->where('status', 'PENDING')->get();

        if ($transactions_temp->isNotEmpty()) {
            foreach ($transactions_temp as $readTransaction) {
                Log::info("Processing transaction for order ID: {$readTransaction->order_id}");

                // Prepare and check the status for each transaction
                $status = PaytmWallet::with('status');
                $status->prepare(['order' => $readTransaction->order_id]);
                $status->check();

                $response = $status->response(); 
                if ($response) {
                    $readTransaction->fill(['status' => $response['STATUS']])->save();
                    Log::info("Transaction status for order ID: {$readTransaction->order_id} is {$response['STATUS']}");

                    if ($response['STATUS'] == 'TXN_SUCCESS' || $response['STATUS'] == 'PENDING') { 
                        $student_transaction_temp = StudentTransaction::where('order_id', $readTransaction->order_id)->first();
                        $txnDate = Carbon::parse($response['TXNDATE']); 
                        $data = [
                            'student_id' => $readTransaction->student_id,
                            'order_id' => $readTransaction->order_id,
                            'txn_id' => $response['TXNID'],
                            'payment_mode' => $response['PAYMENTMODE'],
                            'txn_amount' => $response['TXNAMOUNT'],
                            'status' => $response['STATUS'],
                            'bank_txn_id' => $response['BANKTXNID'],
                            'txn_date' => $txnDate,
                            'gateway_name' => $response['GATEWAYNAME'], 
                            'response_message' => $response['RESPMSG'],
                            'bank_name' => $response['BANKNAME'], 
                            'mid' => $response['MID'],
                            'response_code' => $response['RESPCODE'],  
                            'changed_collection_mode'=>$readTransaction->changed_collection_mode
                        ];
                        
                        if (!empty($student_transaction_temp)) {
                            // Update the existing StudentTransaction
                            StudentTransaction::where('order_id', $readTransaction->order_id)->update($data);
                            Log::info("Updated existing StudentTransaction for order ID: {$readTransaction->order_id}");
                        } else {
                            // Create a new StudentTransaction
                            StudentTransaction::create($data);
                            Log::info("Created new StudentTransaction for order ID: {$readTransaction->order_id}");
                        }
                    } else {
                        Log::info("Transaction failed for order ID: {$readTransaction->order_id} with status: {$response['STATUS']}");
                    }
                } else {
                    Log::error("No response received for order ID: {$readTransaction->order_id}");
                }

                // If the status check is successful and the status is not 'PENDING'
                if ($status->isSuccessful() && $response) { 
                    if ($response['STATUS'] == 'TXN_SUCCESS') {
                        $student = ConvoStudent::where('is_printed',0)->where('id', $readTransaction->student_id)->first();
                        
                        if (!empty($student)) {
                            $paymentDetails = [
                                'txn_amount' => $response['TXNAMOUNT'],
                                'txn_id' => $response['TXNID'],
                                'bank_txn_id' => $response['BANKTXNID'],
                                'txn_date' => $txnDate, // Ensure this variable is set correctly
                                'status' => $response['STATUS'],
                                'payment_mode' => $response['PAYMENTMODE'],
                            ];

                            $update_data = [];
                            if ($student->status == 'student acknowledge all data as correct but payment & preview pdf approval is pending') {
                                $update_data['status'] = 'student acknowledge all data as correct, Payment is completed but preview pdf approval is pending';
                            }
                            if( $student->status == 'student re-acknowledged new data as correct but payment & preview pdf approval is pending'){
                            $update_data['status'] = 'student re-acknowledged new data as correct, Payment is completed but preview pdf approval is pending';
                            }
                            if (!empty($update_data) && (!empty($readTransaction) && $readTransaction->changed_collection_mode == 0)) {
                                    ConvoStudent::where('is_printed',0)->where('id', $readTransaction->student_id)->update($update_data);
                                    Log::info("Updated Status to ".$update_data['status']);
                            }
                            if((!empty($readTransaction) && $readTransaction->changed_collection_mode == 1)){
                                $update_data_collection['collection_mode'] = "Attending Convocation";
                                ConvoStudent::where('is_printed',0)->where('id', $readTransaction->student_id)->update($update_data_collection);
                            }
                            try {
                                Mail::to($student->wpu_email_id)->send(new PaymentConfirmation($paymentDetails, $student));
                                Log::info("Sent payment confirmation email to student ID: {$readTransaction->student_id} for order ID: {$readTransaction->order_id}");
                            } catch (\Exception $e) {
                                Log::error('Failed to send email for user ' . $student->wpu_email_id . ': ' . $e->getMessage());
                            }
                        } else {
                            Log::warning("No student found for ID: {$readTransaction->student_id}");
                        }
                    }
                }
            }
        } else {
            Log::info("No pending transactions found between {$startDate} and {$endDate}");
        }

        Log::info('Ended verify payment status Command ');
        // $this->info('Emails sent successfully!');
    }


    public function paytmTransStatusCheck() {

       
    }
}
