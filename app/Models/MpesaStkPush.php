<?php

namespace App\Models;

use Carbon\Carbon;
use http\Env\Request;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;



class MpesaStkpush
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
    protected $callbackurl;


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
        $this->callbackurl=env('CALLBACKURL');

    }

    public function registerMpesaEndpoints(){
        $validationURL=null;
        $response = Daraja::getInstance()
            ->setCredentials($this->consumer_key,$this->consumer_secret,$this->env)
            ->registerCallbacks($this->short_code, $this->confirmationUrl, $validationURL,'Completed');

    }

    /** Lipa na M-PESA password **/
    public function getPassword()
    {
        $timestamp = Carbon::now()->format('YmdHms');
        $password = base64_encode($this->short_code . "" . $this->passkey . "" . $timestamp);

        return $password;
    }




    public function lipaNaMpesa($amount, $phone, $accountReference)
    {
        $this->phone = $phone;
        $this->amount = $amount;
        $this->accountReference = $accountReference;





        $headers = ['Content-Type:application/json; charset=utf8'];

        $access_token_url = "https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials";
        $initiate_url ="https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest";


        $curl = curl_init($access_token_url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_HEADER, FALSE);
        curl_setopt($curl, CURLOPT_USERPWD, $this->consumer_key . ':' . $this->consumer_secret);
        $result = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $result = json_decode($result);
        $access_token = $result->access_token;
        curl_close($curl);



        $timestamp = date("Ymdhis");



        $password=base64_encode($this->short_code.$this->passkey.$timestamp);

        $timestamp= date(    "Ymdhis");
        $password=base64_encode($this->short_code.$this->passkey.$timestamp);


//        return $password;
        //return dd($timestamp);

       // return dd($timestamp);
        # header for stk push
        $stkheader = ['Content-Type:application/json', 'Authorization:Bearer ' . $access_token];
        # initiating the transaction
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $initiate_url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $stkheader); //setting custom header



//        \Savannabits\Daraja\Daraja::getInstance()
//            ->setCredentials("CONSUMER KEY","CONSUMER SECRET","sandbox")
//            ->STKPushSimulation($BusinessShortCode, $LipaNaMpesaPasskey, $TransactionType, $Amount, $PartyA, $PartyB, $PhoneNumber, $CallBackURL, $AccountReference, $TransactionDesc, $Remark);
//

        $curl_post_data = array(
            'BusinessShortCode'=> $this->short_code,
            'Password'=>$password,
            'Timestamp'=>$timestamp,
            'TransactionType'=> 'CustomerPayBillOnline',
            'Amount'=> 1,
            'PartyA'=> $phone,
            'PartyB'=> $this->short_code,
            'PhoneNumber'=> $phone,
            'CallBackURL'=> $this->callbackurl,
            'AccountReference'=> 'FAO',
            'TransactionDesc'=> 'Payment of X'
        );

       // return dd($curl_post_data);


              $data_string = json_encode($curl_post_data);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        $response = curl_exec($curl);

        return $response;
    }

    public function status($transactionCode)
    {
        $type = 4;
        $command = "TransactionStatusQuery";
        $remarks = "Transaction Status Query";
        $occasion = "Transaction Status Query";
        $results_url = "https://mydomain.com/TransactionStatus/result/"; //Endpoint to receive results Body
        $timeout_url = "https://mydomain.com/TransactionStatus/queue/"; //Endpoint to to go to on timeout

        $access_token = ($this->env == "live") ? "https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials" : "https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials";
        $credentials = base64_encode($this->consumer_key . ':' . $this->consumer_secret);

        $ch = curl_init($access_token);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Basic " . $credentials]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response);

        //echo $result->{'access_token'};

        $token = isset($result->{'access_token'}) ? $result->{'access_token'} : "N/A";

        $publicKey = file_get_contents(__DIR__ . "/mpesa_public_cert.cer");
        $isvalid = openssl_public_encrypt($this->initiatorPassword, $encrypted, $publicKey, OPENSSL_PKCS1_PADDING);
        $password = base64_encode($encrypted);

        //echo $token;

        $curl_post_data = array(
            "Initiator" => $this->initiatorName,
            "SecurityCredential" => $password,
            "CommandID" => $command,
            "TransactionID" => $transactionCode,
            "PartyA" => $this->short_code,
            "IdentifierType" => $type,
            "ResultURL" => $results_url,
            "QueueTimeOutURL" => $timeout_url,
            "Remarks" => $remarks,
            "Occasion" => $occasion,
        );

        $data_string = json_encode($curl_post_data);

        //echo $data_string;

        $endpoint = "https://sandbox.safaricom.co.ke/mpesa/transactionstatus/v1/query";

        $ch2 = curl_init($endpoint);
        curl_setopt($ch2, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch2, CURLOPT_POST, 1);
        curl_setopt($ch2, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch2);
        curl_close($ch2);

        //echo "Authorization: ". $response;

        $result = json_decode($response);

        return $result;

    }

}
