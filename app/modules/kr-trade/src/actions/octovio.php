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
    if(isset($gateWayData['gateway']) && in_array('octovio', $gateWayData['gateway'])){  
        if(isset($responseData['statuscode']) && $responseData['statuscode'] == '200'){    
            if(!empty($paymentSetting['setting']['octovio_merchant_key']) && !empty($paymentSetting['setting']['octovio_hash_key'])){
                $userData = $responseData['data'];
                
                $timeStamp = time();
                $ip_address = $_SERVER['REMOTE_ADDR'];
                $hashkey = $paymentSetting['setting']['octovio_hash_key'];
                $secretToken = md5($hashkey.$brandId.$l8UserId.$timeStamp);
                $str=$secretToken."_".$l8UserId."_".$brandId."_".$managerId."_".$timeStamp;
                
                $merchantID         = $paymentSetting['setting']['octovio_merchant_key'];        
                //mandatory!
                $url_redirect       = APP_URL.'/app/modules/kr-trade/views/octovioreturn.php';;  
                //optional
                $notification_url   = "https://leads8.com/dmn/octovio_callback/?custom_data=$str";
                //optional
                $trans_comment      = "Deposit your account";                       
                //optional
                $trans_refNum       = getToken(12);             
                //optional
                $trans_installments = "1";                      
                //optional
                $trans_amount       = $_POST['amount'];                  
                //mandatory!
                $trans_currency     = "USD";                    
                //mandatory!
                $disp_paymentType   = "CC";                     
                //mandatory!
                $disp_payFor        = "Purchase";               
                //optional
                $disp_recurring     = "0";                      
                //optional
                $disp_lng           = "en-us";                  
                //optional
                $disp_mobile        = "auto";                   
                //optional
                $PersonalHashKey    = $paymentSetting['setting']['octovio_hash_key'];

                $retSignature = $merchantID . $url_redirect . $notification_url . $trans_comment . $trans_refNum . 
                $trans_installments . $trans_amount . $trans_currency . $disp_paymentType .
                $disp_payFor . $disp_recurring . $disp_lng . $disp_mobile . $PersonalHashKey;
                
                $signature = base64_encode(hash("sha256", $retSignature, true)); 

                ?>
				<form method="GET" action="https://uiservices.octoviopay.com/hosted/default.aspx?" name="paymentform"> 
                    <input type="hidden" readonly name="merchantID" value="<?php echo($merchantID); ?>" />
                    <input type="hidden" readonly name="url_redirect" value="<?php echo($url_redirect); ?>" />
                    <input type="hidden" readonly name="notification_url" value="<?php echo($notification_url); ?>" />
                    <input type="hidden" readonly name="trans_comment" value="<?php echo($trans_comment); ?>" />
                    <input type="hidden" readonly name="trans_refNum" value="<?php echo($trans_refNum); ?>" />
                    <input type="hidden" readonly name="trans_installments" value="<?php echo($trans_installments); ?>" />
                    <input type="hidden" readonly name="trans_amount" value="<?php echo($trans_amount); ?>" />
                    <input type="hidden" readonly name="trans_currency" value="<?php echo($trans_currency); ?>" />
                    <input type="hidden" readonly name="disp_paymentType" value="<?php echo($disp_paymentType); ?>" />
                    <input type="hidden" readonly name="disp_payFor" value="<?php echo($disp_payFor); ?>" /> 
                    <input type="hidden" readonly name="disp_recurring" value="<?php echo($disp_recurring); ?>" />
                    <input type="hidden" readonly name="disp_lng" value="<?php echo($disp_lng); ?>" /> <br>
                    <input type="hidden" readonly name="disp_mobile" value="<?php echo($disp_mobile); ?>" />
                    <input type="hidden" readonly name="signature" value="<?php echo($signature); ?>" />
                    
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

function getToken($length) {
     $token = "";
     $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
     $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
     $codeAlphabet.= "0123456789";
     $max = strlen($codeAlphabet);
 // edited
     for ($i=0; $i < $length; $i++) 
        $token .= $codeAlphabet[random_int(0, $max-1)];     
     return uniqid($token);
}//
?>


