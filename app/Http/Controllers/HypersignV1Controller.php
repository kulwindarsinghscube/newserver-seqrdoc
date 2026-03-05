<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Mail;
use Session;

use App\Mail\CertificateMail;
use App\Mail\EventPassMail;
use Carbon\Carbon;


class HypersignV1Controller extends Controller
{
    public function certificate() {

        return view('hypersign.certificate');
    }
    


    // Function to send the email
    public function sendEmail(Request $request)
    {
        try {
            // Get request data
            $data = $request->all();

            
            // Get recipient details from the request
            $vcAttributes = $data['recipientDetails'];

            foreach ($vcAttributes as $vc) {
                $date = date('Y-m-d H:i:s');  // Current date/time
                // $vc['issuedDate'] = $date;  // Set issued date

                // JWT secret from .env file
                $jwtSecret = env('JWT_SECRET', 'defaultSecret');

                // Create JWT token for the recipient
                
                
                // $token = JWT::encode(
                //     ['vcAttributes' => $vc, 'tokenType' => 'certificate', 'schemaId' => $data['schemaId']],
                //     $jwtSecret,
                //     'HS256'
                // );

                $token = $this->generateToken($vc,$data['schemaId']);
                // echo $token;
                // die();
                // Prepare the email based on the schema
                if ($data['schemaId'] === env('CERTIFICATE_SCHEMA')) {
                    // Send certificate email

                    try {   
                
                        Mail::to($vc['recipientEmail'])->send(new CertificateMail($vc, $token));
                    } catch (Exception $e) {
                        print_r($e);
                    }

                    // Mail::to($vc['recipientEmail'])->send(new CertificateMail($vc, $token));
                } elseif ($data['schemaId'] === env('EVENT_PASS_SCHEMA')) {
                    // Send event pass email
                    Mail::to($vc['recipientEmail'])->send(new EventPassMail($vc, $token));
                }
            }

            $url = url('/credential/issue/?jwt=' . $token);

            return response()->json(['message' => 'Emails sent successfully','url' => $url], 200);
        } catch (\Exception $e) {
            // Handle exception and return error message
            return response()->json(['error' => 'Failed to send emails: ' . $e->getMessage()], 500);
        }
    }



    // Method to generate JWT token
    public function generateToken($vc,$schemaId)
    {
        $header = json_encode(['alg' => 'HS256', 'typ' => 'JWT']);
        $payload = json_encode([
            'vcAttributes' => $vc,
            'tokenType' => 'certificate',
            'schemaId' => $schemaId,
            'iat' => time(), // Issued at
            'exp' => time() + 600, // Expiry time (10 minutes)
        ]);

        // Secret key to sign the token\

        
        // $secret = env('JWT_SECRET', 'your-secret-key'); // Store your secret key securely in .env
        $secret = 'rajabced12'; // Store your secret key securely in .env

        // Encode the header and payload to base64url
        $base64Header = rtrim(strtr(base64_encode($header), '+/', '-_'), '=');
        $base64Payload = rtrim(strtr(base64_encode($payload), '+/', '-_'), '=');

        // Create the signature
        $signature = hash_hmac('sha256', "$base64Header.$base64Payload", $secret, true);
        $base64Signature = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

        // Combine the parts to form the JWT
        $jwt = "$base64Header.$base64Payload.$base64Signature";

        return $jwt;
        // return response()->json(['token' => $jwt]);
    }



    public function showCertificate()
    {
        // echo "<pre>";
        // print_r($_GET);
        // die();

        $token = $_GET['jwt'];
        $data = $this->decodeToken($token);

        
        $vcDetails = $data['vcAttributes'];
        // $vcDetails = [
        //     'degreeName' => 'Master of Science',
        //     'recipientFullName' => 'John Doe',
        //     'degreeType' => 'Postgraduate',
        //     'issuerName' => 'University of Excellence',
        //     'issuedDate' => now()->toDateString()
        // ];

        return view('hypersign.certificateIssue', compact('vcDetails'));
    }


