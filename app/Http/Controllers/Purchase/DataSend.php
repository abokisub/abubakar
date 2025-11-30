<?php

namespace App\Http\Controllers\Purchase;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class  DataSend extends Controller
{
    public static function Adex1($data)
    {
        if (DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->count() == 1) {
            $sendRequest = DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->first();
            $network = DB::table('network')->where(['network' => $sendRequest->network])->first();
            $dataplan = DB::table('data_plan')->where(['plan_id' => $data['purchase_plan']])->first();
            $api_website = DB::table('web_api')->first();
            $adex_api = DB::table('adex_api')->first();
            $accessToken = base64_encode($adex_api->adex1_username . ":" . $adex_api->adex1_password);
            $paypload = array(
                'network' => $network->adex_id,
                'phone' => $sendRequest->plan_phone,
                'data_plan' => $dataplan->adex1,
                'bypass' => true,
                'request-id' => $data['transid']
            );
            $admin_details = [
                'website_url' => $api_website->adex_website1,
                'endpoint' => $api_website->adex_website1 . "/api/data/",
                'accessToken' => $accessToken
            ];
            $response = ApiSending::AdexApi($admin_details, $paypload);
            if (!empty($response)) {
                if ($response['status'] == 'success') {
                    if (isset($response['response'])) {
                        DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->update(['api_response' => $response['response']]);
                    }
                    $plan_status = 'success';
                } else if ($response['status'] == 'fail') {
                    if (isset($response['response'])) {
                        DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->update(['api_response' => $response['response']]);
                    }
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
        if (DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->count() == 1) {
            $sendRequest = DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->first();
            $network = DB::table('network')->where(['network' => $sendRequest->network])->first();
            $dataplan = DB::table('data_plan')->where(['plan_id' => $data['purchase_plan']])->first();
            $api_website = DB::table('web_api')->first();
            $adex_api = DB::table('adex_api')->first();
            $accessToken = base64_encode($adex_api->adex2_username . ":" . $adex_api->adex2_password);
            $paypload = array(
                'network' => $network->adex_id,
                'phone' => $sendRequest->plan_phone,
                'data_plan' => $dataplan->adex2,
                'bypass' => true,
                'request-id' => $data['transid']
            );
            $admin_details = [
                'website_url' => $api_website->adex_website2,
                'endpoint' => $api_website->adex_website2 . "/api/data/",
                'accessToken' => $accessToken
            ];
            $response = ApiSending::AdexApi($admin_details, $paypload);
            if (!empty($response)) {
                if ($response['status'] == 'success') {
                    if (isset($response['response'])) {
                        DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->update(['api_response' => $response['response']]);
                    }
                    $plan_status = 'success';
                } else if ($response['status'] == 'fail') {
                    if (isset($response['response'])) {
                        DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->update(['api_response' => $response['response']]);
                    }
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
        if (DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->count() == 1) {
            $sendRequest = DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->first();
            $network = DB::table('network')->where(['network' => $sendRequest->network])->first();
            $dataplan = DB::table('data_plan')->where(['plan_id' => $data['purchase_plan']])->first();
            $api_website = DB::table('web_api')->first();
            $adex_api = DB::table('adex_api')->first();
            $accessToken = base64_encode($adex_api->adex3_username . ":" . $adex_api->adex3_password);
            $paypload = array(
                'network' => $network->adex_id,
                'phone' => $sendRequest->plan_phone,
                'data_plan' => $dataplan->adex3,
                'bypass' => true,
                'request-id' => $data['transid']
            );
            $admin_details = [
                'website_url' => $api_website->adex_website3,
                'endpoint' => $api_website->adex_website3 . "/api/data/",
                'accessToken' => $accessToken
            ];
            $response = ApiSending::AdexApi($admin_details, $paypload);
            if (!empty($response)) {
                if ($response['status'] == 'success') {
                    if (isset($response['response'])) {
                        DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->update(['api_response' => $response['response']]);
                    }
                    $plan_status = 'success';
                } else if ($response['status'] == 'fail') {
                    if (isset($response['response'])) {
                        DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->update(['api_response' => $response['response']]);
                    }
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
        if (DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->count() == 1) {
            $sendRequest = DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->first();
            $network = DB::table('network')->where(['network' => $sendRequest->network])->first();
            $dataplan = DB::table('data_plan')->where(['plan_id' => $data['purchase_plan']])->first();
            $api_website = DB::table('web_api')->first();
            $adex_api = DB::table('adex_api')->first();
            $accessToken = base64_encode($adex_api->adex4_username . ":" . $adex_api->adex4_password);
            $paypload = array(
                'network' => $network->adex_id,
                'phone' => $sendRequest->plan_phone,
                'data_plan' => $dataplan->adex4,
                'bypass' => true,
                'request-id' => $data['transid']
            );
            $admin_details = [
                'website_url' => $api_website->adex_website4,
                'endpoint' => $api_website->adex_website4 . "/api/data/",
                'accessToken' => $accessToken
            ];
            $response = ApiSending::AdexApi($admin_details, $paypload);
            if (!empty($response)) {
                if ($response['status'] == 'success') {
                    if (isset($response['response'])) {
                        DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->update(['api_response' => $response['response']]);
                    }
                    $plan_status = 'success';
                } else if ($response['status'] == 'fail') {
                    if (isset($response['response'])) {
                        DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->update(['api_response' => $response['response']]);
                    }
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
        if (DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->count() == 1) {
            $sendRequest = DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->first();
            $network = DB::table('network')->where(['network' => $sendRequest->network])->first();
            $dataplan = DB::table('data_plan')->where(['plan_id' => $data['purchase_plan']])->first();
            $api_website = DB::table('web_api')->first();
            $adex_api = DB::table('adex_api')->first();
            $accessToken = base64_encode($adex_api->adex5_username . ":" . $adex_api->adex5_password);
            $paypload = array(
                'network' => $network->adex_id,
                'phone' => $sendRequest->plan_phone,
                'data_plan' => $dataplan->adex5,
                'bypass' => true,
                'request-id' => $data['transid']
            );
            $admin_details = [
                'website_url' => $api_website->adex_website5,
                'endpoint' => $api_website->adex_website5 . "/api/data/",
                'accessToken' => $accessToken
            ];
            $response = ApiSending::AdexApi($admin_details, $paypload);
            if (!empty($response)) {
                if ($response['status'] == 'success') {
                    if (isset($response['response'])) {
                        DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->update(['api_response' => $response['response']]);
                    }
                    $plan_status = 'success';
                } else if ($response['status'] == 'fail') {
                    if (isset($response['response'])) {
                        DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->update(['api_response' => $response['response']]);
                    }
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

    public static function Msorg1($data)
    {
        if (DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->count() == 1) {
            $sendRequest = DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->first();
            $network = DB::table('network')->where(['network' => $sendRequest->network])->first();
            $dataplan = DB::table('data_plan')->where(['plan_id' => $data['purchase_plan']])->first();
            $api_website = DB::table('web_api')->first();
            $msorg_api = DB::table('msorg_api')->first();
            $paypload = array(
                'network' => $network->msorg_id,
                'mobile_number' => $sendRequest->plan_phone,
                'plan' => $dataplan->msorg1,
                'Ported_number' => true,
            );
            $admin_details = [
                'endpoint' => $api_website->msorg_website1 . "/api/data/",
                'token' => $msorg_api->msorg1
            ];
            $response = ApiSending::MSORGAPI($admin_details, $paypload);
            // declare plan status
            if (!empty($response)) {
                if (isset($response['Status'])) {
                    if ($response['Status'] == 'successful' || $response['Status'] == 'processing') {
                        if (isset($response['api_response'])) {
                            DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->update(['api_response' => $response['api_response']]);
                        }
                        $plan_status = 'success';
                    } else {
                        if (isset($response['api_response'])) {
                            DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->update(['api_response' => $response['api_response']]);
                        }
                        $plan_status = 'fail';
                    }
                } else {
                    $plan_status = 'fail';
                }
            } else {
                $plan_status = null;
            }

            return $plan_status;
        } else {
            return 'fail';
        }
    }
    public static function Msorg2($data)
    {
        if (DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->count() == 1) {
            $sendRequest = DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->first();
            $network = DB::table('network')->where(['network' => $sendRequest->network])->first();
            $dataplan = DB::table('data_plan')->where(['plan_id' => $data['purchase_plan']])->first();
            $api_website = DB::table('web_api')->first();
            $msorg_api = DB::table('msorg_api')->first();
            $paypload = array(
                'network' => $network->msorg_id,
                'mobile_number' => $sendRequest->plan_phone,
                'plan' => $dataplan->msorg2,
                'Ported_number' => true,
            );
            $admin_details = [
                'endpoint' => $api_website->msorg_website2 . "/api/data/",
                'token' => $msorg_api->msorg2
            ];
            $response = ApiSending::MSORGAPI($admin_details, $paypload);
            // declare plan status
            if (!empty($response)) {
                if (isset($response['Status'])) {
                    if ($response['Status'] == 'successful' || $response['Status'] == 'processing') {
                        if (isset($response['api_response'])) {
                            DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->update(['api_response' => $response['api_response']]);
                        }
                        $plan_status = 'success';
                    } else {
                        if (isset($response['api_response'])) {
                            DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->update(['api_response' => $response['api_response']]);
                        }
                        $plan_status = 'fail';
                    }
                } else {
                    $plan_status = 'fail';
                }
            } else {
                $plan_status = null;
            }

            return $plan_status;
        } else {
            return 'fail';
        }
    }
    public static function Msorg3($data)
    {
        if (DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->count() == 1) {
            $sendRequest = DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->first();
            $network = DB::table('network')->where(['network' => $sendRequest->network])->first();
            $dataplan = DB::table('data_plan')->where(['plan_id' => $data['purchase_plan']])->first();
            $api_website = DB::table('web_api')->first();
            $msorg_api = DB::table('msorg_api')->first();
            $paypload = array(
                'network' => $network->msorg_id,
                'mobile_number' => $sendRequest->plan_phone,
                'plan' => $dataplan->msorg3,
                'Ported_number' => true,
            );
            $admin_details = [
                'endpoint' => $api_website->msorg_website3 . "/api/data/",
                'token' => $msorg_api->msorg3
            ];
            $response = ApiSending::MSORGAPI($admin_details, $paypload);
            // declare plan status
            if (!empty($response)) {
                if (isset($response['Status'])) {
                    if ($response['Status'] == 'successful' || $response['Status'] == 'processing') {
                        if (isset($response['api_response'])) {
                            DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->update(['api_response' => $response['api_response']]);
                        }
                        $plan_status = 'success';
                    } else {
                        if (isset($response['api_response'])) {
                            DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->update(['api_response' => $response['api_response']]);
                        }
                        $plan_status = 'fail';
                    }
                } else {
                    $plan_status = 'fail';
                }
            } else {
                $plan_status = null;
            }

            return $plan_status;
        } else {
            return 'fail';
        }
    }
    public static function Msorg4($data)
    {
        if (DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->count() == 1) {
            $sendRequest = DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->first();
            $network = DB::table('network')->where(['network' => $sendRequest->network])->first();
            $dataplan = DB::table('data_plan')->where(['plan_id' => $data['purchase_plan']])->first();
            $api_website = DB::table('web_api')->first();
            $msorg_api = DB::table('msorg_api')->first();
            $paypload = array(
                'network' => $network->msorg_id,
                'mobile_number' => $sendRequest->plan_phone,
                'plan' => $dataplan->msorg4,
                'Ported_number' => true,
            );
            $admin_details = [
                'endpoint' => $api_website->msorg_website4 . "/api/data/",
                'token' => $msorg_api->msorg4
            ];
            $response = ApiSending::MSORGAPI($admin_details, $paypload);
            // declare plan status
            if (!empty($response)) {
                if (isset($response['Status'])) {
                    if ($response['Status'] == 'successful' || $response['Status'] == 'processing') {
                        if (isset($response['api_response'])) {
                            DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->update(['api_response' => $response['api_response']]);
                        }
                        $plan_status = 'success';
                    } else {
                        if (isset($response['api_response'])) {
                            DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->update(['api_response' => $response['api_response']]);
                        }
                        $plan_status = 'fail';
                    }
                } else {
                    $plan_status = 'fail';
                }
            } else {
                $plan_status = null;
            }

            return $plan_status;
        } else {
            return 'fail';
        }
    }
    public static function Msorg5($data)
    {
        if (DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->count() == 1) {
            $sendRequest = DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->first();
            $network = DB::table('network')->where(['network' => $sendRequest->network])->first();
            $dataplan = DB::table('data_plan')->where(['plan_id' => $data['purchase_plan']])->first();
            $api_website = DB::table('web_api')->first();
            $msorg_api = DB::table('msorg_api')->first();
            $paypload = array(
                'network' => $network->msorg_id,
                'mobile_number' => $sendRequest->plan_phone,
                'plan' => $dataplan->msorg5,
                'Ported_number' => true,
            );
            $admin_details = [
                'endpoint' => $api_website->msorg_website5 . "/api/data/",
                'token' => $msorg_api->msorg5
            ];
            $response = ApiSending::MSORGAPI($admin_details, $paypload);
            // declare plan status
            if (!empty($response)) {
                if (isset($response['Status'])) {
                    if ($response['Status'] == 'successful' || $response['Status'] == 'processing') {
                        if (isset($response['api_response'])) {
                            DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->update(['api_response' => $response['api_response']]);
                        }
                        $plan_status = 'success';
                    } else {
                        if (isset($response['api_response'])) {
                            DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->update(['api_response' => $response['api_response']]);
                        }
                        $plan_status = 'fail';
                    }
                } else {
                    $plan_status = 'fail';
                }
            } else {
                $plan_status = null;
            }

            return $plan_status;
        } else {
            return 'fail';
        }
    }


    public static function Virus1($data)
    {
        if (DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->count() == 1) {
            $sendRequest = DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->first();
            $network = DB::table('network')->where(['network' => $sendRequest->network])->first();
            $dataplan = DB::table('data_plan')->where(['plan_id' => $data['purchase_plan']])->first();
            $api_website = DB::table('web_api')->first();
            $virus_api = DB::table('virus_api')->first();
            if ($sendRequest->network == 'MTN' and $sendRequest->network_type == 'GIFTING') {
                $virus_id = "gifting";
            } else if ($sendRequest->network == 'AIRTEL' and $sendRequest->network_type == 'COOPERATE GIFTING') {
                $virus_id = "airtel-cg";
            } else {
                $virus_id = $network->virus_id;
            }

            $paypload = array(
                'network' => $virus_id,
                'mobile' => $sendRequest->plan_phone,
                'plan_code' => $dataplan->virus1,
                'token' => $virus_api->virus1,
                'request_id' => $data['transid']
            );
            $admin_details = [
                'endpoint' => $api_website->virus_website1 . "/api/data",
            ];
            $response = ApiSending::VIRUSAPI($admin_details, $paypload);

            // declare plan status
            if (!empty($response)) {
                if (isset($response['status'])) {
                    if ($response['status'] == 'success') {
                        if (isset($response['desc'])) {
                            DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->update(['api_response' => $response['desc']]);
                        }
                        $plan_status = 'success';
                    } else if ($response['status'] == 'fail') {
                        if (isset($response['desc'])) {
                            DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->update(['api_response' => $response['desc']]);
                        }
                        $plan_status = 'fail';
                    } else {
                        $plan_status = 'process';
                    }
                } else if (isset($response['code'])) {
                    if ($response['code'] == 'fail') {
                        $plan_status = "fail";
                    } else {
                        $plan_status = null;
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
    public static function Virus2($data)
    {
        if (DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->count() == 1) {
            $sendRequest = DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->first();
            $network = DB::table('network')->where(['network' => $sendRequest->network])->first();
            $dataplan = DB::table('data_plan')->where(['plan_id' => $data['purchase_plan']])->first();
            $api_website = DB::table('web_api')->first();
            $virus_api = DB::table('virus_api')->first();
            if ($sendRequest->network == 'MTN' and $sendRequest->network_type == 'GIFTING') {
                $virus_id = "gifting";
            } else if ($sendRequest->network == 'AIRTEL' and $sendRequest->network_type == 'COOPERATE GIFTING') {
                $virus_id = "airtel-cg";
            } else {
                $virus_id = $network->virus_id;
            }

            $paypload = array(
                'network' => $virus_id,
                'mobile' => $sendRequest->plan_phone,
                'plan_code' => $dataplan->virus2,
                'token' => $virus_api->virus2,
                'request_id' => $data['transid']
            );
            $admin_details = [
                'endpoint' => $api_website->virus_website2 . "/api/data",
            ];
            $response = ApiSending::VIRUSAPI($admin_details, $paypload);
            // declare plan status
            if (!empty($response)) {
                if (isset($response['status'])) {
                    if ($response['status'] == 'success') {
                        if (isset($response['desc'])) {
                            DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->update(['api_response' => $response['desc']]);
                        }
                        $plan_status = 'success';
                    } else if ($response['status'] == 'fail') {
                        if (isset($response['desc'])) {
                            DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->update(['api_response' => $response['desc']]);
                        }
                        $plan_status = 'fail';
                    } else {
                        $plan_status = 'process';
                    }
                } else if (isset($response['code'])) {
                    if ($response['code'] == 'fail') {
                        $plan_status = "fail";
                    } else {
                        $plan_status = null;
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
    public static function Virus3($data)
    {
        if (DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->count() == 1) {
            $sendRequest = DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->first();
            $network = DB::table('network')->where(['network' => $sendRequest->network])->first();
            $dataplan = DB::table('data_plan')->where(['plan_id' => $data['purchase_plan']])->first();
            $api_website = DB::table('web_api')->first();
            $virus_api = DB::table('virus_api')->first();
            if ($sendRequest->network == 'MTN' and $sendRequest->network_type == 'GIFTING') {
                $virus_id = "gifting";
            } else if ($sendRequest->network == 'AIRTEL' and $sendRequest->network_type == 'COOPERATE GIFTING') {
                $virus_id = "airtel-cg";
            } else {
                $virus_id = $network->virus_id;
            }

            $paypload = array(
                'network' => $virus_id,
                'mobile' => $sendRequest->plan_phone,
                'plan_code' => $dataplan->virus3,
                'token' => $virus_api->virus3,
                'request_id' => $data['transid']
            );
            $admin_details = [
                'endpoint' => $api_website->virus_website3 . "/api/data",
            ];
            $response = ApiSending::VIRUSAPI($admin_details, $paypload);
            // declare plan status
            if (!empty($response)) {
                if (isset($response['status'])) {
                    if ($response['status'] == 'success') {
                        if (isset($response['desc'])) {
                            DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->update(['api_response' => $response['desc']]);
                        }
                        $plan_status = 'success';
                    } else if ($response['status'] == 'fail') {
                        if (isset($response['desc'])) {
                            DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->update(['api_response' => $response['desc']]);
                        }
                        $plan_status = 'fail';
                    } else {
                        $plan_status = 'process';
                    }
                } else if (isset($response['code'])) {
                    if ($response['code'] == 'fail') {
                        $plan_status = "fail";
                    } else {
                        $plan_status = null;
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
    public static function Virus4($data)
    {
        if (DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->count() == 1) {
            $sendRequest = DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->first();
            $network = DB::table('network')->where(['network' => $sendRequest->network])->first();
            $dataplan = DB::table('data_plan')->where(['plan_id' => $data['purchase_plan']])->first();
            $api_website = DB::table('web_api')->first();
            $virus_api = DB::table('virus_api')->first();
            if ($sendRequest->network == 'MTN' and $sendRequest->network_type == 'GIFTING') {
                $virus_id = "gifting";
            } else if ($sendRequest->network == 'AIRTEL' and $sendRequest->network_type == 'COOPERATE GIFTING') {
                $virus_id = "airtel-cg";
            } else {
                $virus_id = $network->virus_id;
            }

            $paypload = array(
                'network' => $virus_id,
                'mobile' => $sendRequest->plan_phone,
                'plan_code' => $dataplan->virus4,
                'token' => $virus_api->virus4,
                'request_id' => $data['transid']
            );
            $admin_details = [
                'endpoint' => $api_website->virus_website4 . "/api/data",
            ];
            $response = ApiSending::VIRUSAPI($admin_details, $paypload);
            // declare plan status
            if (!empty($response)) {
                if (isset($response['status'])) {
                    if ($response['status'] == 'success') {
                        if (isset($response['desc'])) {
                            DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->update(['api_response' => $response['desc']]);
                        }
                        $plan_status = 'success';
                    } else if ($response['status'] == 'fail') {
                        if (isset($response['desc'])) {
                            DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->update(['api_response' => $response['desc']]);
                        }
                        $plan_status = 'fail';
                    } else {
                        $plan_status = 'process';
                    }
                } else if (isset($response['code'])) {
                    if ($response['code'] == 'fail') {
                        $plan_status = "fail";
                    } else {
                        $plan_status = null;
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
    public static function Virus5($data)
    {
        if (DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->count() == 1) {
            $sendRequest = DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->first();
            $network = DB::table('network')->where(['network' => $sendRequest->network])->first();
            $dataplan = DB::table('data_plan')->where(['plan_id' => $data['purchase_plan']])->first();
            $api_website = DB::table('web_api')->first();
            $virus_api = DB::table('virus_api')->first();
            if ($sendRequest->network == 'MTN' and $sendRequest->network_type == 'GIFTING') {
                $virus_id = "gifting";
            } else if ($sendRequest->network == 'AIRTEL' and $sendRequest->network_type == 'COOPERATE GIFTING') {
                $virus_id = "airtel-cg";
            } else {
                $virus_id = $network->virus_id;
            }

            $paypload = array(
                'network' => $virus_id,
                'mobile' => $sendRequest->plan_phone,
                'plan_code' => $dataplan->virus5,
                'token' => $virus_api->virus5,
                'request_id' => $data['transid']
            );
            $admin_details = [
                'endpoint' => $api_website->virus_website5 . "/api/data",
            ];
            $response = ApiSending::VIRUSAPI($admin_details, $paypload);

            // declare plan status
            if (!empty($response)) {
                if (isset($response['status'])) {
                    if ($response['status'] == 'success') {
                        if (isset($response['desc'])) {
                            DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->update(['api_response' => $response['desc']]);
                        }
                        $plan_status = 'success';
                    } else if ($response['status'] == 'fail') {
                        if (isset($response['desc'])) {
                            DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->update(['api_response' => $response['desc']]);
                        }
                        $plan_status = 'fail';
                    } else {
                        $plan_status = 'process';
                    }
                } else if (isset($response['code'])) {
                    if ($response['code'] == 'fail') {
                        $plan_status = "fail";
                    } else {
                        $plan_status = null;
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


    public static function Simhosting($data)
    {
        if (DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->count() == 1) {
            $sendRequest = DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->first();
            $dataplan = DB::table('data_plan')->where(['plan_id' => $data['purchase_plan']])->first();
            $other_api = DB::table('other_api')->first();


            $paypload = array(
                'network' => 1,
                'number' => $sendRequest->plan_phone,
                'plan' => $dataplan->simhosting,
                'senderID' => $data['transid']
            );
            $endpoints = "https://api.mysimhosting.com/v1/data";
            $headers = [
                "Authorization: Bearer " . $other_api->simhosting,
                'Content-Type: application/json'
            ];
            $response = ApiSending::OTHERAPI($endpoints, $paypload, $headers);
            // declare plan status
            if (!empty($response)) {
                if (isset($response['status'])) {
                    if ($response['status'] == 'success') {
                        $plan_status = 'success';
                    } else if ($response['status'] == 'fail') {
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
    public static function Smeplug($data)
    {
        if (DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->count() == 1) {
            $sendRequest = DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->first();
            $dataplan = DB::table('data_plan')->where(['plan_id' => $data['purchase_plan']])->first();
            $network = DB::table('network')->where(['network' => $sendRequest->network])->first();
            $other_api = DB::table('other_api')->first();

            if ($network->network == 'MTN') {
                $the_network = '1';
            } else if ($network->network == 'AIRTEL') {
                $the_network = '2';
            } else if ($network->network == 'GLO') {
                $the_network = '4';
            } else {
                $the_network = '3';
            }

            $paypload = array(
                'network_id' => $the_network,
                'phone' => $sendRequest->plan_phone,
                'plan_id' => $dataplan->smeplug,
            );
            $endpoints = "https://smeplug.ng/api/v1/data/purchase";
            $headers =  [
                "Authorization: Bearer " . $other_api->smeplug,
                'Content-Type: application/json'
            ];
            $response = ApiSending::OTHERAPI($endpoints, $paypload, $headers);
            // declare plan status
            if (!empty($response)) {
                if (isset($response['status'])) {
                    if ($response['status'] == true) {
                        $plan_status = 'success';
                    } else if ($response['status'] == false) {
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
    public static function Msplug($data)
    {
        return null;
    }
    public static function Simserver($data)
    {
        if (DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->count() == 1) {
            $sendRequest = DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->first();
            $dataplan = DB::table('data_plan')->where(['plan_id' => $data['purchase_plan']])->first();
            $other_api = DB::table('other_api')->first();

            $paypload = array(
                'process' => "buy",
                'recipient' => $sendRequest->plan_phone,
                'api_key' => $other_api->simserver,
                'product_code' => $dataplan->simserver,
                'callback' => null,
                'user_reference' => $data['transid'],
            );
            $endpoints = "https://api.simservers.io";

            $response = ApiSending::OTHERAPI($endpoints, $paypload, null);
            // declare plan status
            if (!empty($response)) {
                if (isset($response['status'])) {
                    if ($response['status'] == 'success') {
                        $plan_status = 'success';
                    } else if ($response['status'] == 'fail') {
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
    public static function Ogdamns($data)
    {
        if (DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->count() == 1) {
            $sendRequest = DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->first();
            $dataplan = DB::table('data_plan')->where(['plan_id' => $data['purchase_plan']])->first();
            $network = DB::table('network')->where(['network' => $sendRequest->network])->first();
            $other_api = DB::table('other_api')->first();

            if ($network->network == 'MTN') {
                $the_network = '1';
            } else if ($network->network == 'AIRTEL') {
                $the_network = '2';
            } else if ($network->network == 'GLO') {
                $the_network = '3';
            } else {
                $the_network = '4';
            }

            $paypload = array(
                'networkId' => $the_network,
                'phoneNumber' => $sendRequest->plan_phone,
                'planId' => $dataplan->ogdamns,
                'reference' => $data['transid']
            );
            $endpoints = "https://simhosting.ogdams.ng/api/v1/vend/data";
            $headers =  [
                "Authorization: Bearer " . $other_api->ogdamns,
                'Content-Type: application/json'
            ];
            $response = ApiSending::OTHERAPI($endpoints, $paypload, $headers);
            // declare plan status
            if (!empty($response)) {
                if (isset($response['status'])) {
                    if ($response['status'] == true) {
                        $plan_status = 'success';
                    } else if ($response['status'] == false) {
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
    public static function Email($data)
    {
        if (DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->count() == 1) {
            $sendRequest = DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->first();
            $network = DB::table('network')->where(['network' => $sendRequest->network])->first();

            $message = strtoupper($sendRequest->username) . ' wants to buy ' . $network->network . ' ' . $sendRequest->network_type . ' ' . $sendRequest->plan_name . ' ₦' . number_format($sendRequest->amount, 2) . ' to ' . $sendRequest->plan_phone . '.  Refreence is ' . $sendRequest->transid;
            $datas = [
                'mes' => $message,
                'title' => 'DATA PURCHASE'
            ];
            $response = ApiSending::ADMINEMAIL($datas);
            if (!empty($response)) {
                if ($response['status'] == 'success') {
                    $plan_status = 'success';
                } else if ($response['status'] !=  'fail') {
                    $plan_status = 'fail';
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
        if (DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->count() == 1) {
            $sendRequest = DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->first();
            $dataplan = DB::table('data_plan')->where(['plan_id' => $data['purchase_plan']])->first();
            $network = DB::table('network')->where(['network' => $sendRequest->network])->first();
            $other_api = DB::table('other_api')->first();

            if ($network->network == 'MTN') {
                $the_network = 01;
            } else if ($network->network == 'AIRTEL') {
                $the_network = 03;
            } else if ($network->network == 'GLO') {
                $the_network = 02;
            } else {
                $the_network = 04;
            }

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://easyaccessapi.com.ng/api/data.php",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => array(
                    'network' => $the_network,
                    'mobileno' => $sendRequest->plan_phone,
                    'dataplan' => $dataplan->easyaccess,
                    'client_reference' => $data['transid'], //update this on your script to receive webhook notifications
                ),
                CURLOPT_HTTPHEADER => array(
                    "AuthorizationToken: " . $other_api->easy_access, //replace this with your authorization_token
                    "cache-control: no-cache"
                ),
            ));
            $response = json_decode(curl_exec($curl), true);
            curl_close($curl);
            if ($response) {
                if ($response['success'] == 'true') {
                    return 'success';
                } else {
                    return 'fail';
                }
            }
        } else {
            return 'fail';
        }
    }

    public static function Megasubcloud($data)
    {
        if (DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->count() == 1) {
            $sendRequest = DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->first();
            $dataplan = DB::table('data_plan')->where(['plan_id' => $data['purchase_plan']])->first();
            $other_api = DB::table('other_api')->first();
            $payload = [
                'network_api_id' => '5',
                'mobile_number' => $sendRequest->plan_phone,
                'data_api_id' => $dataplan->megasubcloud,
                'validatephonenetwork' => false,
                'duplication_check' => false
            ];
            $endpoints = 'https://www.101terabyte.com/API/?action=buy_data';
            $headers = [
                "Authorization: Token ",
                'Content-Type: multipart/form-data',
                'Password: '
            ];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $endpoints);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, ($payload));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            if (isset($headers)) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            }
            $dataapi = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            $response = json_decode($dataapi, true);
            if (!empty($response)) {
                if ($response['Detail']['success']) {
                    if ($response['Detail']['success'] == 'true') {
                        if (isset($response['Detail']['info']['Id'])) {
                            $trans = $response['Detail']['info']['Id'];
                            $realresponse = $response['Detail']['info']['realresponse'];
                            if ($response['Detail']['info']['Success'] == '1') {
                                DB::table('data')->where(['transid' => $sendRequest->transid])->update(['mega_trans' => $trans, 'api_response' => $realresponse]);
                                DB::table('message')->where(['transid' => $sendRequest->transid])->update(['message' => $realresponse]);
                                return 'success';
                            } else {
                                DB::table('data')->where(['transid' => $sendRequest->transid])->update(['mega_trans' => $trans, 'api_response' => $realresponse]);
                                DB::table('message')->where(['transid' => $sendRequest->transid])->update(['message' => $realresponse]);
                                return 'fail';
                            }
                        } else {
                            return 'fail';
                        }
                    } else {
                        return 'fail';
                    }
                } else {
                    return ' fail';
                }
            } else {
                return 'fail';
            }
        } else {
            return 'fail';
        }
    }

    public static function Mega($data)
    {
        if (DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->count() == 1) {
            $sendRequest = DB::table('data')->where(['username' => $data['username'], 'transid' => $data['transid']])->first();
            $dataplan = DB::table('data_plan')->where(['plan_id' => $data['purchase_plan']])->first();
            $other_api = DB::table('other_api')->first();
            $payload = [
                'network_api_id' => '5',
                'mobile_number' => $sendRequest->plan_phone,
                'data_api_id' => $dataplan->megasub,
                'validatephonenetwork' => false,
                'duplication_check' => false
            ];
            $headers = [
                "Authorization: Token ",
                'Content-Type: multipart/form-data',
                'Password: '
            ];
            $endpoints = 'https://megasubplug.com/API/?action=buy_data';
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $endpoints);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, ($payload));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            if (isset($headers)) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            }
            $dataapi = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            $api_result = json_decode($dataapi, true);
            if (!empty($api_result)) {
                if ($api_result['Detail']['success']) {
                    if ($api_result['Detail']['success'] == 'true') {
                        if (isset($api_result['Detail']['info']['Id'])) {
                            $trans = $api_result['Detail']['info']['Id'];
                            DB::table('data')->where(['transid' => $sendRequest->transid])->update(['mega_trans' => $trans]);
                            return 'success';
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
        } else {
            return 'fail';
        }
    }
}
