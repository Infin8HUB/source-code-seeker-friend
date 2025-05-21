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
    if(isset($gateWayData['gateway']) && in_array('syspg', $gateWayData['gateway'])){  
        if(isset($responseData['statuscode']) && $responseData['statuscode'] == '200'){    
            if(!empty($paymentSetting['setting']['syspg_app_token'])){
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
                
                $apiUrl = "https://portal.paypound.ltd/api/hosted-pay/payment-request";
                $apiToken = $paymentSetting['setting']['syspg_app_token'];                
                $timeStamp = time();
                $ip_address = $_SERVER['REMOTE_ADDR'];
                $secretToken = md5($apiToken.$brandId.$l8UserId.$timeStamp);
                $dialingCode = str_replace('+', '', $userData['dialing_code']);
                $country = strtoupper($userData['country_code']);
                $email = $userData['email'];
                $phone_no = $userData['phone_number'];
                $amount = $_POST['amount'];
                $currency = 'USD';
                $customer_order_id = 'ORD-'.time();
                $successUrl = APP_URL.'/app/modules/kr-trade/views/syspgreturn.php';
                $errorUrl = APP_URL.'/app/modules/kr-trade/views/syspgreturn.php';
                $notifyUrl = "https://leads8.com/dmn/syspg_callback/?secret_token=$secretToken&c=$l8UserId&b=$brandId&m=$managerId&stamp=$timeStamp"

                ?>
                <form method="post" action="https://my.syspg.net/paymentflow.do" name="paymentform">
				<input type="hidden" name="ccholder" value="<?= $firstName ?>"/>
				<input type="hidden" name="ccholder_lname" value="<?= $lastName ?>"/>
				<input type="hidden" name="bill_street_1" value="<?= $address ?>"/>
				<input type="hidden" name="bill_street_2" value="<?= $address ?>"/>
				<input type="hidden" name="bill_city" value="<?= $city ?>"/>
				<input type="hidden" name="bill_state" value="<?= $state ?>"/>
				<input type="hidden" name="bill_country" value="<?= $country ?>"/>
				<input type="hidden" name="bill_zip" value="<?= $zipcode ?>"/>
				<input type="hidden" name="bill_phone" value="<?= $dialingCode.$phone_no ?>"/>
				<input type="hidden" name="email" value="<?= $email ?>"/>
				<input type="hidden" name="price" value="<?= $amount ?>"/>
				<input type="hidden" name="curr" value="<?= $currency ?>"/>
				<input type="hidden" name="product_name" value="Deposit"/>
				<input type="hidden" name="id_order" value="<?= customer_order_id ?>"/>
				<input type="hidden" name="notes" value="Deposit account"/>
				<input type="hidden" name="client_ip" value="<?= $ip_address ?>"/>
				<input type="hidden" name="source_url" value="<?php echo (isset($_SERVER["HTTPS"])?'https://':'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']);?>"/>
				<input type="hidden" name="notify_url" value="<?= $notifyUrl ?>"/>
				<input type="hidden" name="success_url" value="<?= $successUrl ?>"/>
				<input type="hidden" name="error_url" value="<?= $errorUrl ?>"/>
				<input type="hidden" name="mer_no" value="112888"/>
				<input type="hidden" name="ter_no" value="112888001"/>
				<input type="hidden" name="source" value="sCheckout"/>
				<input type="hidden" name="cardsend" value="CHECKOUT"/>
				<input type="hidden" name="action" value="product"/>
				<input type="hidden" name="api_type" value="1"/>
				<input type="hidden" name="api_token" value="<?= $apiToken ?>"/>
				<input type="hidden" name="trans_type" value="sales"/>
				<input type="hidden" name="trans_model" value="M"/>
				<input type="hidden" name="encryption_mode" value="SHA256"/>
				<input type="hidden" name="character_set" value="UTF8"/>
				<input type="hidden" name="language" value="EN"/>
				</form>

				<script>document.paymentform.submit();</script>
                <?php
                                
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


