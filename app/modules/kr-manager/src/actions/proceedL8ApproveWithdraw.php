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
            'message' => 'Parameters missing'
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
$amount = isset($postData['amount']) ? $postData['amount'] : 0;
$symbol = isset($postData['symbol']) ? $postData['symbol'] : 0;
$fees = isset($postData['fees']) ? $postData['fees'] : 0;
$description = isset($postData['description']) ? $postData['description'] : '';
$payment_type = isset($postData['payment_type']) ? $postData['payment_type'] : 'withdraw';

try {
    $userData = MySQL::querySqlRequest("SELECT * FROM user_krypto WHERE id_leads=:id_leads",
                                [
                                  'id_leads' => $leadsUserId
                                ]);

    if(!empty($userData)){
        $userData = isset($userData[0]) ? $userData[0] : [];

        $User = new User($userData['id_user']);
        $Balance = new Balance($User, $App, 'real');
        if($postData['payment_type'] == 'chargeback'){
            $WithdrawReference = $postData['transaction_id'];
        } else {
            $WithdrawReference = $Balance->_generateWithdrawReference();
        }
        $r = MySQL::insertSqlRequest("INSERT INTO widthdraw_history_krypto (id_user, id_balance, amount_widthdraw_history, date_widthdraw_history, status_widthdraw_history, paypal_widthdraw_history, token_widthdraw_history, description_widthdraw_history, symbol_widthdraw_history, method_widthdraw_history, fees_widthdraw_history, ref_widthdraw_history, type_history, is_hide)
                                VALUES (:id_user, :id_balance, :amount_widthdraw_history, :date_widthdraw_history, :status_widthdraw_history, :paypal_widthdraw_history, :token_widthdraw_history, :description_widthdraw_history, :symbol_widthdraw_history, :method_widthdraw_history, :fees_widthdraw_history, :ref_widthdraw_history, :type_history, :is_hide)",
                                [
                                  'id_user' => $Balance->_getUser()->_getUserID(),
                                  'id_balance' => $Balance->_getBalanceID(),
                                  'amount_widthdraw_history' => $amount,
                                  'date_widthdraw_history' => time(),
                                  'status_widthdraw_history' => 0,
                                  'paypal_widthdraw_history' => '',
                                  'token_widthdraw_history' => '',
                                  'description_widthdraw_history' => ucfirst($payment_type).'-'.$description,
                                  'symbol_widthdraw_history' => $symbol,
                                  'method_widthdraw_history' => 0,
                                  'fees_widthdraw_history' => $fees,
                                  'ref_widthdraw_history' => $WithdrawReference,
                                    'type_history' => $payment_type,
                                    'is_hide' => 0
                                ]);
        if(!$r) throw new Exception("Error : Fail to create widthdraw request (please contact the support)", 1);
        
        $refId = $r;
        $Balance->_setDoneWithdraw('2-'.$refId);
        
        echo json_encode([
            'status' => 'true',
            'message' => ucfirst($payment_type).' approved'
        ]);
        exit;
    } else {
        echo json_encode([
            'status' => 'false',
            'message' => ucfirst($payment_type).' not approved'
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