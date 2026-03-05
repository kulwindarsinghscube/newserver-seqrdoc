<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\Helpers\CoreHelper;
use GuzzleHttp\Client;

class BlockchainAuthorizationController extends Controller
{

    public function test() {
        // create & initialize a curl session
        $curl = curl_init();
        // set our url with curl_setopt()
        curl_setopt($curl, CURLOPT_URL, "https://veraciousapis.herokuapp.com/v1/mainnet/verifyWeb3Session");
        // return the transfer as a string, also with setopt()
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        // curl_exec() executes the started curl session
        // $output contains the output string
        $output = curl_exec($curl);
        // close curl resource to free up system resources
        // (deletes the variable made by curl_init)
        // print_r($output);
        // die();
        curl_close($curl);
        return view('admin.blockchain_authorization.test');
    }


	public function live() {
        
         // create & initialize a curl session
         $curl = curl_init();
         // set our url with curl_setopt()
         curl_setopt($curl, CURLOPT_URL, "https://mainnet-apis.herokuapp.com/v1/mainnet/verifyWeb3Session");
         // return the transfer as a string, also with setopt()
         curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
         // curl_exec() executes the started curl session
         // $output contains the output string
        $output = curl_exec($curl);
         // close curl resource to free up system resources
         // (deletes the variable made by curl_init)
         curl_close($curl);
        
        //  print_r($output);
         return view('admin.blockchain_authorization.live');
        
    }


    
}
