<?php

namespace App\Http\Controllers\Purchase;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class  ExamSend extends Controller
{
    public static function Adex1($data)
    {
        if (DB::table('exam')->where(['username' => $data['username'], 'transid' => $data['transid']])->count() == 1) {
            $sendRequest = DB::table('exam')->where(['username' => $data['username'], 'transid' => $data['transid']])->first();
            $api_website = DB::table('web_api')->first();
            $adex_api = DB::table('adex_api')->first();
            $exam_id = DB::table('exam_id')->where('exam_name', $sendRequest->exam_name)->first();
            $accessToken = base64_encode($adex_api->adex1_username . ":" . $adex_api->adex1_password);
            $paypload = array(
                'exam' => $exam_id->plan_id,
                'quantity' => $sendRequest->quantity
            );
            $admin_details = [
                'website_url' => $api_website->adex_website1,
                'endpoint' => $api_website->adex_website1 . "/api/exam/",
                'accessToken' => $accessToken
            ];
            $response = ApiSending::AdexApi($admin_details, $paypload);
            if (!empty($response)) {
                if ($response['status'] == 'success') {
                    $pin = $response['pin'];
                    DB::table('exam')->where(['username' => $data['username'], 'transid' => $data['transid']])->update(['purchase_code' => $pin]);
                    $plan_status = 'success';
                } else if ($response['status'] == 'fail') {
                    $plan_status = 'fail';
                } else if ($response['status'] == 'process') {
                    $plan_status = 'process';
                } else {
                    $plan_status = 'process';
                }
            } else {
                $plan_status = null;
            }
            return $plan_status;
        } else {
            return 'fail';
        }
    }
    public static function Adex2($data)
    {
        if (DB::table('exam')->where(['username' => $data['username'], 'transid' => $data['transid']])->count() == 1) {
            $sendRequest = DB::table('exam')->where(['username' => $data['username'], 'transid' => $data['transid']])->first();
            $api_website = DB::table('web_api')->first();
            $adex_api = DB::table('adex_api')->first();
            $exam_id = DB::table('exam_id')->where('exam_name', $sendRequest->exam_name)->first();
            $accessToken = base64_encode($adex_api->adex2_username . ":" . $adex_api->adex2_password);
            $paypload = array(
                'exam' => $exam_id->plan_id,
                'quantity' => $sendRequest->quantity
            );
            $admin_details = [
                'website_url' => $api_website->adex_website2,
                'endpoint' => $api_website->adex_website2 . "/api/exam/",
                'accessToken' => $accessToken
            ];
            $response = ApiSending::AdexApi($admin_details, $paypload);
            if (!empty($response)) {
                if ($response['status'] == 'success') {
                    $pin = $response['pin'];
                    DB::table('exam')->where(['username' => $data['username'], 'transid' => $data['transid']])->update(['purchase_code' => $pin]);
                    $plan_status = 'success';
                } else if ($response['status'] == 'fail') {
                    $plan_status = 'fail';
                } else if ($response['status'] == 'process') {
                    $plan_status = 'process';
                } else {
                    $plan_status = 'process';
                }
            } else {
                $plan_status = null;
            }
            return $plan_status;
        } else {
            return 'fail';
        }
    }
    public static function Adex3($data)
    {
        if (DB::table('exam')->where(['username' => $data['username'], 'transid' => $data['transid']])->count() == 1) {
            $sendRequest = DB::table('exam')->where(['username' => $data['username'], 'transid' => $data['transid']])->first();
            $api_website = DB::table('web_api')->first();
            $adex_api = DB::table('adex_api')->first();
            $exam_id = DB::table('exam_id')->where('exam_name', $sendRequest->exam_name)->first();
            $accessToken = base64_encode($adex_api->adex3_username . ":" . $adex_api->adex3_password);
            $paypload = array(
                'exam' => $exam_id->plan_id,
                'quantity' => $sendRequest->quantity
            );
            $admin_details = [
                'website_url' => $api_website->adex_website3,
                'endpoint' => $api_website->adex_website3 . "/api/exam/",
                'accessToken' => $accessToken
            ];
            $response = ApiSending::AdexApi($admin_details, $paypload);
            if (!empty($response)) {
                if ($response['status'] == 'success') {
                    $pin = $response['pin'];
                    DB::table('exam')->where(['username' => $data['username'], 'transid' => $data['transid']])->update(['purchase_code' => $pin]);
                    $plan_status = 'success';
                } else if ($response['status'] == 'fail') {
                    $plan_status = 'fail';
                } else if ($response['status'] == 'process') {
                    $plan_status = 'process';
                } else {
                    $plan_status = 'process';
                }
            } else {
                $plan_status = null;
            }
            return $plan_status;
        } else {
            return 'fail';
        }
    }
    public static function Adex4($data)
    {
        if (DB::table('exam')->where(['username' => $data['username'], 'transid' => $data['transid']])->count() == 1) {
            $sendRequest = DB::table('exam')->where(['username' => $data['username'], 'transid' => $data['transid']])->first();
            $api_website = DB::table('web_api')->first();
            $adex_api = DB::table('adex_api')->first();
            $exam_id = DB::table('exam_id')->where('exam_name', $sendRequest->exam_name)->first();
            $accessToken = base64_encode($adex_api->adex4_username . ":" . $adex_api->adex4_password);
            $paypload = array(
                'exam' => $exam_id->plan_id,
                'quantity' => $sendRequest->quantity
            );
            $admin_details = [
                'website_url' => $api_website->adex_website4,
                'endpoint' => $api_website->adex_website4 . "/api/exam/",
                'accessToken' => $accessToken
            ];
            $response = ApiSending::AdexApi($admin_details, $paypload);
            if (!empty($response)) {
                if ($response['status'] == 'success') {
                    $pin = $response['pin'];
                    DB::table('exam')->where(['username' => $data['username'], 'transid' => $data['transid']])->update(['purchase_code' => $pin]);
                    $plan_status = 'success';
                } else if ($response['status'] == 'fail') {
                    $plan_status = 'fail';
                } else if ($response['status'] == 'process') {
                    $plan_status = 'process';
                } else {
                    $plan_status = 'process';
                }
            } else {
                $plan_status = null;
            }
            return $plan_status;
        } else {
            return 'fail';
        }
    }
    public static function Adex5($data)
    {
        if (DB::table('exam')->where(['username' => $data['username'], 'transid' => $data['transid']])->count() == 1) {
            $sendRequest = DB::table('exam')->where(['username' => $data['username'], 'transid' => $data['transid']])->first();
            $api_website = DB::table('web_api')->first();
            $adex_api = DB::table('adex_api')->first();
            $exam_id = DB::table('exam_id')->where('exam_name', $sendRequest->exam_name)->first();
            $accessToken = base64_encode($adex_api->adex5_username . ":" . $adex_api->adex5_password);
            $paypload = array(
                'exam' => $exam_id->plan_id,
                'quantity' => $sendRequest->quantity
            );
            $admin_details = [
                'website_url' => $api_website->adex_website5,
                'endpoint' => $api_website->adex_website5 . "/api/exam/",
                'accessToken' => $accessToken
            ];
            $response = ApiSending::AdexApi($admin_details, $paypload);
            if (!empty($response)) {
                if ($response['status'] == 'success') {
                    $pin = $response['pin'];
                    DB::table('exam')->where(['username' => $data['username'], 'transid' => $data['transid']])->update(['purchase_code' => $pin]);
                    $plan_status = 'success';
                } else if ($response['status'] == 'fail') {
                    $plan_status = 'fail';
                } else if ($response['status'] == 'process') {
                    $plan_status = 'process';
                } else {
                    $plan_status = 'process';
                }
            } else {
                $plan_status = null;
            }
            return $plan_status;
        } else {
            return 'fail';
        }
    }


