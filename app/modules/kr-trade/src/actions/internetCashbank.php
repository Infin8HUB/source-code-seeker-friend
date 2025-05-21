<?php
session_start();

require "../../../../../config/config.settings.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/vendor/autoload.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/MySQL/MySQL.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/App/App.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/App/AppModule.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/User/User.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/Leads8Api/LeadsApi.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/Lang/Lang.php";

// Load app modules & check domain
$App = new App(true);
$App->_loadModulesControllers();
try {
    $User = new User();
    
    
    if (!$User->_isLogged())
        header('Location: ' . APP_URL."/login.php");
    
    $leadsApiObj = new LeadsApi();
    
    $l8UserId = $_SESSION['leads_userid'];
    $brandId = $leadsApiObj->getBusinessId();
    $managerId = isset($_SESSION['leads_managerid']) ? $_SESSION['leads_managerid'] : 0;   
    
    $params = [
        'brand_id' => $brandId,
        'user_id' => $l8UserId
    ];
    $responseData = $leadsApiObj->callCurl('get_customer_details', $params);
    $paramgateWayData = [
        'brand_uid' => $brandId
    ];
    $gateWayData = $leadsApiObj->callCurl('get_brand_gateway_setting', $paramgateWayData);
    $paymentSetting = $leadsApiObj->callCurl('get_payment_setting', array("brand_uid" => $brandId));
   // echo "<pre>";
   // print_r($responseData); exit;
    if(isset($gateWayData['gateway']) && in_array('internet_cashbank', $gateWayData['gateway'])){  
        if(isset($responseData['statuscode']) && $responseData['statuscode'] == '200'){    
            if(!empty($paymentSetting['setting']['internet_cashbank_merchant_key']) && !empty($paymentSetting['setting']['internet_cashbank_merchant_sign'])){
                $userData = $responseData['data'];
                

                
                $firstName = trim($userData['first_name']);
                $lastName = trim($userData['last_name']);
                if(empty($lastName)){
                    $pos = strpos($firstName, ' ');
                    if($pos === false){
                        $lastName = $firstName;
                    } else {
                        $nameArr = explode(' ', $firstName);
                        $firstName = $nameArr[0];
                        unset($nameArr[0]);
                        $lastName = implode(' ', $nameArr);
                    }
                }

                if($paymentSetting['setting']['internet_cashbank_payment_mode'] == 'live'){
                    $apiUrl = 'https://internetcashbank.com/api/v1/invoice/create';
                } else {
                    $apiUrl = 'https://sand.internetcashbank.com/api/v1/invoice/create';
                }

                $merchant_key = $paymentSetting['setting']['internet_cashbank_merchant_key'];
                $merchant_sign = $paymentSetting['setting']['internet_cashbank_merchant_sign'];
                $order_nr = 'ORD-'.time();
                $currency = 'USD';
                $amount = (isset($_POST['amount']) && $_POST['amount'] > 0) ? $_POST['amount'] : 250;
                $amount = $amount * 100;
                $payer_email = $userData['email'];
                $payer_name = $firstName;
                $payer_lname = $lastName;
                $lang = 'EN';
                $description = 'Deposit';
                $success_url = APP_URL.'/app/modules/kr-trade/views/int-cashbank_return.php';
                $cancel_url = APP_URL.'/app/modules/kr-trade/views/int-cashbank_return.php';
                $callback_url = 'https://leads8.com/dmn/internetcashbank_callback/';
                // $callback_url = 'https://leads8.com/cashbank_success.php';
                $hash = md5("$merchant_key|$order_nr|$amount|$currency|$merchant_sign");
                $attributes = $brandId."_".$l8UserId."_".$managerId;
                
                $data = [
                    'merchant_key' => $merchant_key,
                    'order_nr' => $order_nr,
                    'amount' => $amount,
                    'currency' => $currency,
                    'payer_email' => $payer_email,
                    'payer_name' => $payer_name,
                    'payer_lname' => $payer_lname,
                    'lang' => $lang,
                    'description' => $description,
                    'success_url' => $success_url,
                    'cancel_url' => $cancel_url,
                    'callback_url' => $callback_url,
                    'hash' => $hash,
                    'attributes' => json_encode($attributes)
                ];
                // echo "<pre>";
                // print_r($data); exit;
                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, $apiUrl);
                curl_setopt($curl, CURLOPT_POST, 1);
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($curl, CURLOPT_HTTPHEADER,[
                    'Content-Type: application/json'
                ]);
                curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
                $response = curl_exec($curl);
                curl_close($curl);

                $responseData = json_decode($response, 1);
                // echo "<pre>";
                // print_r($responseData); exit;
                if(isset($responseData['response']) && $responseData['response'] == true){
                    header('Location: ' . $responseData['invoice_url']);
                    exit;
                } else {
                    $_SESSION['error_message'] = $responseData['error_msg'];
                    header('Location: ' . APP_URL."/dashboard.php");
                }
            } else {
                $_SESSION['error_message'] = 'Sorry, Somethings went wrongs. Please contact to support team.';
                header('Location: ' . APP_URL."/dashboard.php");
            }            
        } else {
            $_SESSION['error_message'] = 'Sorry, User not found. Please try again';
            header('Location:' . APP_URL."/dashboard.php");
            exit;
        }
    } else {
        $_SESSION['error_message'] = 'Sorry, Somethings went wrongs1.';
        header('Location: ' . APP_URL."/dashboard.php");
    }   
    
} catch (Exception $e) {
//    $_SESSION['error_message'] = 'Sorry, Somethings went wrongs2.';
    $_SESSION['error_message'] = $e->getMessage();
    header('Location: ' . APP_URL."/dashboard.php");
}
?>