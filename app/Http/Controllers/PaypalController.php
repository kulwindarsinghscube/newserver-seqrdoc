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

class PaypalController extends Controller
{
    
  

    public function paypalToken() {
        
        error_reporting(E_ALL);
        error_reporting(-1);
        ini_set('error_reporting', E_ALL);
        //open connection
        $ch = curl_init();
        $client = "AULgVgmjK92ckKJFQzDlBoxmrdp5i7Z1Kwx-m1KvsibRjPNxgdbfTKtRG6R7rpR53ekCHz_pkcPmdYiC";
        $secret = "EDO6uB-wVr58OfJA89-Vv0Y_GCNPFcNj3YHvopKOByL3VUvNbpjy1NQ1YxwiVXQySpYX9qKdFsc24KFB";
        curl_setopt($ch, CURLOPT_URL, "https://api.sandbox.paypal.com/v1/oauth2/token");
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
        curl_setopt($ch, CURLOPT_USERPWD, $client.":".$secret);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
        $result = curl_exec($ch);
        
        if(empty($result))die("Error: No response.");
        else
        {
            $json = json_decode($result);
            // print_r($json->access_token);
            return $json->access_token;
        }

        curl_close($ch);
    }


    public function paypalPayment() {
        
        error_reporting(E_ALL);
        error_reporting(-1);
        ini_set('error_reporting', E_ALL);
        //open connection
        $ch = curl_init();
        $client = "AULgVgmjK92ckKJFQzDlBoxmrdp5i7Z1Kwx-m1KvsibRjPNxgdbfTKtRG6R7rpR53ekCHz_pkcPmdYiC";
        $secret = "EDO6uB-wVr58OfJA89-Vv0Y_GCNPFcNj3YHvopKOByL3VUvNbpjy1NQ1YxwiVXQySpYX9qKdFsc24KFB";
        curl_setopt($ch, CURLOPT_URL, "https://api.sandbox.paypal.com/v1/oauth2/token");
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
        curl_setopt($ch, CURLOPT_USERPWD, $client.":".$secret);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
        $result = curl_exec($ch);

        print_r($result);
        die();
        if(empty($result))die("Error: No response.");
        else
        {
            $json = json_decode($result);
        }
        curl_close($ch);
        $c = curl_init();
        $data = '{
        "intent":"sale",
        "payer": {
            "payment_method": "CREDIT_CARD",
            "funding_instruments": [
            {
                "credit_card": {
                "number": "5454545454545454",
                "type": "mastercard",
                "expire_month": 12,
                "expire_year": 2021,
                "cvv2": 111,
                "first_name": "Joe",
                "last_name": "Shopper"
                }
            }
            ]
        },
        "transactions":[
            {
            "amount":{
                "total":"7.47",
                "currency":"USD"
            },
            "description":"This is the payment transaction description."
            }
        ]
        }
        ';
        curl_setopt($c, CURLOPT_URL, "https://api.sandbox.paypal.com/v2/payments/payment");
        curl_setopt($c, CURLOPT_CUSTOMREQUEST ,"POST");
        curl_setopt($c, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($c, CURLOPT_POSTFIELDS, $data); 
        curl_setopt($c, CURLOPT_HTTPHEADER, array("Content-Type: application/json","Authorization: Bearer ".$json->access_token));
        $result = curl_exec($c);
        curl_close($c);
        if(empty($result))die("Error: No response.");
        else
        {
            $json = json_decode($result);
            print_r($result);
        }



    }

    public function paypalPaymentProcess() {

        $curl = curl_init();

        $accessToken = $this->paypalToken();


        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.sandbox.paypal.com/v1/payments/payouts",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => '{
        "sender_batch_header": {
            "email_subject": "You have a payment",
            "sender_batch_id": "batch-1719204300144"
        },
        "items": [
            {
            "recipient_type": "PHONE",
            "amount": {
                "value": "1.00",
                "currency": "USD"
            },
            "receiver": "4087811638",
            "note": "Payouts sample transaction",
            "sender_item_id": "item-1-1719204300144"
            },
            {
            "recipient_type": "EMAIL",
            "amount": {
                "value": "1.00",
                "currency": "USD"
            },
            "receiver": "ps-rec@paypal.com",
            "note": "Payouts sample transaction",
            "sender_item_id": "item-2-1719204300144"
            },
            {
            "recipient_type": "PAYPAL_ID",
            "amount": {
                "value": "1.00",
                "currency": "USD"
            },
            "receiver": "FSMRBANCV8PSG",
            "note": "Payouts sample transaction",
            "sender_item_id": "item-3-1719204300144"
            }
        ]
        }',
        CURLOPT_HTTPHEADER => array(
            'accept: application/json',
            'authorization: Bearer '.$accessToken,
            'content-type: application/json'
        ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
        echo "cURL Error #:" . $err;
        } else {
        echo $response;
        }


    }



    public function paypalCheckout() {
        
        $config = [
            'client_id' => 'AULgVgmjK92ckKJFQzDlBoxmrdp5i7Z1Kwx-m1KvsibRjPNxgdbfTKtRG6R7rpR53ekCHz_pkcPmdYiC',
            'secret' => 'EDO6uB-wVr58OfJA89-Vv0Y_GCNPFcNj3YHvopKOByL3VUvNbpjy1NQ1YxwiVXQySpYX9qKdFsc24KFB',
            'settings' => [
                'mode' => 'sandbox', // or 'live'
                'base_url' => 'https://api.sandbox.paypal.com', // or 'https://api.paypal.com' for live
            ],
        ];
        // echo $config['client_id'];
        // die();

        // Get access token from PayPal
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $config['settings']['base_url'] . "/v1/oauth2/token");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_USERPWD, $config['client_id'] . ':' . $config['secret']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");

        $headers = [];
        $headers[] = "Accept: application/json";
        $headers[] = "Accept-Language: en_US";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);

        $result = json_decode($result);
        $access_token = $result->access_token;

        // Create payment
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $config['settings']['base_url'] . "/v1/payments/payment");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);

        $headers = [];
        $headers[] = "Content-Type: application/json";
        $headers[] = "Authorization: Bearer " . $access_token;
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $paymentData = '{
        "intent": "sale",
        "payer": {
            "payment_method": "paypal"
        },
        "transactions": [{
            "amount": {
            "total": "1.00",
            "currency": "USD"
            },
            "description": "Payment description"
        }],
        "redirect_urls": {
            "return_url": "http://localhost/PaymentGateway/rohit-paypal/execute_payment.php?success=true",
            "cancel_url": "http://localhost/PaymentGateway/rohit-paypal/execute_payment.php?success=false"
        }
        }';

        curl_setopt($ch, CURLOPT_POSTFIELDS, $paymentData);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);

        $result = json_decode($result);
        $approvalUrl = '';

        foreach ($result->links as $link) {
            if ($link->rel == 'approval_url') {
                $approvalUrl = $link->href;
                break;
            }
        }

        header("Location: {$approvalUrl}");
        exit;

    }

}   
