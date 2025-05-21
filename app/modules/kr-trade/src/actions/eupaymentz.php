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
    if(isset($gateWayData['gateway']) && in_array('eupaymentz', $gateWayData['gateway'])){  
        if(isset($responseData['statuscode']) && $responseData['statuscode'] == '200'){    
            if(!empty($paymentSetting['setting']['eupaymentz_account_id'])){
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
                } else {
                	$address = $userData['address'];
                	$city = $userData['city'];
                	$state = $userData['state'];
                	$zipcode = $userData['zipcode'];
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
                //echo "<pre>";print_r($paymentSetting);exit;
                $apiUrl = "https://ts.secure1gateway.com/api/v2/processTx";
                $account_id = $paymentSetting['setting']['eupaymentz_account_id'];                
                $account_password = $paymentSetting['setting']['eupaymentz_account_password'];
                $account_passphrase = $paymentSetting['setting']['eupaymentz_account_passphrase'];
                $timeStamp = time();
                $ip_address = $_SERVER['REMOTE_ADDR'];
                $dialingCode = str_replace('+', '', $userData['dialing_code']);
                $country = strtoupper($userData['country_code']);
                $email = $userData['email'];
                $phone_no = $userData['phone_number'];
                $amount = $_POST['amount'];
                $currency = 'USD';
                $customer_order_id = 'ORD-'.time();
                $account_sha = hash('sha256', $account_passphrase.$amount.$account_id.$email.$ip_address);
                $successUrl = APP_URL.'/app/modules/kr-trade/views/eupaymentz.php';
                $notifyUrl = "https://leads8.com/dmn/eupaymentz_callback/";

                $data = [
                    'account_id' => $account_id,
                    'account_password' => $account_password,
                    'account_sha' => $account_sha,
                    'action_type' => "payment",
                    'account_gateway' => "1",
                    'cust_billing_first_name' => $firstName,
                    'cust_billing_last_name' => $lastName,
                    'cust_billing_address' => $address,
                    'cust_billing_country' => $country,
                    'cust_billing_state' => $state,
                    'cust_billing_city' => $city,
                    'cust_billing_zipcode' => $zipcode,
                    'customer_ip' => $ip_address,
                    'cust_email' => $email,
                    'cust_billing_phone' => $phone_no,
                    'transac_amount' => $amount,
                    'transac_products_name' => "Deposit",
                    'transac_currency_code' => $currency,
                    'merchant_payment_id' => $customer_order_id,
                    'merchant_data1' => $l8UserId,
                    'merchant_data2' => $brandId,
                    'merchant_data3' => $managerId,
                    'merchant_url_return' => $successUrl,
                    'merchant_url_callback' => $notifyUrl
                ];

                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, $apiUrl);
                curl_setopt($curl, CURLOPT_POST, 1);
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($curl, CURLOPT_HTTPHEADER,[
                    'Content-Type: application/json',
                ]);
                curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
                $response = curl_exec($curl);
                curl_close($curl);

                $responseData = json_decode($response, 1);
                //echo "<pre>";print_r($responseData);exit;
                if(isset($responseData['status']) && $responseData['status'] == 'fail'){
                    $_SESSION['error_message'] = 'Something went wrong. Please try again later.';
                    header('Location:' . APP_URL."/dashboard.php");
                    exit;
                } else {
                    $responseData = $responseData['data'];
                    if(isset($responseData['UrlToRedirect']) && $responseData['UrlToRedirect'] != ''){
                        //$params = http_build_query($responseData['UrlToRedirecPostedParameters'][0]);
                        $params = $responseData['UrlToRedirecPostedParameters'][0]['key']."=".$responseData['UrlToRedirecPostedParameters'][0]['value'];
                        $redirectUrl = $responseData['UrlToRedirect']."?".$params;
                        header('Location:' . $redirectUrl);
                    } else {
                        $_SESSION['error_message'] = $responseData['resp_trans_description_status'];
                        header('Location:' . APP_URL."/dashboard.php");
                        exit;
                    }
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


