<?php
namespace App\Http\Controllers\Purchase;

use App\Http\Controllers\Controller;
use App\Http\Controllers\MailController;
use Illuminate\Support\Facades\DB;

class  ApiSending extends Controller {

    public static function AdexApi($data, $sending_data){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $data['website_url']."/api/user/");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt(
           $ch, CURLOPT_HTTPHEADER, [
                "Authorization: Basic ".$data['accessToken']."",
            ]
        );
        $json = curl_exec($ch);
        curl_close($ch);
        $decode_adex = (json_decode($json,true));
        if(!empty($decode_adex)){
            if(isset($decode_adex['AccessToken'])){
                $access_token = $decode_adex['AccessToken'];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $data['endpoint']);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($sending_data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $headers = [
                 "Authorization: Token $access_token",
                'Content-Type: application/json'
            ];
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $dataapi = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            return json_decode($dataapi,true);

        }else{
            return ['status' => 'fail'];
        }
        }else{
            return ['status' => 'fail'];
        }
    }

    public static function MSORGAPI($endpoint, $data){

         $ch = curl_init();
         curl_setopt($ch, CURLOPT_URL, $endpoint['endpoint']);
         curl_setopt($ch, CURLOPT_POST, 1);
         curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
         $headers = [
              "Authorization: Token ".$endpoint['token'],
             'Content-Type: application/json'
         ];
         curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
         $dataapi = curl_exec($ch);
         $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
         curl_close($ch);


            return json_decode($dataapi,true);

    }

    public static function VIRUSAPI($endpoint, $data){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint['endpoint']);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $dataapi = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if($httpcode == 200 || $httpcode == 201){
                // file_put_contents('status.txt', $httpcode);
                // file_put_contents('message.txt', $dataapi);
           return json_decode($dataapi,true);
            }else{
               return ['status' => 'fail'];
            }
    }

    public static function OTHERAPI($endpoint, $payload, $headers){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
       if(isset($headers)){
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
       }
        $dataapi = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        file_put_contents('status.txt', $httpcode);
                file_put_contents('message.txt', $dataapi);

           return json_decode($dataapi,true);

    }
public static function ADMINEMAIL($data){
if (DB::table('user')->where(['status' => 1, 'type' => 'ADMIN'])->count() !=  0){
  $all_admin = DB::table('user')->where(['status' => 1, 'type' => 'ADMIN'])->get();
  $sets = DB::table('general')->first();
  foreach($all_admin as $adex){
  $email_data = [
    'email' => $adex->email,
    'username' => $adex->username,
    'title' => $data['title'],
    'sender_mail' => $sets->app_email,
    'app_name' => $sets->app_name,
    'mes' => $data['mes']
  ];
    MailController::send_mail($email_data,'email.purchase');
    return ['status' => 'success'];
  }
}else{
    return ['status' => 'fail'];
}
}
}
