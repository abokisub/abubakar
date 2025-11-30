<?php

use App\Http\Controllers\API\AdminController;
use App\Http\Controllers\API\AdminTrans;
use App\Http\Controllers\API\AppController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\MessageController;
use App\Http\Controllers\API\NewStock;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\PlanController;
use App\Http\Controllers\API\SecureController;
use App\Http\Controllers\API\Selection;
use App\Http\Controllers\API\Trans;
use App\Http\Controllers\API\TransactionCalculator;
use App\Http\Controllers\API\WebhookController;
use App\Http\Controllers\Purchase\AccessUser;
use App\Http\Controllers\Purchase\AirtimeCash;
use App\Http\Controllers\Purchase\AirtimePurchase;
use App\Http\Controllers\Purchase\BillPurchase;
use App\Http\Controllers\Purchase\BonusTransfer;
use App\Http\Controllers\Purchase\BulksmsPurchase;
use App\Http\Controllers\Purchase\CablePurchase;
use App\Http\Controllers\Purchase\DataPurchase;
use App\Http\Controllers\Purchase\ExamPurchase;
use App\Http\Controllers\Purchase\IUCvad;
use App\Http\Controllers\Purchase\MeterVerify;
use App\Http\Controllers\APP\Auth;
use App\Http\Controllers\Purchase\DataCard;
use App\Http\Controllers\Purchase\RechargeCard;
use App\Http\Controllers\API\Banks;


