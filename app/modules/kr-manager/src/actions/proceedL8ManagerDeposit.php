<?php

/**
 * Process payment paypal action
 *
 * @package Krypto
 * @author Ovrley <hello@ovrley.com>
 */
session_start();

require "../../../../../config/config.settings.php";

require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/vendor/autoload.php";

require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/MySQL/MySQL.php";

require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/App/App.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/App/AppModule.php";

require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/User/User.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/Lang/Lang.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoApi.php";

// Load app modules
$App = new App(true);
$App->_loadModulesControllers();

if(empty($_POST)){
    echo json_encode([
            'status' => 'false',
            'message' => 'Parameters missing1'
        ]);
    exit;
}
$postData = $_POST;
if(isset($postData['pass_code']) && $postData['pass_code'] != '2563562'){
    echo json_encode([
            'status' => 'false',
            'message' => 'Invalid token'
        ]);
    exit;
}

$leadsUserId = isset($postData['id_lead']) ? $postData['id_lead'] : 0;
$transactionAmount = (isset($postData['amount'])) ? $postData['amount'] : 0;
$LD8PaymentType = (isset($postData['payment_type'])) ? $postData['payment_type'] : '';
$transactionId = (isset($postData['transaction_id'])) ? $postData['transaction_id'] : '';
$description = (isset($postData['description'])) ? $postData['description'] : '';
$transaction_name = (isset($postData['transaction_name'])) ? $postData['transaction_name'] : '';
$add_transaction_record = (isset($postData['add_transaction_from_crypto'])) ? $postData['add_transaction_from_crypto'] : true;
try {
    $userData = MySQL::querySqlRequest("SELECT * FROM user_krypto WHERE id_leads=:id_leads",
                                [
                                  'id_leads' => $leadsUserId
                                ]);

    if(!empty($userData)){
        
        $leadsApiObj = new LeadsApi();
                
        $paramCurrency = [
            'brand_id' => $leadsApiObj->getBusinessId()
        ];
        $currencyName = 'USD';
        $responseCurrency = $leadsApiObj->callCurl('getBrandDefaultCurrency', $paramCurrency);
        if(isset($responseCurrency['statuscode']) && $responseCurrency['statuscode'] == '200'){
            $currencyName = $responseCurrency['data']['name'];
        }

        // Leads8 customer deposit api called
        $brandCurrency = $currencyName;
        
        $userData = isset($userData[0]) ? $userData[0] : [];

        $User = new User($userData['id_user']);
        $Balance = new Balance($User, $App, 'real');

        $amount = floatval($transactionAmount);
        $payment_type = $transaction_name;
        $description = $description;
        $currency = (strtoupper($brandCurrency) == 'USD') ? strtoupper($brandCurrency).'T' : strtoupper($brandCurrency);
        $datapayment = $transactionId;
        $payment_status = 2;
        $wallet_target = (strtoupper($brandCurrency) == 'USD') ? strtoupper($brandCurrency).'T' : strtoupper($brandCurrency);
        $payment_reference = $transactionId;
        $Balance->_addDeposit(
                $amount,
                $payment_type,
                $description,
                $currency,
                $datapayment,
                $payment_status,
                $wallet_target,
                $payment_reference,
                $LD8PaymentType,
                $add_transaction_record
                );
        echo json_encode([
            'status' => 'true',
            'message' => 'Deposit added'
        ]);
        exit;
    } else {
        echo json_encode([
            'status' => 'false',
            'message' => 'Deposit not added'
        ]);
        exit;
    }   
} catch (Exception $e) {
    echo json_encode([
            'status' => 'false',
            'message' => $e->getMessage()
        ]);
    exit;
}