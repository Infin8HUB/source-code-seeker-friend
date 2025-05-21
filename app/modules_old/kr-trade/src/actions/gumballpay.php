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
//    echo "<pre>";
//    print_r($responseData); exit;
    if((isset($gateWayData['gateway']) && in_array('gumballpay', $gateWayData['gateway'])) && ((isset($_SESSION['gumballpay_payment']) && $_SESSION['gumballpay_payment'] == 'enabled') 
                || (isset($_SESSION['leads_managerid']) && $_SESSION['leads_managerid'] > 0))){  
        if(isset($responseData['statuscode']) && $responseData['statuscode'] == '200'){    
            if(!empty($paymentSetting['setting']['gumballpay_group_id']) && !empty($paymentSetting['setting']['gumballpay_merchant_control'])){
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
                
                $groupId = $paymentSetting['setting']['gumballpay_group_id']; 
                $merchantControl = $paymentSetting['setting']['gumballpay_merchant_control'];
                
                $apiUrl = "https://sandbox.gumballpay.com/paynet/api/v2/sale-form/group/".$groupId;
                if(isset($paymentSetting['setting']['gumballpay_payment_mode']) && $paymentSetting['setting']['gumballpay_payment_mode'] == 'live'){
                    $apiUrl = "https://gate.gumballpay.com/paynet/api/v2/sale-form/group/".$groupId;
                }
                
                $stateReqCountry = array('US', 'CA', 'AU');
                
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
                
                $requestFields = array(
                    'client_orderid' => uniqid(), 
                    'order_desc' => 'Deposit', 
                    'first_name' => $firstName, 
                    'last_name' => $lastName,
                    'address1' => (!empty($_POST['address'])) ? $_POST['address'] : $userData['address'], 
                    'city' => (!empty($_POST['city'])) ? $_POST['city'] : $userData['city'], 
//                    'state' => (!empty($_POST['state'])) ? $_POST['state'] : $userData['state'], 
                    'zip_code' => (!empty($_POST['zipcode'])) ? $_POST['zipcode'] : $userData['zipcode'], 
                    'country' => $userData['country_code'], 
                    'phone' => $userData['dialing_code'].$userData['phone_number'],
                    'amount' => isset($_POST['amount']) ? $_POST['amount'] : 100, 
                    'email' => $userData['email'], 
                    'currency' => 'USD', 
                    'ipaddress' => $_SERVER['REMOTE_ADDR'], 
                    'site_url' => APP_URL, 
                    'preferred_language' => 'EN', 
                    'redirect_url' => APP_URL.'/app/modules/kr-trade/views/paynetreturn.php', 
                    'server_callback_url' => 'https://leads8.com/dmn/gumballpay_callback/',
                    'merchant_data' => $brandId."_".$l8UserId."_".$managerId  //"brandId_customerId_managerId", 
                );
                
                if(in_array($userData['country_code'], $stateReqCountry)){
                    $requestFields['state'] = (!empty($_POST['state'])) ? $_POST['state'] : $userData['state'];
                }

                $requestFields['control'] = signPaymentRequest($requestFields, $groupId, $merchantControl);
        //        echo "<pre>";
        //        print_r($requestFields);
                $responseFields = sendRequest($apiUrl, $requestFields);
//                echo "<pre>";
//                print_r($responseFields); exit;
                if(isset($responseFields['type']) && trim($responseFields['type']) == 'async-form-response' && isset($responseFields['redirect-url'])){
                    header('Location: '.$responseFields['redirect-url']);
                } else {
                    $_SESSION['error_message'] = 'Sorry, we are not able to process your payment. Please contact to support.';
                    header('Location: ' . APP_URL."/dashboard.php");
                }
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

/**
 * Executes request
 *
 * @param       string      $url                Url for payment method
 * @param       array       $requestFields      Request data fields
 *
 * @return      array                           Host response fields
 *
 * @throws      RuntimeException                Error while executing request
 */
function sendRequest($url, array $requestFields)
{
    $curl = curl_init($url);

    curl_setopt_array($curl, array
    (
        CURLOPT_HEADER         => 0,
        CURLOPT_USERAGENT      => 'PaynetEasy-Client/1.0',
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_POST           => 1,
        CURLOPT_RETURNTRANSFER => 1
    ));

    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($requestFields));

    $response = curl_exec($curl);

    if(curl_errno($curl))
    {
        $error_message  = 'Error occurred: ' . curl_error($curl);
        $error_code     = curl_errno($curl);
    }
    elseif(curl_getinfo($curl, CURLINFO_HTTP_CODE) != 200)
    {
        $error_code     = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error_message  = "Error occurred. HTTP code: '{$error_code}'";
    }

    curl_close($curl);

    if (!empty($error_message))
    {
        throw new RuntimeException($error_message, $error_code);
    }

    if(empty($response))
    {
        throw new RuntimeException('Host response is empty');
    }

    $responseFields = array();

    parse_str($response, $responseFields);

    return $responseFields;
}

function signString($s, $merchantControl)
{
    return sha1($s . $merchantControl);
}

/**
 * Signs payment (sale/auth/transfer) request
 *
 * @param 	array		$requestFields		request array
 * @param	string		$endpointOrGroupId	endpoint or endpoint group ID
 * @param	string		$merchantControl	merchant control key
 */
function signPaymentRequest($requestFields, $endpointOrGroupId, $merchantControl)
{
    $base = '';
    $base .= $endpointOrGroupId;
    $base .= $requestFields['client_orderid'];
    $base .= $requestFields['amount'] * 100;
    $base .= $requestFields['email'];

    return signString($base, $merchantControl);
}

/**
 * Signs status request
 *
 * @param 	array		$requestFields		request array
 * @param	string		$login			merchant login
 * @param	string		$merchantControl	merchant control key
 */
function signStatusRequest($requestFields, $login, $merchantControl)
{
    $base = '';
    $base .= $login;
    $base .= $requestFields['client_orderid'];
    $base .= $requestFields['orderid'];

    return signString($base, $merchantControl);
}


function signAccountVerificationRequest($requestFields, $endpointOrGroupId, $merchantControl)
{
    $base = '';
    $base .= $endpointOrGroupId;
    $base .= $requestFields['client_orderid'];
    $base .= $requestFields['email'];
    return signString($base, $merchantControl);
}
?>