Route::get('account/my-account/{id}', [AuthController::class, 'account']);
Route::post('register', [AuthController::class, 'register']);
Route::post('verify/user/accounct', [AuthController::class, 'verify']);
Route::get('website/app/setting', [AppController::class, 'system']);
Route::post('login/verify/user', [AuthController::class, 'login']);
Route::post('logout/user', [AuthController::class, 'logout']);
Route::post('upgrade/api/user', [AppController::class, 'apiUpgrade']);
Route::get('/user/resend/{id}/otp', [AuthController::class, 'resendOtp']);
Route::post('/website/affliate/user', [AppController::class, 'buildWebsite']);
Route::get('/upgrade/awuf/{id}/user', [AppController::class, 'AwufPackage']);
Route::get('/upgrade/agent/{id}/user', [AppController::class, 'AgentPackage']);
Route::get('/website/app/network', [AppController::class, 'SystemNetwork']);
Route::get('airtimecash/number', [AppController::class, 'CashNumber']);
Route::get('/verify/network/{id}/adex/system', [AppController::class, 'checkNetworkType']);
Route::get('/system/notification/user/{id}/request', [AdminController::class, 'userRequest']);
Route::get('/clear/notification/clear/all/{id}/by/admin', [AdminController::class, 'ClearRequest']);
Route::get('/system/all/user/adex/admin/safe/url/{id}/secure', [AdminController::class, 'UserSystem']);
Route::post('/delete/user/adex/user/hacker/{id}/system', [AppController::class, 'DeleteUser']);
Route::post('/delete/single/adex/user/hacker/{id}/system', [AppController::class, 'singleDelete']);
Route::post('/system/all/user/edit/user/safe/url/{id}/secure', [AdminController::class, 'editUserDetails']);
Route::post('/system/admin/create/new/user/safe/url/{id}/secure', [AdminController::class, 'CreateNewUser']);
Route::post('/system/admin/change_key/changes/of/key/url/{id}/secure', [AdminController::class, 'ChangeApiKey']);
Route::post('/system/admin/edit/edituser/adex/adex/secure/boss/asd/asd/changes/of/key/url/{id}/secure', [AdminController::class, 'EditUser']);
Route::post('/filter/user/details/admin/by/adex/{id}/secure/react', [AdminController::class, 'FilterUser']);
Route::post('/credit/user/only/admin/secure/{id}/verified/by/adex', [AdminController::class, 'CreditUserAdex']);
Route::post('/credit/upgradeuser/upgrade/{id}/adex/by/adex', [AdminController::class, 'UpgradeUserAccount']);
Route::post('/reset/user/account/{id}/adex/secure', [AdminController::class, 'ResetUserPassword']);
Route::post('/delete/user/adex/automated/hacker/{id}/system', [AdminController::class, 'Automated']);
Route::post('/delete/user/adex/bank/hacker/{id}/system', [AdminController::class, 'BankDetails']);
Route::post('/reset/user/block/number/{id}/adex/secure', [AdminController::class, 'AddBlock']);
Route::post('delete/user/adex/block/hacker/{id}/system', [AdminController::class, 'DeleteBlock']);
Route::get('system/all/user/discount/discount/user/safe/url/{id}/secure', [AdminController::class, 'Discount']);
Route::post('edit/airtime/discount/account/{id}/adex/secure', [AdminController::class, 'AirtimeDiscount']);
Route::post('edit/cable/charges/account/{id}/adex/secure', [AdminController::class, 'CableCharges']);
Route::post('edit/bill/charges/account/{id}/adex/secure', [AdminController::class, 'BillCharges']);
Route::post('edit/cash/discount/charges/account/{id}/adex/secure', [AdminController::class, 'CashDiscount']);
Route::post('edit/result/charges/account/{id}/adex/secure', [AdminController::class, 'ResultCharge']);
Route::post('edit/other/charges/account/{id}/adex/secure', [AdminController::class, 'OtherCharge']);
Route::post('edit/airtime/lock/account/{id}/adex/secure', [SecureController::class, 'Airtimelock']);
Route::post('edit/data/lock/account/{id}/adex/secure', [SecureController::class, 'DataLock']);
Route::post('edit/cable/lock/account/{id}/adex/secure', [SecureController::class, 'CableLock']);
Route::post('edit/result/lock/account/{id}/adex/secure', [SecureController::class, 'ResultLock']);
Route::post('edit/other/lock/account/{id}/adex/secure', [SecureController::class, 'OtherLock']);
Route::post('delete/data/adex/plans/hacker/{id}/system', [SecureController::class, 'DataPlanDelete']);
Route::post('add/data/plan/new/adex/safe/url/{id}/secure', [SecureController::class, 'AddDataPlan']);
Route::post('system/data/plan/edit/user/safe/url/{id}/secure', [SecureController::class, 'RDataPlan']);
Route::post('system/admin/edit/dataplan/dataplan/adex/secure/boss/asd/asd/changes/{id}/secure', [SecureController::class, 'EditDataPlan']);
Route::post('delete/cable/adex/plans/hacker/{id}/system', [SecureController::class, 'DeleteCablePlan']);
Route::post('system/cable/plan/edit/user/safe/url/{id}/secure', [SecureController::class, 'RCablePlan']);
Route::post('add/cable/plan/new/adex/safe/url/{id}/secure', [SecureController::class, 'AddCablePlan']);
Route::post('system/admin/edit/cableplan/cableplan/adex/secure/boss/asd/asd/changes/{id}/secure', [SecureController::class, 'EditCablePlan']);
Route::post('delete/bill/adex/plans/hacker/{id}/system', [SecureController::class, 'DeleteBillPlan']);
Route::post('system/bill/plan/edit/user/safe/url/{id}/secure', [SecureController::class, 'RBillPlan']);
Route::post('add/bill/plan/new/adex/safe/url/{id}/secure', [SecureController::class, 'CreateBillPlan']);
Route::post('system/admin/edit/billplan/billplan/adex/secure/boss/asd/asd/changes/{id}/secure', [SecureController::class, 'EditBillPlan']);
Route::post('system/network/plan/edit/user/safe/url/{id}/secure', [SecureController::class, 'RNetwork']);
Route::post('edit/network/plan/new/adex/safe/url/{id}/secure', [SecureController::class, 'EditeNetwork']);
Route::post('edit/adexpi/charges/account/{id}/adex/secure', [SecureController::class, 'EditAdexApi']);
Route::post('edit/msorgapi/charges/account/{id}/adex/secure', [SecureController::class, 'EditMsorgApi']);
Route::post('edit/virusapi/charges/account/{id}/adex/secure', [SecureController::class, 'EditVirusApi']);
Route::post('edit/otherapi/charges/account/{id}/adex/secure', [SecureController::class, 'EditOtherApi']);
Route::post('edit/webapi/charges/account/{id}/adex/secure', [SecureController::class, 'EditWebUrl']);
Route::post('system/result/plan/edit/user/safe/url/{id}/secure', [SecureController::class, 'RResult']);
Route::post('add/result/plan/new/adex/safe/url/{id}/secure', [SecureController::class, 'AddResult']);
Route::post('delete/result/adex/plans/hacker/{id}/system', [SecureController::class, 'DelteResult']);
Route::post('system/admin/edit/resultplan/resultplan/adex/secure/boss/asd/asd/changes/{id}/secure', [SecureController::class, 'EditResult']);
Route::get('system/notification/user/{id}/request/user', [AppController::class, 'UserNotif']);
Route::get('clear/notification/clear/all/{id}/by/user', [AppController::class, 'ClearNotifUser']);
Route::get('user/stock/wallet/{id}/secure/adex', [SecureController::class, 'UserStock']);
Route::post('user/edit/stockvending/{id}/adex/secure', [SecureController::class, 'UserEditStock']);
Route::post('edituser/adex/secure/{id}/secure', [SecureController::class, 'UserProfile']);
Route::post('change/password/by/user/adex/{id}/now', [SecureController::class, 'ResetPasswordUser']);
Route::post('change/pin/by/user/adex/{id}/now', [SecureController::class, 'ChangePin']);
Route::post('create/newpin/by/user/adex/{id}/now', [SecureController::class, 'CreatePin']);
Route::post('accountdetails/adex/secure/{id}/secure', [SecureController::class, 'UserAccountDetails']);
Route::get('user/accountdetails/wallet/{id}/secure/adex', [SecureController::class, 'UsersAccountDetails']);
Route::post('get/data/plans/{id}/adex', [PlanController::class, 'DataPlan']);
Route::get('cable/plan/{id}/adex/system', [PlanController::class, 'CablePlan']);
Route::get('cable/charges/{id}/admin', [PlanController::class, 'CableCharges']);
Route::post('edit/datasel/account/{id}/adex/secure', [AdminController::class, 'DataSel']);
Route::post('edit/data_card_sel/account/{id}/secure', [AdminController::class, 'DataCardSel']);
Route::post('edit/recharge_card_sel/account/{id}/secure', [AdminController::class, 'RechargeCardSel']);
Route::post('edit/airtimesel/account/{id}/adex/secure', [AdminController::class, 'AirtimeSel']);
Route::post('edit/cablesel/account/{id}/adex/secure', [AdminController::class, 'CableSel']);
Route::post('edit/billsel/account/{id}/adex/secure', [AdminController::class, 'BillSel']);
Route::post('edit/bulksmssel/account/{id}/adex/secure', [AdminController::class, 'BulkSMSsel']);
Route::post('edit/examsel/account/{id}/adex/secure', [AdminController::class, 'ExamSel']);
Route::get('website/app/cable/lock', [AppController::class, 'CableName']);
Route::get('bill/charges/{id}/admin', [AppController::class, 'BillCal']);
Route::get('website/app/bill/list', [AppController::class, 'DiscoList']);
Route::post('airtimecash/discount/admin', [AppController::class, 'AirtimeCash']);
Route::get('bulksms/cal/admin', [AppController::class, 'BulksmsCal']);
Route::get('resultprice/admin/secure', [AppController::class, 'ResultPrice']);
Route::get('total/data/purchase/{id}/secure', [SecureController::class, 'DataPurchased']);
Route::get('system/user/stockbalance/{id}/secure', [SecureController::class, 'StockBalance']);
Route::get('system/app/softwarwe', [SecureController::class, 'SOFTWARE']);
Route::post('edit/systeminfo/{id}/adex/secure', [SecureController::class, 'SystemInfo']);
Route::post('system/message/{id}/adex/secure', [SecureController::class, 'SytemMessage']);
Route::post('delete/feature/{id}/system', [SecureController::class, 'DeleteFeature']);
Route::post('new/feature/{id}/adex/secure', [SecureController::class, 'AddFeature']);
Route::post('delete/app/{id}/system', [SecureController::class, 'DeleteApp']);
Route::post('new/app/{id}/adex/secure', [SecureController::class, 'NewApp']);
Route::post('edit/paymentinfo/{id}/adex/secure', [SecureController::class, 'PaymentInfo']);
Route::post('manualpayment/adex/secure/{id}/secure', [PaymentController::class, 'BankTransfer']);
Route::get('all/user/infomation/admin/setting/{id}/secure', [AdminController::class, 'AllUsersInfo']);
Route::get('bank/info/all/bank/all/bank/{id}/secure', [AdminController::class, 'AllBankDetails']);
Route::get('user/bank/account/details/{id}/secure', [AdminController::class, 'UserBankAccountD']);
Route::get('user/banned/adex/ade/banned/user/{id}/secure', [AdminController::class, 'AllUserBanned']);
Route::get('all/system/plan/purchase/by/adex/{id}/secure', [AdminController::class, 'AllSystemPlan']);


