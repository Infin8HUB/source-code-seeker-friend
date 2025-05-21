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
    if(isset($gateWayData['gateway']) && in_array('amlnnode', $gateWayData['gateway'])){  
        if(isset($responseData['statuscode']) && $responseData['statuscode'] == '200'){    
            if(!empty($paymentSetting['setting']['amlnnode_api_key']) && !empty($paymentSetting['setting']['amlnnode_secret_key'])){
                $userData = $responseData['data'];
                
                $timeStamp = time();
                $ip_address = $_SERVER['REMOTE_ADDR'];
                $secretkey = $paymentSetting['setting']['amlnnode_secret_key'];
                $secretToken = md5($secretkey.$brandId.$l8UserId.$timeStamp);
                $str=base64_encode($secretToken."_".$l8UserId."_".$brandId."_".$managerId."_".$timeStamp);

                $data['i'] = $str;
                // $apiKey = '69-SNtNjMP_4FuTaIzXJEj1uPXTmjm71g-nHXe8wAVpwBhj6_bgzruWoJNGLMzL7AxeKHTCvzmxYu_ysdkV5K3PHA';
                $apiKey = $paymentSetting['setting']['amlnnode_api_key'];
                $apiUrl = 'https://api.amlnode.com/api/v1/widget/token';

                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, $apiUrl);
                curl_setopt($curl, CURLOPT_POST, 1);
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($curl, CURLOPT_HTTPHEADER,[
                    'Content-Type: application/json',
                    'Authorization: Bearer '.$apiKey
                ]);
                curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
                $response = curl_exec($curl);
                curl_close($curl);

                $responseData = json_decode($response, 1);
                $token = (isset($responseData['token'])) ? $responseData['token'] : '';

                ?>
				<html>
                    <body>
                        <div id="_apf"></div>
                        <script defer src='https://payment-page.amlnode.com/js/apfs.js'
                               id="_apfs"
                               data-api="https://api.amlnode.com/api/v1/"
                               data-token="<?= $token ?>"
                               data-caller="<?= $data['i'] ?>"
                               data-lang="en"
                               data-target="#_apf">
                        </script>
                    </body>
                </html>
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


