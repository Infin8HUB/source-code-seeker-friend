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
    if(isset($gateWayData['gateway']) && in_array('neobank', $gateWayData['gateway'])){  
        if(isset($responseData['statuscode']) && $responseData['statuscode'] == '200'){    
            if(!empty($paymentSetting['setting']['neobank_api_key'])){
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
                
                $url = "https://portal.neobanq.app/api/transaction";
                $apiKey = $paymentSetting['setting']['neobank_api_key'];
                
                $time = time();
                $secret = md5($brandId.'_'.$l8UserId.'_'.$time);
                
                $data = [
                    'api_key' => $apiKey,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'address' => (!empty($_POST['address'])) ? $_POST['address'] : $userData['address'],
                    'sulte_apt_no' => $brandId."_".$l8UserId."_".$managerId,
                    'country' => strtoupper($userData['country_code']),
                    'state' => (!empty($_POST['state'])) ? $_POST['state'] : $userData['state'], // if your country US then use only 2 letter state code.
                    'city' => (!empty($_POST['city'])) ? $_POST['city'] : $userData['city'],
                    'zip' => (!empty($_POST['zipcode'])) ? $_POST['zipcode'] : $userData['zipcode'],
                    'ip_address' => $_SERVER['REMOTE_ADDR'],
                    'email' => $userData['email'],
                    'phone_no' => $userData['dialing_code'].$userData['phone_number'],
                    'card_type' => $_POST['cardtype'], // See your card type in list
                    'amount' => $_POST['amount'],
                    'currency' => 'USD',
                    'card_no' => str_replace(' ', '', $_POST['cardNumber']),
                    'ccExpiryMonth' => $_POST['exp_month'],
                    'ccExpiryYear' => $_POST['exp_year'],
                    'cvvNumber' => $_POST['cvv'],
                    'response_url' => APP_URL.'/app/modules/kr-trade/views/neoreturn.php',
                    'webhook_url' => 'https://leads8.com/dmn/neobank_callback/?t='.$time.'&secret='.$secret,
                ];
                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, $url);
                curl_setopt($curl, CURLOPT_POST, 1);
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($curl, CURLOPT_HTTPHEADER,[
                    'Content-Type: application/json'
                ]);
                curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
                $response = curl_exec($curl);
                curl_close($curl);

                $responseData = json_decode($response);
                
                echo $response;
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