Route::post('new_data_card_plan/{id}/secure', [NewStock::class, 'NewDataCardPlan']);
Route::post('new_recharge_card_plan/{id}/secure', [NewStock::class, 'NewRechargeCardPlan']);
Route::get('all/store/plan/{id}/secure', [NewStock::class, 'AllNewStock']);
Route::post('delete/data_card_plan/{id}/system', [NewStock::class, 'DeleteDataCardPlan']);
Route::post('delete/recharge_card_plan/{id}/system', [NewStock::class, 'DeleteRechargeCardPlan']);
Route::post('adex/data_plan_card/{id}/secure', [NewStock::class, 'RDataCardPlan']);
Route::post('adex/recharge_plan_card/{id}/secure', [NewStock::class, 'RRechargeCardPlan']);
Route::post('edit_data_card_plan/{id}/secure', [NewStock::class, 'EditDataCard']);
Route::post('edit_new_recharge_card_plan/{id}/secure', [NewStock::class, 'EditRechargeCardPlan']);
Route::post('delete/store_data_card/{id}/system', [NewStock::class, 'DeleteStockDataCard']);
Route::post('get/data_card_plan/{id}/system', [NewStock::class, 'DataCardPlansList']);
Route::post('add_store_data_card/{id}/secure', [NewStock::class, 'StoreDataCard']);
Route::post('r_add_store_data_card/{id}/secure', [NewStock::class, 'RStockDataCard']);
Route::post('r_add_store_recharge_card/{id}/secure', [NewStock::class, 'RStockRechargeCard']);
Route::post('get/recharge_card_plan/{id}/system', [NewStock::class, 'RechargeCardPlanList']);
Route::post('edit_store_data_plans/{id}/secure', [NewStock::class, 'EditDataCardPlan']);
Route::post('delete/store_recharge_card/{id}/system', [NewStock::class, 'DeleteStockRechargeCardPlan']);
Route::post('add_store_recharge_card/{id}/secure', [NewStock::class, 'AddStockRechargeCard']);
Route::post('edit_store_recharge_plans/{id}/secure', [NewStock::class, 'EditStoreRechargePlan']);
Route::post('data_card_lock/{id}/secure', [NewStock::class, 'DataCardLock']);
Route::post('recharge_card_lock/{id}/secure', [NewStock::class, 'RechargeCardLock']);
Route::post('get/data_card_plans/{id}/adex', [NewStock::class, 'UserDataCardPlan']);
Route::post('get/recharge_card_plans/{id}/adex', [NewStock::class, 'UserRechargeCardPlan']);
// transas both admin and users here
Route::get('all/data_recharge_cards/{id}/secure', [Trans::class, 'DataRechardPrint']);
Route::get('recharge_card/trans/{id}/secure', [Trans::class, 'RechargeCardProcess']);
Route::get('recharge_card/trans/{id}/secure/sucess', [Trans::class, 'RechargeCardPrint']);
Route::post('search/by/user/{id}/history', [Trans::class, 'SearchAllDataBase']);
Route::get('system/all/trans/{id}/secure', [Trans::class, 'UserTrans']);
Route::get('system/all/history/adex/{id}/secure', [Trans::class, 'AllHistoryUser']);
Route::get('system/all/datatrans/adex/{id}/secure', [Trans::class, 'AllDataHistoryUser']);
Route::get('system/all/stock/trans/adex/{id}/secure', [Trans::class, 'AllStockHistoryUser']);
Route::get('system/all/deposit/trans/adex/{id}/secure', [Trans::class, 'AllDepositHistory']);
Route::get('system/all/airtime/trans/adex/{id}/secure', [Trans::class, 'AllAirtimeUser']);
Route::get('data_card/trans/{id}/secure', [Trans::class, 'DataCardInvoice']);
Route::get('data_card/trans/{id}/secure/sucess', [Trans::class, 'DataCardSuccess']);
Route::get('data/trans/{id}/secure', [Trans::class, "DataTrans"]);
Route::get('airtime/trans/{id}/secure', [Trans::class, 'AirtimeTrans']);
Route::get('deposit/trans/{id}/secure', [Trans::class, 'DepositTrans']);
Route::get('cable/trans/{id}/secure', [Trans::class, 'CableTrans']);
Route::get('bill/trans/{id}/secure', [Trans::class, 'BillTrans']);
Route::get('airtimecash/trans/{id}/secure', [Trans::class, 'AirtimeCashTrans']);
Route::get('bulksms/trans/{id}/secure', [Trans::class, 'BulkSMSTrans']);
Route::get('resultchecker/trans/{id}/secure', [Trans::class, 'ResultCheckerTrans']);
Route::get('manual/trans/{id}/secure', [Trans::class, 'ManualTransfer']);
Route::get('website/app/{id}/data_card_pan', [PlanController::class, 'DataCard']);
Route::get('website/app/{id}/recharge_card_pan', [PlanController::class, 'RechargeCard']);
Route::get('website/app/{id}/dataplan', [PlanController::class, 'DataList']);
Route::get('website/app/cableplan', [PlanController::class, 'CableList']);
Route::get('website/app/disco', [PlanController::class, 'DiscoList']);
Route::get('website/app/exam', [PlanController::class, 'ExamList']);
// api endpoint for users
Route::post('data', [DataPurchase::class, 'BuyData']);
Route::post('topup', [AirtimePurchase::class, 'BuyAirtime']);
Route::get('cable/cable-validation', [IUCvad::class, 'IUC']);
Route::post('cable', [CablePurchase::class, 'BuyCable']);
Route::get('bill/bill-validation', [MeterVerify::class, 'Check']);
Route::post('bill', [BillPurchase::class, 'Buy']);
Route::post('cash', [AirtimeCash::class, 'Convert']);
Route::post('bulksms', [BulksmsPurchase::class, 'Buy']);
Route::post('transferwallet', [BonusTransfer::class, 'Convert']);
Route::post('exam', [ExamPurchase::class, 'ExamPurchase']);
Route::post('user', [AccessUser::class, 'Generate']);
Route::post('data_card', [DataCard::class, 'DataCardPurchase']);
Route::post('recharge_card', [RechargeCard::class, 'RechargeCardPurchase']);
// admin transaction and auto refund

