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
use TCPDF;
use TCPDF_FONTS;
use App\Models\StudentTable;
use App\models\TemplateMaster;
use App\models\BackgroundTemplateMaster;
use App\models\FieldMaster;
use QrCode;


class HypersignRNDController extends Controller
{
    
    public function test()
    {  

        echo "test";

    }


    public function kycAccessToken()
    {  

        $key = 'd4ba826a09d7ecb33dfd470251aa8.98146b0bae354db7a306c32ff457c57f28048b8ba7db9a28fca257819af48286a7b2dcab3a686ed234683c21de1a55599';
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
        echo "<pre>";
        print_r($data);
        echo "</pre>";


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
        echo "<pre>";
        print_r($data);
        echo "</pre>";

        Session::put('access_token', $data['access_token']);

    }


    public function sessionId()
    { 
        $curl = curl_init();

        $data = [
            'user_id' => 'dev7@scube.net.in',   // Replace with actual user ID
            'kyc_level' => 'basic',  // Or 'advanced', based on the level you need
            'callback_url' => 'https://demo.seqrdoc.com/hypersign/callback' // Your callback URL
        ];

        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.entity.dashboard.hypersign.id/api/v1/e-kyc/verification/session', // Replace with actual Hypersign KYC endpoint
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer 46c860195be7bb29561a350cad702.1558ee2e36bc9cfc8dfe2ba24532aef13bf86aa2d7ad87d8df3c3dc9c740f06220baedee02b3841f39b25643ecff03f84' // Replace with actual API key
            ],
        ]);

        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            $error_msg = curl_error($curl);
            dd($error_msg);  // Handle error
        }

        curl_close($curl);

        // Decode the response (assuming it's in JSON format)
        $responseData = json_decode($response, true);

        // Extract session ID if available
        $kycSessionId = $responseData['session_id'] ?? null;

        if ($kycSessionId) {
            echo "KYC Session ID: " . $kycSessionId;
        } else {
            echo "Failed to generate KYC Session ID.";
        }
    }

    public function createDid() {


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
            "Authorization: Bearer ".Session::get('access_token'), // Replace with your authorization token
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
            echo "DID Document: ";
            echo "<pre>";
            print_r($didDocument);
        } else {
            echo "Error in response: ";
            print_r($responseData);
        }

    }


    public function registerDid() {

        $url = 'https://ent-7adde2b.api.entity.hypersign.id/api/v1/did/register';
        $authorization = 'Bearer '.Session::get('access_token');
        $data = [
            "didDocument" => [
                "@context" => ["https://www.w3.org/ns/did/v1"],
                "id" => "did:hid:testnet:f42a2ce5-6e1d-45a3-bdfd-2bbf88a0a6de",
                "controller" => ["did:hid:testnet:f42a2ce5-6e1d-45a3-bdfd-2bbf88a0a6de"],

                
                "alsoKnownAs" => ["did:hid:testnet:f42a2ce5-6e1d-45a3-bdfd-2bbf88a0a6de"],
                "verificationMethod" => [
                    [
                        "id" => "did:hid:testnet:f42a2ce5-6e1d-45a3-bdfd-2bbf88a0a6de#key-1",
                        "type" => "Ed25519VerificationKey2020",
                        "controller" => "did:hid:testnet:f42a2ce5-6e1d-45a3-bdfd-2bbf88a0a6de",
                        "publicKeyMultibase" => "z6Mkq47311foN9ep4uzzFcSoWuh1Xak98sbjwvBySPf7aTUA",
                    ]
                ],
                "authentication" => ["did:hid:testnet:f42a2ce5-6e1d-45a3-bdfd-2bbf88a0a6de#key-1"],
                "assertionMethod" => ["did:hid:testnet:f42a2ce5-6e1d-45a3-bdfd-2bbf88a0a6de#key-1"],
                "keyAgreement" => [],
                // "keyAgreement" => ["did:hid:method:......"],
                "capabilityInvocation" => ["did:hid:testnet:f42a2ce5-6e1d-45a3-bdfd-2bbf88a0a6de#key-1"],
                "capabilityDelegation" => ["did:hid:testnet:f42a2ce5-6e1d-45a3-bdfd-2bbf88a0a6de#key-1"]
            ],
            "verificationMethodId" => "did:hid:testnet:f42a2ce5-6e1d-45a3-bdfd-2bbf88a0a6de#key-1"
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: ' . $authorization,
            'Origin: http://demo.seqrdoc.com',
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }

        curl_close($ch);

        echo "<pre>";
        echo $response;

    }


    public function createSchema() {

        $url = 'https://ent-7adde2b.api.entity.hypersign.id/api/v1/schema'; // Update this to the full API URL
        $authorization = 'Bearer '.Session::get('access_token'); // Replace with your actual JWT token

        $data = [
            "schema" => [
                "name" => "UniversityCertificatenew",
                "author" => "did:hid:testnet:da9887d3-56ef-4737-a14b-63ff72d47529", // Replace with actual DID
                "description" => "University Certificate new",
                "additionalProperties" => false,
                "fields" => [
                    [
                        "name" => "name",
                        "format" => "",
                        "type" => "string",
                        "isRequired" => false
                    ]
                ]
            ],
            "namespace" => "testnet",
            "verificationMethodId" => "did:hid:testnet:da9887d3-56ef-4737-a14b-63ff72d47529#key-1" // Replace ${idx} with the actual key index
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: ' . $authorization,
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

        echo $response;

    }


    public function getSchema() {   

        
        $schemaId = 'sch:hid:testnet:z6MkroafYPx8TgkG53aTCSfYpnbhLxewjLK1YfCJwe5FuznV:1.0'; // Replace with the actual schemaId
        $url = "https://ent-7adde2b.api.entity.hypersign.id/api/v1/schema/$schemaId"; // Update with the full API URL
        $authorization = 'Bearer '.Session::get('access_token'); // Replace with your actual JWT token

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: ' . $authorization,
            'Origin: http://demo.seqrdoc.com'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'Error: ' . curl_error($ch);
        }

        curl_close($ch);

        echo $response;

    }

    public function credientialIssue() {

        $url = 'https://ent-7adde2b.api.entity.hypersign.id/api/v1/credential/issue'; // Update with the full API URL
        $authorization = 'Bearer '.Session::get('access_token'); // Replace with your actual JWT token

        $data = [
            "schemaId" => "sch:hid:testnet:z6MknbgJGRTa6DHviYKpArGyUN2dmz5b57piXgXUnpXSzEwA:1.0", // Replace with the actual schema ID
            "subjectDid" => "did:hid:testnet:f42a2ce5-6e1d-45a3-bdfd-2bbf88a0a6de", // Replace with the actual Subject DID
            "issuerDid" => "did:hid:testnet:f42a2ce5-6e1d-45a3-bdfd-2bbf88a0a6de", // Replace with the actual Issuer DID
            "schemaContext" => [
                "https://schema.org"
            ],
            "type" => [
                "StudentCredential"
            ],
            "expirationDate" => "2027-12-10T18:30:00.000Z", // Expiration date in ISO 8601 format
            "fields" => [
                "name" => "Rohit Bachkar"
            ],
            "namespace" => "testnet",
            "verificationMethodId" => "did:hid:testnet:f42a2ce5-6e1d-45a3-bdfd-2bbf88a0a6de#key-1", // Replace ${idx} with the actual key index
            "persist" => true,
            "registerCredentialStatus" => true
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: ' . $authorization,
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

        echo $response;

    }

    
}
