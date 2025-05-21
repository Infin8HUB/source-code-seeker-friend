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
    //echo "<pre>";
    //print_r($paymentSetting);
    //print_r($responseData); exit;
    if(isset($gateWayData['gateway']) && in_array('ipasspay', $gateWayData['gateway'])){  
        if(isset($responseData['statuscode']) && $responseData['statuscode'] == '200'){    
            if(!empty($paymentSetting['setting']['ipasspay_merchant_key']) && !empty($paymentSetting['setting']['ipasspay_app_id']) && !empty($paymentSetting['setting']['ipasspay_api_secret'])){
                $userData = $responseData['data'];
                
                //update here customer address etc //
                if(!empty($_POST['address']) || !empty($_POST['city']) || !empty($_POST['state']) || !empty($_POST['zipcode'])){
                    $address = (!empty($_POST['address'])) ? $_POST['address'] : $userData['address'];
                    $city = (!empty($_POST['city'])) ? $_POST['city'] : $userData['city'];
                    $state = (!empty($_POST['state'])) ? $_POST['state'] : $userData['state'];
                    $zipcode = (!empty($_POST['zipcode'])) ? $_POST['zipcode'] : $userData['zipcode'];
                    
                    $updateParam = array(
                        'brand_id' => $brandId,
                        'user_id' => $l8UserId,
                        'first_name' => $userData['first_name'],
                        'last_name' => $userData['last_name'],
                        'email' => $userData['email'],
                        'phone_number' => $userData['phone_number'],
                        'country_code' => $userData['country_code'],
                        'address' => $address,
                        'zipcode' => $zipcode,
                        'city' => $city,
                        'state' => $state,
                    );
                    $updateResponseData = $leadsApiObj->callCurl('update_customer_profile_v1', $updateParam);
                }
                
                $apiUrl = "https://sandbox.service.ipasspay.biz/gateway/Index/checkout";
                if(isset($paymentSetting['setting']['ipasspay_payment_mode']) && $paymentSetting['setting']['ipasspay_payment_mode'] == 'live'){
                    $apiUrl = "https://service.ipasspay.biz/gateway/Index/checkout";
                }
                
                

                $api_secret = $paymentSetting['setting']['ipasspay_api_secret'];
                $requestFields['merchant_id'] = $paymentSetting['setting']['ipasspay_merchant_key'];
                $requestFields['app_id'] = $paymentSetting['setting']['ipasspay_app_id'];
                $requestFields['version'] = '2.0';

                $requestFields['order_no'] = uniqid();
                $order_amount = isset($_POST['amount']) ? $_POST['amount'] : 250.00;
                $order_amount = number_format($order_amount,2);
                $order_items = array("goods_name" => "DEPOSIT","quality" => 1,"price" => $order_amount);
                $requestFields['order_currency'] = 'USD'; //USD,CNY
                $requestFields['order_amount'] = $order_amount;
                $requestFields['order_items'] = json_encode($order_items);
                $requestFields['custom_data'] = $brandId."_".$l8UserId."_".$managerId;

                $requestFields['source_url'] = APP_URL;
                $requestFields['syn_notify_url'] = APP_URL.'/app/modules/kr-trade/views/ipasspay_return.php';
                $requestFields['asyn_notify_url'] = 'https://leads8.com/dmn/ipasspay_callback/';
                $requestFields['signature'] = hash('sha256', $requestFields['merchant_id'] . $requestFields['app_id']  . $requestFields['order_no'] . $requestFields['order_amount'] . $requestFields['order_currency'] . $api_secret);

                $stateReqCountry = array('US', 'CA', 'AU', 'JP');
                
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
                $requestFields['bill_email'] = $userData['email'];
                $requestFields['bill_firstname'] = $firstName;
                $requestFields['bill_lastname'] = $lastName;
                $requestFields['bill_phone'] = $userData['dialing_code'].$userData['phone_number'];
                $requestFields['bill_country'] = $userData['country_code'];
                $requestFields['bill_state'] = (!empty($_POST['state'])) ? $_POST['state'] : $userData['state'];;
                $requestFields['bill_city'] = (!empty($_POST['city'])) ? $_POST['city'] : $userData['city'];
                $requestFields['bill_street'] = (!empty($_POST['address'])) ? $_POST['address'] : $userData['address'];
                $requestFields['bill_zip'] = (!empty($_POST['zipcode'])) ? $_POST['zipcode'] : $userData['zipcode'];

                //echo "<pre>";print_r($requestFields); exit;
                process_host($requestFields, $apiUrl);
                
            }  else {
                $_SESSION['error_message'] = 'Sorry, Payment Keys Not Found. Please try again';
                header('Location:' . APP_URL."/dashboard.php");
                exit;
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

function process_host($curlPost,$gateway_url) {
    $curlPost = http_build_query($curlPost);
    $payment_url = $gateway_url."?".$curlPost;

    /**
     * Please let your customer to visit this payment_url.
     * So that the customer can redirect to iPasspay's checkout page to input the card info and checkout in a safe way.
     * More information please refer to https://www.apihome.dev/ipasspay.biz/en-us/#api-payment-gatewayHost
     **/
    //echo $payment_url;exit;

    //Header("HTTP/1.1 303 See Other");
    Header("Location: $payment_url");
    //exit;

}
?>