Route::get('admin/adex/all/trans/{id}/secure', [AdminTrans::class, 'AllTrans']);
Route::post('admin/data_card_refund/{id}/secure', [AdminTrans::class, 'DataCardRefund']);
Route::post('admin/recharge_card_refund/{id}/secure', [AdminTrans::class, 'RechargeCardRefund']);
Route::get('admin/all/data_recharge_cards/{id}/secure', [AdminTrans::class, 'DataRechargeCard']);
Route::get('admin/all/transaction/history/{id}/secure', [AdminTrans::class, 'AllSummaryTrans']);
Route::get('admin/all/data/trans/by/adex/{id}/secure', [AdminTrans::class, 'DataTransSum']);
Route::get('admin/all/airtime/trans/by/adex/{id}/secure', [AdminTrans::class, 'AirtimeTransSum']);
Route::get('admin/all/stock/trans/by/adex/{id}/secure', [AdminTrans::class, 'StockTransSum']);
Route::get('admin/all/deposit/trans/by/adex/{id}/secure', [AdminTrans::class, 'DepositTransSum']);
Route::post('admin/data/{id}/secure', [AdminTrans::class, 'DataRefund']);
Route::post('admin/airtime/{id}/secure', [AdminTrans::class, 'AirtimeRefund']);
Route::post('admin/cable/{id}/secure', [AdminTrans::class, 'CableRefund']);
Route::post('admin/bill/{id}/secure', [AdminTrans::class, 'BillRefund']);
Route::post('admin/exam/{id}/secure', [AdminTrans::class, 'ResultRefund']);
Route::post('admin/bulksms/{id}/secure', [AdminTrans::class, 'BulkSmsRefund']);
Route::post('cash/data/{id}/secure', [AdminTrans::class, 'AirtimeCashRefund']);
Route::post('manual/data/{id}/secure', [AdminTrans::class, 'ManualSuccess']);
//message notif
Route::post('gmail/sendmessage/{id}/adex/secure', [MessageController::class, 'Gmail']);
Route::post('system/sendmessage/{id}/adex/secure', [MessageController::class, 'System']);
Route::post('bulksms/sendmessage/{id}/adex/secure', [MessageController::class, 'Bulksms']);
//calculator
Route::post('transaction/calculator/{id}/adex/secure', [TransactionCalculator::class, 'Admin']);
Route::post('user/calculator/{id}/adex/secure', [TransactionCalculator::class, 'User']);

