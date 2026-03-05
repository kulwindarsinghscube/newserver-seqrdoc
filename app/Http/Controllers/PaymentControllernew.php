<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentControllernew extends Controller
{
      public function index()
    {
        return view('admin.payment.index'); 
    }

    public function paymentRequest(Request $request)
    {
        $merchantId  = env('CCAVENUE_MERCHANT_ID');
        $accessCode  = env('CCAVENUE_ACCESS_CODE');
        $workingKey  = env('CCAVENUE_WORKING_KEY');
        $redirectUrl = env('CCAVENUE_REDIRECT_URL');
        $cancelUrl   = env('CCAVENUE_CANCEL_URL');

        $orderId = 'SeQR_PT_'.strtotime("now");
        $amount  = $request->amount;

        $merchantData = [
            "merchant_id"  => $merchantId,
            "order_id"     => $orderId,
            "currency"     => "INR",
            "amount"       => $amount,
            "redirect_url" => $redirectUrl,
            "cancel_url"   => $cancelUrl,
            "language"     => "EN",
            "billing_name" => $request->billing_name,
            "billing_tel"  => $request->billing_tel,
            "billing_email"=> $request->billing_email,
        ];
// dd($merchantData);
        $merchantDataString = http_build_query($merchantData);

        // Encrypt data
        $encryptedData = $this->encrypt($merchantDataString, $workingKey);
        

        $paymentUrl  = env('CCAVENUE_SANDBOX_URL');

        return view('admin.payment.redirect', compact('encryptedData', 'accessCode', 'paymentUrl'));
    }

    public function paymentResponse(Request $request)
    {
        $workingKey  = env('CCAVENUE_WORKING_KEY');
        $encResponse = $request->encResp;

        $rcvdString = $this->decrypt($encResponse, $workingKey);
        parse_str($rcvdString, $response);

        // Save to DB
        DB::table('ccavenue_transactions')->insert([
            'order_id'        => $response['order_id'] ?? null,
            'tracking_id'     => $response['tracking_id'] ?? null,
            'bank_ref_no'     => $response['bank_ref_no'] ?? null,
            'order_status'    => $response['order_status'] ?? null,
            'failure_message' => $response['failure_message'] ?? null,
            'payment_mode'    => $response['payment_mode'] ?? null,
            'status_message'  => $response['status_message'] ?? null,
            'currency'        => $response['currency'] ?? null,
            'amount'          => $response['amount'] ?? null,
            'billing_tel'     => $response['billing_tel'] ?? null,
            'billing_email'   => $response['billing_email'] ?? null,
            'trans_date'      => $response['trans_date'] ?? null,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);
        

        return view('admin.payment.response', compact('response'));
    }

    // Cancel
    public function paymentCancel()
    {
        return "Payment Cancelled";
    }

    // AES Encrypt
    private function encrypt($plainText, $key)
    {
        $secretKey  = pack('H*', md5($key));
        $initVector = pack("C*", 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15);
        $encryptedText = openssl_encrypt($plainText, "AES-128-CBC", $secretKey, OPENSSL_RAW_DATA, $initVector);
        return bin2hex($encryptedText);
    }

    // AES Decrypt
    private function decrypt($encryptedText, $key)
    {
        $secretKey  = pack('H*', md5($key));
        $initVector = pack("C*", 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15);
        $encryptedText = hex2bin($encryptedText);
        return openssl_decrypt($encryptedText, "AES-128-CBC", $secretKey, OPENSSL_RAW_DATA, $initVector);
    }

}