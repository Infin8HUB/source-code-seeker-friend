<?php

/**
 * Load chart data
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

require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoOrder.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoNotification.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoIndicators.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoGraph.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoHisto.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoCoin.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoApi.php";

// Load app modules
$App = new App(true);
$App->_loadModulesControllers();

try {
    
    if(!$_SESSION['is_manager_login']) throw new Exception("Permission denied", 1);

    // Check if user is logged
    $User = new User();
    if (!$User->_isLogged()) {
        throw new Exception("Error : User is not logged", 1);
    }

    $thirdPartyChoosen = null;
    $Trade = new Trade($User, $App);
    $CurrentBalance = null;

    if(empty($_POST) || !isset($_POST['trans_id']) || !isset($_POST['type'])) throw new Exception("Permission denied", 1);
    if(!in_array($_POST['type'], array('deposit', 'withdraw'))) throw new Exception("Something went wrongs", 1);

    if($App->_getIdentityEnabled()) $Identity = new Identity($User);

    if($App->_hiddenThirdpartyActive()){

      $Balance = new Balance($User, $App);
      $CurrentBalance = $Balance->_getCurrentBalance();

      $ishide = $CurrentBalance->_hideShowTransaction($_POST['trans_id'], $_POST['type']);
      
      if($ishide == -1){
          die(json_encode([
            'error' => 1,
            'msg' => 'Something went wrongs'
          ]));
      }

    }

    die(json_encode([
      'error' => 0,
      'msg' => 'Success !',
        'is_hide' => $ishide
    ]));



} catch (\Exception $e) {
    die(json_encode([
    'error' => 1,
    'msg' => $e->getMessage()
  ]));
}

?>