// fund
Route::post('atmfunding/adex/secure/{id}/secure', [PaymentController::class, 'ATM']);
// Route::get('monnify/callback', [PaymentController::class, 'MonnifyATM']);
Route::any('xixapay_webhook/secure/callback/pay/techboy/3399', [PaymentController::class, 'Xixapay']);
Route::post('paystack/adex/secure/{id}/secure', [PaymentController::class, 'Paystackfunding']);
Route::get('callback/paystack', [PaymentController::class, 'PaystackCallBack']);

Route::post('update-kyc-here/adex/secure', [PaymentController::class, 'UpdateKYC']);
Route::post('dynamic-account-number-here/adex/secure', [PaymentController::class, 'DynamicAccount']);

Route::any('callback/simserver', [WebhookController::class, 'Simserver']);
Route::post('adex/webhook/secure', [WebhookController::class, 'AdexWebhook']);
// invite
Route::post('inviting/user/{id}/secure', [SecureController::class, 'InviteUser']);
//reset
Route::post('reset/mypassword', [SecureController::class, 'ResetPassword']);
Route::post('change/mypassword/{id}/secure', [SecureController::class, 'ChangePPassword']);

// list data plan
Route::get('website/plan', [PlanController::class, 'HomeData']);

// sel

Route::get('data/sel/by/adex/{id}/secure', [Selection::class, 'DataSel']);
Route::get('airtime/sel/by/adex/{id}/secure', [Selection::class, 'AirtimeSel']);
Route::get('cable/sel/by/adex/{id}/secure', [Selection::class, 'CableSel']);
Route::get('bulksms/sel/by/adex/{id}/secure', [Selection::class, 'BulksmsSel']);
Route::get('bill/sel/by/adex/{id}/secure', [Selection::class, 'BillSel']);
Route::get('result/sel/by/adex/{id}/secure', [Selection::class, 'ResultSel']);
Route::get('data_card_sel/adex/{id}/data_card', [Selection::class, 'DataCard']);
Route::get('recharge_card_sel/adex/{id}/recharge_card', [Selection::class, 'RechargeCard']);


