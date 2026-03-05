<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ApiTracker;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ApiTrakerExport;
use Mail;
use Session;
//use Illuminate\Support\Facades\Mail;
use App\Exports\TemplateMasterExport;
use App\Jobs\SendMailJob;
use Storage;

class PaypalV1Controller extends Controller
{
    
  

    function getAccessToken() {
        $ch = curl_init();

        $clientId = 'AULgVgmjK92ckKJFQzDlBoxmrdp5i7Z1Kwx-m1KvsibRjPNxgdbfTKtRG6R7rpR53ekCHz_pkcPmdYiC';
        $secret = 'EDO6uB-wVr58OfJA89-Vv0Y_GCNPFcNj3YHvopKOByL3VUvNbpjy1NQ1YxwiVXQySpYX9qKdFsc24KFB';
        curl_setopt($ch, CURLOPT_URL, "https://api.sandbox.paypal.com/v1/oauth2/token");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
        curl_setopt($ch, CURLOPT_USERPWD, $clientId . ":" . $secret);

        $headers = array();
        $headers[] = "Accept: application/json";
        $headers[] = "Accept-Language: en_US";
        $headers[] = "Content-Type: application/x-www-form-urlencoded";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);

        $json = json_decode($result);
        return $json->access_token;
    }




    function createPayment() {

        $accessToken = $this->getAccessToken();

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://api.sandbox.paypal.com/v1/payments/payment");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);

        $paymentData = '{
            "intent": "sale",
            "payer": {
                "payment_method": "paypal"
            },
            "transactions": [{
                "amount": {
                    "total": "10.00",
                    "currency": "USD"
                },
                "description": "Payment description"
            }],
            "redirect_urls": {
                "return_url": "https://demo.seqrdoc.com/paypal/success",
                "cancel_url": "https://demo.seqrdoc.com/paypal/cancel"
            }
        }';

        curl_setopt($ch, CURLOPT_POSTFIELDS, $paymentData);

        $headers = array();
        $headers[] = "Content-Type: application/json";
        $headers[] = "Authorization: Bearer " . $accessToken;
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);

        $json = json_decode($result);
        foreach ($json->links as $link) {
            if ($link->rel == "approval_url") {
                header("Location: " . $link->href);
                exit;
            }
        }
    }




    function executePayment($accessToken, $paymentId, $payerId) {
         
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://api.sandbox.paypal.com/v1/payments/payment/$paymentId/execute");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);

        $data = json_encode(["payer_id" => $payerId]);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $headers = array();
        $headers[] = "Content-Type: application/json";
        $headers[] = "Authorization: Bearer " . $accessToken;
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);

        $json = json_decode($result);
        return $json;
    }


    function success() {
        
        if (!isset($_GET['paymentId']) || !isset($_GET['PayerID'])) {
            die('Payment not made');
        }

        $accessToken = $this->getAccessToken();      
        $paymentId = $_GET['paymentId'];
        $payerId = $_GET['PayerID'];

        $result = $this->executePayment($accessToken, $paymentId, $payerId);

        if ($result->state == 'approved') {
            echo "<pre>";
            print_r($result);
            echo "</pre>";

            echo 'Payment success';

        } else {
            echo 'Payment failed';
        }


    }


    function cancel() {
        echo 'Payment cancelled';
    }


    

}   