    public static function Easy($data)
    {
        if (DB::table('exam')->where(['username' => $data['username'], 'transid' => $data['transid']])->count() == 1) {
            $sendRequest = DB::table('exam')->where(['username' => $data['username'], 'transid' => $data['transid']])->first();
            $adex_api = DB::table('other_api')->first();
            $paypload = array(
                'no_of_pins' => $sendRequest->quantity,
            );
            if ($sendRequest->exam_name == 'WAEC') {
                $endpoints = "https://easyaccessapi.com.ng/api/waec_v2.php";
            } else if ($sendRequest->exam_name == 'NECO') {
                $endpoints = "https://easyaccessapi.com.ng/api/neco_v2.php";
            } else if ($sendRequest->exam_name == 'NABTEB') {
                $endpoints = "https://easyaccessapi.com.ng/api/nabteb_v2.php";
            }
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $endpoints,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $paypload,
                CURLOPT_HTTPHEADER => array(
                    "AuthorizationToken: " . $adex_api->easy_access,
                    "cache-control: no-cache"
                ),
            ));
            $dataapi = curl_exec($curl);
            $response = json_decode($dataapi, true);
            if ($response) {
                if (isset($response['status'])) {
                    if ($response['status'] == 'true') {
                        if ($sendRequest->quantity == 1) {
                            $pin = $response['pin'];
                        } else if ($sendRequest->quantity == 2) {
                            $pin = "pin1 => " . $response['pin'] . " pin2 => " . $response['pin2'];
                        } else if ($sendRequest->quantity == 3) {
                            $pin = "pin1 => " . $response['pin'] . " pin2 => " . $response['pin2'] . " pin3 => " . $response['pin3'];
                        } else if ($sendRequest->quantity == 4) {
                            $pin = "pin1 => " . $response['pin'] . " pin2 => " . $response['pin2'] . " pin3 => " . $response['pin3'] . " pin4 => " . $response['pin4'];
                        } else if ($sendRequest->quantity == 5) {
                            $pin = "pin1 => " . $response['pin'] . " pin2 => " . $response['pin2'] . " pin3 => " . $response['pin3'] . " pin4 => " . $response['pin4'] . " pin5 => " . $response['pin5'];
                        } else {
                            $pin = "pin1 => " . $response['pin'] . " pin2 => " . $response['pin2'] . " pin3 => " . $response['pin3'] . " pin4 => " . $response['pin4'] . " pin5 => " . $response['pin5'];
                        }
                        DB::table('exam')->where(['username' => $data['username'], 'transid' => $data['transid']])->update(['purchase_code' => $pin]);
                    } else {
                        return 'fail';
                    }
                } else {
                    return 'fail';
                }
            } else {
                return 'fail';
            }
        } else {
            return 'fail';
        }
    }
    public  function Vtpass($data)
    {
        if (DB::table('exam')->where(['username' => $data['username'], 'transid' => $data['transid']])->count() == 1) {
            $sendRequest = DB::table('exam')->where(['username' => $data['username'], 'transid' => $data['transid']])->first();
            $other_api = DB::table('other_api')->first();
            $paypload = array(
                'serviceID' => 'waec',
                'phone' => $this->core()->app_phone,
                'variation_code' => 'waecdirect',
                'request_id' => Carbon::parse($this->system_date())->formatLocalized("%Y%m%d%H%M%S") . '_' . $data['transid']
            );
            $endpoints = "https://vtpass.com/api/pay";
            $headers =  [
                "Authorization: Basic " . base64_encode($other_api->vtpass_username . ":" . $other_api->vtpass_password),
                'Content-Type: application/json'
            ];
            $response = ApiSending::OTHERAPI($endpoints, $paypload, $headers);
            // declare plan status
            if (!empty($response)) {
                if (isset($response['code'])) {
                    if ($response['code'] == 000) {
                        if ((isset($response['purchased_code'])) && !empty($response['purchased_code'])) {
                            DB::table('bill')->where(['username' => $sendRequest->username, 'transid' => $sendRequest->transid])->update(['token' => $response['purchased_code']]);
                        }
                        $plan_status = 'success';
                    } else if ($response['response_description'] !=  'TRANSACTION SUCCESSFUL') {
                        $plan_status = 'fail';
                    } else {
                        $plan_status = 'process';
                    }
                } else {
                    $plan_status = null;
                }
            } else {
                $plan_status = null;
            }

            return $plan_status;
        } else {
            return 'fail';
        }
    }
    public static function Self($data)
    {
        if (DB::table('exam')->where(['username' => $data['username'], 'transid' => $data['transid']])->count() == 1) {
            $sendRequest = DB::table('exam')->where(['username' => $data['username'], 'transid' => $data['transid']])->first();
            if (DB::table('stock_result_pin')->where(['exam_name' => $sendRequest->exam_name, 'plan_status' => 0])->take($sendRequest->quantity)->count() >= $sendRequest->quantity) {
                $adex_pin = DB::table('stock_result_pin')->where(['exam_name' => $sendRequest->exam_name, 'plan_status' => 0])->take($sendRequest->quantity)->get();
                $result_pin[] = null;
                foreach ($adex_pin as $boss) {
                    $pin = DB::table('stock_result_pin')->where(['id' => $boss->id])->first();

                    $result_pin[] = $pin->exam_pin . "<=>" . $pin->exam_serial;


                    DB::table('stock_result_pin')->where(['id' => $boss->id])->update(['plan_status' => 1, 'buyer_username' => $sendRequest->username, 'bought_date' => $sendRequest->plan_date]);
                }
                $my_pin = implode(' ', $result_pin);

                DB::table('exam')->where(['username' => $sendRequest->username, 'transid' => $sendRequest->transid])->update(['purchase_code' => $my_pin]);

                return 'success';
            } else {
                return 'fail';
            }
        } else {
            return 'fail';
        }
    }
}