    public function ssiAccessToken()
    {  

        $key = '3575f9281d75b716132d6b8d16f1d.49d17d0b54c446c6cf900d7d2995871cd7e979c792e4fbe8b6f0bb9e862d0e490b84f5b44e8665607a782a692510ef348';
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.entity.dashboard.hypersign.id/api/v1/app/oauth',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'X-Api-Secret-Key: '.$key,
                'Origin: http://demo.seqrdoc.com'
            ],
        ]);

        $response = curl_exec($curl);

        // Check for errors
        if (curl_errno($curl)) {
            // Handle error
            $error_msg = curl_error($curl);
            dd($error_msg);
        }

        curl_close($curl);

        // Decode the response (if it's in JSON format)
        $data = json_decode($response, true);
        // echo "<pre>";
        // print_r($data);
        // echo "</pre>";

        Session::put('access_token', $data['access_token']);

        return $data['access_token'];

    }

    public function createDidold() {


        if(Session::get('access_token') ) {
            $jwtToken = Session::get('access_token');
        } else {
            $jwtToken = $this->ssiAccessToken();
        }
        // Define the base URL
        $baseUrl = "https://ent-7adde2b.api.entity.hypersign.id"; // Replace with your base URL
        $url = $baseUrl . "/api/v1/did/create";

        // Prepare the payload
        $payload = [
            "namespace" => "testnet"
        ];

        // Initialize cURL
        $ch = curl_init();

        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return the response as a string
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer ".$jwtToken, // Replace with your authorization token
            "Content-Type: application/json",
            "Origin: http://demo.seqrdoc.com"
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        // Execute the request
        $response = curl_exec($ch);

        // Check for errors
        if (curl_errno($ch)) {
            echo "cURL Error: " . curl_error($ch);
            curl_close($ch);
            return;
        }

        // Close cURL
        curl_close($ch);

        // Decode the response
        $responseData = json_decode($response, true);

        // Check if the response is successful
        if (isset($responseData['metaData']['didDocument'])) {
            $didDocument = $responseData['metaData']['didDocument'];
            // Do something with $didDocument
            // echo "DID Document: ";
            // echo "<pre>";
            // print_r($didDocument['id']);

            // Extract the 'id' value
            $idValue = $didDocument['id'];

            return $idValue;
        } else {
            echo "Error in response: ";
            print_r($responseData);
        }

    }


    public function createDid() {


        if(Session::get('access_token') ) {
            $jwtToken = Session::get('access_token');
        } else {
            $jwtToken = $this->ssiAccessToken();
        }
         $url = 'https://ent-7adde2b.api.entity.hypersign.id/api/v1/did/create';

        $payload = [
            "namespace" => "testnet",
            "options" => [
                "keyType" => [
                    "Ed25519VerificationKey2020"
                ],
                "chainId" => "0x1",
                "publicKey" => "z76tzt4XCb6FNqC3CPZvsxRfEDX5HHQc2VPux4DeZYndW",
                "walletAddress" => "0x01978e553Df0C54A63e2E063DFFe71c688d91C76"
            ]
        ];

        $token = $jwtToken;
        


        $headers = [
            'Authorization: Bearer ' . $token,
            'Origin: http://demo.seqrdoc.com',
            'Content-Type: application/json',
        ];

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => $headers,
        ]);

        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            $error = curl_error($curl);
            curl_close($curl);
            return response()->json(['error' => $error], 500);
        }

        curl_close($curl);


        // Decode the response
        $responseData = json_decode($response, true);

        // Check if the response is successful
        if (isset($responseData['metaData']['didDocument'])) {
            $didDocument = $responseData['metaData']['didDocument'];
            // Do something with $didDocument
            // echo "DID Document: ";
            // echo "<pre>";
            // print_r($didDocument['id']);

            // Extract the 'id' value
            $idValue = $didDocument['id'];

            return $idValue;
        } else {
            echo "Error in response: ";
            print_r($responseData);
        }

        // return $didId['did'];
        // return response()->json(json_decode($response, true));

    }


    public function credentialIssue(Request $request){

        $data = $request->json()->all();

        $vcDetails = $data['vcDetails'];       // Access vcDetails
        $additionalData = $data['additionalData']; // Access additionalData

        // Dynamic data from the request
        $fields = $data['vcDetails']; // Ensure fields are passed in JSON format
        $subjectDid = $additionalData['subjectDid'];
        $issuerDid = 'did:hid:testnet:2f582b51-e4c1-4ef4-bfec-877bd852cdd5';

        if(Session::get('access_token') ) {
            $jwtToken = Session::get('access_token');
        } else {
            $jwtToken = $this->ssiAccessToken();
        }
        $url = 'https://ent-7adde2b.api.entity.hypersign.id/api/v1/credential/issue'; // Update with the full API URL
        $authorization =  "Bearer ".$jwtToken; // Replace with your actual JWT token

        $data = [
            "schemaId" => "sch:hid:testnet:z6MkpHD47Fcg7xKh6WUL8qSJQTVxri5nPXmDVXJqNVNRTnob:1.0", // Replace with the actual schema ID
            "subjectDid" => $subjectDid, // Replace with the actual Subject DID
            "issuerDid" => $issuerDid, // Replace with the actual Issuer DID
            "schemaContext" => [
                "https://schema.org"
            ],
            "type" => [
                "StudentCredential"
            ],
            "expirationDate" => "2027-12-10T18:30:00.000Z", // Expiration date in ISO 8601 format
            "fields" => $fields,
            "namespace" => "testnet",
            "verificationMethodId" => "{$issuerDid}#key-1", // Replace ${idx} with the actual key index
            "persist" => true,
            "registerCredentialStatus" => true
        ];

        // print_r(json_encode($data));

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: '. $authorization,
            'Content-Type: application/json',
            'Origin: http://demo.seqrdoc.com'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'Error: ' . curl_error($ch);
        }

        curl_close($ch);
        




        // Convert the array to JSON
        // $jsonData = json_encode($response, JSON_PRETTY_PRINT);
        $jsonData = $response;

        // Define the file name
        // $fileName = 'credential.json';
        $filePath = public_path().'/credential.json';
        // Write content to the file
        file_put_contents($filePath, $jsonData);

        // // Return a response to trigger download
        // return response($jsonData)
        //     ->header('Content-Type', 'application/json')
        //     ->header('Content-Disposition', "attachment; filename=$fileName");
        // Check if the file exists before attempting to download
        // if (file_exists($filePath)) {
        //     return response()->download($filePath, 'credential.json');
        // }

        // return response()->json(['error' => 'File not found'], 404);


        // print_r($response);
        // echo $response;

        return response()->json(['success'=>true,'msg'=>'Credential Issue successfully']);

    }


    public function jsonFileDownload(){
        $filename = public_path().'/credential.json';
  
        if (file_exists($filename)) {
            return \Response::download($filename);
        }
        unlink($filename);
  
    }



    // Method to decode and verify JWT token
    public function decodeToken($token)
    {
        // $token = $request->input('token');
        // $token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ2Y0F0dHJpYnV0ZXMiOnsicmVjaXBpZW50RnVsbE5hbWUiOiJSYWogVmlqYXlrdW1hciBQYXRpbCIsInJlY2lwaWVudEVtYWlsIjoicmFqLnYucGF0aWwxMDhAZ21haWwuY29tIiwiZGVncmVlVHlwZSI6IkJhY2hlbG9yIiwiZGVncmVlTmFtZSI6IkVuZ2luZWVyaW5nIiwiZGF0ZU9mQmlydGgiOiIxOTk5LTExLTIzVDE4OjMwOjAwLjAwMFoiLCJkZWdyZWVFYXJuZWREYXRlIjoiMTk5OS0wMy0yNlQxODozMDowMC4wMDBaIiwiaXNzdWVyTmFtZSI6IldJVCBTb2xhcHVyIiwiZW5yb2xsbWVudE51bWJlciI6IjIwMTgxMjMxMjMxIiwiaXNzdWVkRGF0ZSI6IjIwMjQtMTItMjcgMTY6NDc6MzkifSwidG9rZW5UeXBlIjoiY2VydGlmaWNhdGUiLCJzY2hlbWFJZCI6InNjaDpoaWQ6dGVzdG5ldDp6Nk1rcEhENDdGY2c3eEtoNldVTDhxU0pRVFZ4cmk1blBYbURWWEpxTlZOUlRub2I6MS4wIiwiaWF0IjoxNzM1Mjk4MjU5LCJleHAiOjE3MzUyOTg4NTl9.ZzWQibrJtC9szcCRPGTvHYqhL7H6tV2ZSPboFDALNdE';
        $secret = 'rajabced12'; // Secret key

        // Split the token into its three parts
        list($base64Header, $base64Payload, $base64Signature) = explode('.', $token);

        // Decode the header and payload
        $header = json_decode(base64_decode(strtr($base64Header, '-_', '+/')), true);
        $payload = json_decode(base64_decode(strtr($base64Payload, '-_', '+/')), true);

        // Recreate the signature from the token to verify its validity
        $signature = base64_decode(strtr($base64Signature, '-_', '+/'));
        $dataToSign = "$base64Header.$base64Payload";
        $expectedSignature = hash_hmac('sha256', $dataToSign, $secret, true);

        // Check if the signatures match
        if (hash_equals($expectedSignature, $signature)) {
           
            return $payload;
            // return response()->json(['data' => $payload]);
        } else {
            return response()->json(['error' => 'Invalid token'], 400);
        }
    }

}
