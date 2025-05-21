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
$refId = (isset($postData['ref_id'])) ? $postData['ref_id'] : 0;
try {
    $userData = MySQL::querySqlRequest("SELECT * FROM user_krypto WHERE id_leads=:id_leads",
                                [
                                  'id_leads' => $leadsUserId
                                ]);

    if(!empty($userData)){
        $userData = isset($userData[0]) ? $userData[0] : [];

        $User = new User($userData['id_user']);
        $Balance = new Balance($User, $App, 'real');

        $test = $Balance->_setCancelWithdraw('2-'.$refId);
        
        echo json_encode([
            'data' => $test,
            'status' => 'true',
            'message' => 'Withdraw cancelled'
        ]);
        exit;
    } else {
        echo json_encode([
            'status' => 'false',
            'message' => 'Withdraw not cancelled'
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