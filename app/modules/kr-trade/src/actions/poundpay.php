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
    if(isset($gateWayData['gateway']) && in_array('pound_pay', $gateWayData['gateway'])){  
        if(isset($responseData['statuscode']) && $responseData['statuscode'] == '200'){    
            if(!empty($paymentSetting['setting']['pound_pay_api_key'])){
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
                
                $apiUrl = "https://portal.paypound.ltd/api/hosted-pay/payment-request";
                $apiKey = $paymentSetting['setting']['pound_pay_api_key'];
                
                $timeStamp = time();
                $ip_address = $_SERVER['REMOTE_ADDR'];
                $secretToken = md5($apiKey.$brandId.$l8UserId.$timeStamp);

                $dialingCode = str_replace('+', '', $userData['dialing_code']);
                
                $data = [
                    'api_key' => $apiKey,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'address' => (!empty($_POST['address'])) ? $_POST['address'] : $userData['address'],
                    'country' => strtoupper($userData['country_code']),
                    'state' => (!empty($_POST['state'])) ? $_POST['state'] : $userData['state'],
                    'city' => (!empty($_POST['city'])) ? $_POST['city'] : $userData['city'],
                    'zip' => (!empty($_POST['zipcode'])) ? $_POST['zipcode'] : $userData['zipcode'],
                    'ip_address' => $ip_address,
                    'email' => $userData['email'],
                    'country_code' => $dialingCode,
                    'phone_no' => $userData['phone_number'],
                    'amount' => $_POST['amount'],
                    'currency' => 'USD',
                    'customer_order_id' => 'ORD-'.time(),
                    'response_url' => APP_URL.'/app/modules/kr-trade/views/poundpayreturn.php',
                    'webhook_url' => "https://leads8.com/dmn/poundpay_callback/?secret_token=$secretToken&c=$l8UserId&b=$brandId&m=$managerId&stamp=$timeStamp"
                ];
                // echo "<pre>";
                // print_r($data); exit;

                $apiUrl = 'https://portal.paypound.ltd/api/hosted-pay/payment-request';

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
                // print_r($responseData);
                // exit;

                if(isset($responseData['status']) && $responseData['status'] == '3d_redirect'){
                    header('Location:' . $responseData['redirect_3ds_url']);
                    exit;
                } else {
                    $_SESSION['error_message'] = 'Sorry, Somethings went wrongs. Please try again';
                    header('Location:' . APP_URL."/dashboard.php");
                    exit;
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
?>