// app link over here


Route::post('app/adex/secure/login', [Auth::class, 'AppLogin']);
Route::post('app/adex/verify/otp', [Auth::class, 'AppVerify']);
Route::post('app/adex/resend/otp', [Auth::class, 'ResendOtp']);
Route::post('app/adex/signup', [Auth::class, 'SignUp']);
Route::post('app/finger/adex/login', [Auth::class, 'FingerPrint']);
Route::post('app/secure/check/login/details', [Auth::class, 'APPLOAD']);
Route::get('app/adex/setting', [Auth::class, 'AppGeneral']);
// Route::post('app/check/monnify/secure', [Auth::class, 'APPMOnify']);
Route::post('app/manual/funding/{id}/send', [Auth::class, 'ManualFunding']);
Route::get('app/network', [Auth::class, 'Network']);
Route::get('app/network_type/{id}/check', [Auth::class, 'NetworkType']);
Route::post('app/data_plan/{id}/load', [Auth::class, 'DataPlans']);
Route::post('app/verify/transaction-pin', [Auth::class, 'TransactionPin']);
Route::get('app/cable_bill', [Auth::class, 'CableBillID']);
Route::post('app/cable_plan/load', [Auth::class, 'CablePlan']);
Route::post('app/price', [Auth::class, 'PriceList']);
Route::post('app/transaction', [Auth::class, 'Transaction']);
Route::post('app/profile_image', [Auth::class, 'ProfileImage']);
Route::post('app/notification', [Auth::class, 'Notification']);
Route::post('app/complete_profile', [Auth::class, 'CompleteProfile']);
Route::post('app/complete_pin', [Auth::class, 'NewPin']);
Route::post('app/deposit/transaction', [Auth::class, 'DepositTransaction']);
Route::post('app/transaction/details', [Auth::class, 'TransactionInvoice']);
Route::post('app/transaction_history_adex_doing', [Auth::class, 'TransactionHistoryAdex']);
Route::post('app/system_notification_here', [Auth::class, 'AppSystemNotification']);
Route::post('app/clear/notification/here', [Auth::class, 'ClearNotification']);
Route::post('app/recent_transacion', [Auth::class, 'recentTransaction']);
Route::post('app/data_card_plan', [Auth::class, 'DataCardPlans']);
Route::post('app/recharge_card_plan', [Auth::class, 'RechargeCardPlans']);
Route::post('app/otp_transaction_pin', [Auth::class, 'SendOtp']);
Route::post('app/delete_account_adex', [Auth::class, 'DeleteUserAccountNot']);

