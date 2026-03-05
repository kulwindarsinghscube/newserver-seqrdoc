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

use App\Models\StudentTable;

class MpesaController extends Controller
{
    



    public function mpesaToken(){

        $consumerKey = 'w7e3QXMoCqEb67kxoB5RqTMyfADBQwjwk1cLoWfHlcHDDrFY';
        $consumerSecret = 'RKUcOpGzu6LgpJmucJ299KhE0ulXXDdqvhvVMNBlRfxWxpniygHLoJj7b5KGv4XB';

        $credentials = base64_encode($consumerKey . ':' . $consumerSecret);
    
        $url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: Basic ' . $credentials));
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $curl_response = curl_exec($curl);
        curl_close($curl);

        $result = json_decode($curl_response);
        return $result->access_token;

    }

    // $accessToken = $this->mpesaToken();


    public function MpesaSentAmount() {

        $shortCode = '600977';
        $amount = '10';
        $phone = '254708374149';
        $accountReference = 'ACCOUNT_REFERENCE'; // This can be any reference you'd like to use for the transaction.
        $transactionDesc = 'TRANSACTION_DESCRIPTION'; // Description of the transaction.
        $remarks = 'REMARKS'; // Any remarks you'd like to add.
        
        $url = 'https://sandbox.safaricom.co.ke/mpesa/b2c/v1/paymentrequest';

        $accessToken = $this->mpesaToken();
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Authorization:Bearer ' . $accessToken));
        
        $curl_post_data = array(
            'InitiatorName' => 'testapi', // Replace with your initiator name.
            'SecurityCredential' => 'p2K6gZStDJwxATowWWotSHBulnKkFQiwuBTPCZ525OHtmdRenywlQ6vu/O0z2860N42e6+PvxHEsSq9zewWS7HsdE7aBeYyMicckLHf1lJhjZd0DwMr5+tKl1ZkHAYccTkIZ/w2+0vbdfDdefRc8YaNkyp668rJsqEnvmHjuBBBdu0yZYgDczSn60A1Axf6+RhFupmOyBkIhAQqnWNJZxsQIgDvjXedoFgmdKQ8wqEojL7bjvOeu8s1GKVXHgtnpQ/Ptq3H/vv65XmbaBPGxdwWm1IY7emMBqncaBW/4h3VZoYuazuvqXFYjcwAq1e8cF8Nt+sMAfjkaiNAzzi9dcQ==', // Replace with your security credential.
            'CommandID' => 'BusinessPayment', // This can be BusinessPayment, SalaryPayment, or PromotionPayment.
            'Amount' => $amount,
            'PartyA' => $shortCode,
            'PartyB' => $phone,
            'Remarks' => $remarks,
            'QueueTimeOutURL' => 'https://demo.seqrdoc.com/mpesa_timeout_url', // URL to handle timeout response.
            'ResultURL' => 'https://demo.seqrdoc.com/mpesa_result_url', // URL to handle success response.
            'Occasion' => $accountReference
        );
        
        $data_string = json_encode($curl_post_data);
        
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        
        $response = curl_exec($curl);
        
        if ($response === false) {
            die(curl_error($curl));
        }
        
        echo $response;
        
        curl_close($curl);


    }


    public function mpesaTimeout() {
        echo "queue";
    }

    public function mpesaResult() {
        echo "result";
    }

}
