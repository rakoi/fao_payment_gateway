<?php

namespace App\Http\Controllers;

use App\Models\MpesaStkPush;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Savannabits\Daraja\Daraja;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;


class MpesaController extends Controller
{

    protected $consumer_key;
    protected $consumer_secret;
    protected $passkey;
    protected $amount;
    protected $accountReference;
    protected $phone;
    protected $env;
    protected $short_code;
    protected $parent_short_code;
    protected $initiatorName;
    protected $initiatorPassword;
    protected $confirmationUrl;


    public function __construct()
    {

        date_default_timezone_set('Africa/Nairobi');
        $this->short_code = env('SHORTCODE');
        $this->parent_short_code = env('PARENTSHORTCODE');
        $this->consumer_key = env('CONSUMERKEY'); //Your Consumer key
        $this->consumer_secret = env('CONSUMERSECRET'); //Your Secret key
        ///$this->passkey = " "; //Your Passkey
        $this->passkey=env('PASSKEY');
        $this->CallBackURL =env('CALLBACKURL');//Your callback URL
        $this->env = env('MPESAENV');
        $this->initiatorName =env('INITIATORNAME');
        $this->initiatorPassword =env('INITIATERPASSWORD');
        $this->confirmationUrl=env('CONFIRMATIONURL');


    }

    public function sayHello(){
        return "hello";
    }

    public function consumeJson(Request $request)
    {
        // Get the JSON data from the request
        $jsonData = $request->json()->all();

        // Access individual fields from the JSON data
        $transactionType = $jsonData['CustomerPayBillOnline'];
        $transID = $jsonData['TransID'];
        $transTime = $jsonData['TransTime'];
        $transAmount = $jsonData['TransAmount'];
        $refrence= $jsonData['BillRefNumber'];

        //POST AMOUNT AND REFRENCE/STUDENT NUMBER


        $endpointUrl =env('stkpush');;


        // Initialize cURL session
        $ch = curl_init($endpointUrl);

        // Set cURL options
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Execute cURL session and get the response
        $response = curl_exec($ch);



        // Example: Return a response
        return response()->json(['status' => 'success', 'message' => 'JSON data received successfully']);
    }


    //INITIATE STK PUSH USING DARAJA
    public function savanabitsStkPush(){
        return \Savannabits\Daraja\Daraja::getInstance()
            ->setCredentials($this->consumer_key,$this->consumer_secret,$this->env)
            ->STKPushSimulation( $this->short_code,$this->passkey, "CustomerPayBillOnline", "1", "254702164611",  $this->short_code, "254702164611", $this->CallBackURL, "FAO", "PAY SU FAO", "FAO LOAN");
    }
    public function registercallbacks(){


        $validationURL=env('VALIDATIONURL');

        $response = \Savannabits\Daraja\Daraja::getInstance()
            ->setCredentials($this->consumer_key,$this->consumer_secret,$this->env)
            ->registerCallbacks($this->short_code, $this->confirmationUrl,$validationURL,"Completed");

        return $response;
    }

    public function simulatePaybill(){

        $commandID = "CustomerPayBillOnline";
        $amount = 100;
        $msisdn = "254708374149"; // See safaricom daraja documentation and check your credentials for the specific number given for testing.
        $billRefNumber = "135444"; // e.g "MAMA MBOGA 212"
        $response = Daraja::getInstance()
            ->setCredentials($this->consumer_key,$this->consumer_secret,$this->env)
            ->c2b($this->short_code, $commandID, 1, $msisdn, $billRefNumber);

        return $response;
    }

    public function valtrans()
    {
        $response = [
            'ResultCode' => '0',
            'ResultDesc' => 'Accepted',
        ];

        return response()->json($response);
    }




     //Initiate STK Push
     public function stkPushRequest(){

        $accountReference='Transaction#'.Str::random(10);

        $amount= 1;
        $phone=$this->formatPhone(702164611);


        $mpesa=new MpesaStkpush();
        $stk=$mpesa->lipaNaMpesa(1,$phone,$accountReference);
        $invalid=json_decode($stk);

        if(@$invalid->errorCode){
            return $invalid;

            return "ERROR";
        }

        return $stk;
    }
    public function formatPhone($phone)
    {
        $phone = 'hfhsgdgs' . $phone;
        $phone = str_replace('hfhsgdgs0', '', $phone);
        $phone = str_replace('hfhsgdgs', '', $phone);
        $phone = str_replace('+', '', $phone);
        if (strlen($phone) == 9) {
            $phone = '254' . $phone;
        }
        return $phone;
    }

    public function mpesaResponse(Request  $request){
        return $request;
    }
    public function confirmpay(Request $request){

        // Get the values from the request
        $transactionType = $request->input('TransactionType');
        $transID = $request->input('TransID');
        $transTime = $request->input('TransTime');
        $transAmount = $request->input('TransAmount');
        $businessShortCode = $request->input('BusinessShortCode');
        $billRefNumber = $request->input('BillRefNumber');
        $invoiceNumber = $request->input('InvoiceNumber');
        $orgAccountBalance = $request->input('OrgAccountBalance');
        $thirdPartyTransID = $request->input('ThirdPartyTransID');
        $msisdn = $request->input('MSISDN');
        $firstName = $request->input('FirstName');
        $middleName = $request->input('MiddleName');
        $lastName = $request->input('LastName');


        //post to fao amount mpesa refrenceno and student number

        $url=env("FAO_ENDPOINT");

        $params = [
            'TransAmount' => $transAmount,
            'Refrence' => $billRefNumber,
            'TransCode' => $transID,
        ];





        $client = new Client();

        try {
            $response = $client->request('GET', $url, [
                'query' => $params,
                'verify' => false, // This is equivalent to CURLOPT_SSL_VERIFYPEER in cURL, but use it cautiously
            ]);

            $statusCode = $response->getStatusCode();
            $body = $response->getBody()->getContents();

            // Do something with the response
            return response()->json(['response' => $body], $statusCode);
        } catch (GuzzleHttp\Exception\RequestException $e) {
            // Handle Guzzle request errors
            return response()->json(['error' => 'Guzzle Request Error: ' . $e->getMessage()], 500);
        }
    }
}