// data and airtime refund
Route::get('refund/adex/refund', [AdminTrans::class, 'AutoRefundByAdex']);
Route::get('success/adex/success', [AdminTrans::class, 'AutoSuccessByAdex']);

Route::get('check/banks/user/gstar/{id}/secure/this/site/here', [Banks::class, 'GetBanksArray']);
// api get admin balance

Route::get('check/api/balance/{id}/secure', [AdminController::class, 'ApiBalance']);

// Route::get('adex-export-to-excel', [PaymentController::class, 'importExcel']);

Route::get('/test', function () {
    return response()->json(['status' => 'ok']);
});

Route::post('xixapay/webhook', [\App\Http\Controllers\API\PaymentController::class, 'Xixapay']);
Route::post('monnify/webhook', [\App\Http\Controllers\API\PaymentController::class, 'MonnifyWebhook']);

// BellBank API Routes (protected - requires authentication)
Route::prefix('bellbank')->middleware('auth:sanctum')->group(function () {
    Route::get('banks', [\App\Http\Controllers\API\BellBankController::class, 'getBanks']);
    Route::post('name-enquiry', [\App\Http\Controllers\API\BellBankController::class, 'nameEnquiry']);
    Route::post('virtual-account', [\App\Http\Controllers\API\BellBankController::class, 'createVirtualAccount']);
    Route::post('transfer', [\App\Http\Controllers\API\BellBankController::class, 'initiateTransfer']);
    Route::get('transaction/{reference}', [\App\Http\Controllers\API\BellBankController::class, 'getTransactionStatus']);
});

// BellBank User Routes (with token verification)
Route::prefix('bellbank')->group(function () {
    Route::get('banks/{id}/secure', [\App\Http\Controllers\API\BellBankController::class, 'userGetBanks']);
    Route::get('beneficiaries/{id}/secure', [\App\Http\Controllers\API\BellBankController::class, 'getBeneficiaries']);
    Route::post('beneficiaries/{id}/secure', [\App\Http\Controllers\API\BellBankController::class, 'saveBeneficiary']);
    Route::delete('beneficiaries/{beneficiaryId}/{id}/secure', [\App\Http\Controllers\API\BellBankController::class, 'deleteBeneficiary']);
    Route::post('beneficiaries/{beneficiaryId}/toggle-favorite/{id}/secure', [\App\Http\Controllers\API\BellBankController::class, 'toggleFavoriteBeneficiary']);
    Route::post('name-enquiry/{id}/secure', [\App\Http\Controllers\API\BellBankController::class, 'userNameEnquiry']);
    Route::post('transfer/{id}/secure', [\App\Http\Controllers\API\BellBankController::class, 'userInitiateTransfer']);
    Route::get('transfer/receipt/{reference}/{id}/secure', [\App\Http\Controllers\API\BellBankController::class, 'getUserTransferReceipt']);
});

// BellBank Admin Routes
Route::get('admin/bellbank/accounts/{id}/secure', [\App\Http\Controllers\API\BellBankController::class, 'adminGetAccounts']);
Route::get('admin/bellbank/transactions/{id}/secure', [\App\Http\Controllers\API\BellBankController::class, 'adminGetTransactions']);
Route::get('admin/bellbank/transfers/{id}/secure', [\App\Http\Controllers\API\BellBankController::class, 'adminGetTransfers']);
Route::post('admin/bellbank/transfers/{id}/refresh/{reference}/secure', [\App\Http\Controllers\API\BellBankController::class, 'adminRefreshTransactionStatus']);
Route::get('admin/bellbank/kyc/{id}/secure', [\App\Http\Controllers\API\BellBankController::class, 'adminGetKYC']);
Route::get('admin/bellbank/webhooks/{id}/secure', [\App\Http\Controllers\API\BellBankController::class, 'adminGetWebhooks']);
Route::get('admin/bellbank/banks/{id}/secure', [\App\Http\Controllers\API\BellBankController::class, 'adminGetBanks']);
Route::post('admin/bellbank/name-enquiry/{id}/secure', [\App\Http\Controllers\API\BellBankController::class, 'adminNameEnquiry']);
Route::post('admin/bellbank/transfer/{id}/secure', [\App\Http\Controllers\API\BellBankController::class, 'adminInitiateTransfer']);
Route::get('admin/bellbank/settings/{id}/secure', [\App\Http\Controllers\API\BellBankController::class, 'adminGetSettings']);
Route::post('admin/bellbank/settings/{id}/secure', [\App\Http\Controllers\API\BellBankController::class, 'adminUpdateSettings']);

// BellBank Webhook (no auth required, signature verified)
Route::post('webhook/bellbank', [\App\Http\Controllers\API\BellBankWebhookController::class, 'handle']);
