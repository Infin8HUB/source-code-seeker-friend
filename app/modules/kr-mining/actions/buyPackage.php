<?php
session_start();

require "../../../../config/config.settings.php";

require $_SERVER['DOCUMENT_ROOT'] . FILE_PATH . "/vendor/autoload.php";

require $_SERVER['DOCUMENT_ROOT'] . FILE_PATH . "/app/src/MySQL/MySQL.php";

require $_SERVER['DOCUMENT_ROOT'] . FILE_PATH . "/app/src/User/User.php";
require $_SERVER['DOCUMENT_ROOT'] . FILE_PATH . "/app/src/Lang/Lang.php";

require $_SERVER['DOCUMENT_ROOT'] . FILE_PATH . "/app/src/App/App.php";
require $_SERVER['DOCUMENT_ROOT'] . FILE_PATH . "/app/src/App/AppModule.php";

require $_SERVER['DOCUMENT_ROOT'] . FILE_PATH . "/app/src/CryptoApi/CryptoIndicators.php";
require $_SERVER['DOCUMENT_ROOT'] . FILE_PATH . "/app/src/CryptoApi/CryptoGraph.php";
require $_SERVER['DOCUMENT_ROOT'] . FILE_PATH . "/app/src/CryptoApi/CryptoHisto.php";
require $_SERVER['DOCUMENT_ROOT'] . FILE_PATH . "/app/src/CryptoApi/CryptoCoin.php";
require $_SERVER['DOCUMENT_ROOT'] . FILE_PATH . "/app/src/CryptoApi/CryptoApi.php";

$App = new App(true);
$App->_loadModulesControllers();

try {
    $User = new User();
    if (!$User->_isLogged())
        die('Error : User not logged');
    //if(!$User->_isAdmin() && !$User->_isManager()) throw new Exception("Permission denied", 1);

    $Lang = new Lang($User->_getLang(), $App);

    if (empty($_GET) || !isset($_GET['id']))
        throw new Exception("Permission denied", 1);

    
    $Balance = new Balance($User, $App, 'real');
    //$Balance = $Balance->_getCurrentBalance(); 
    $userUSDTBalance = $Balance->getBalanceByCoin("USDT"); 
    
    $leadsApiObj = new LeadsApi();       
    $param = [
        'brand_uid' => $leadsApiObj->getBusinessId(),
        'product_id' => $_GET['id']
    ];
    $responsePackages = $leadsApiObj->callCurl('productList', $param);
    //echo "<pre>";print_r($responsePackages);exit;
    if($responsePackages['statuscode'] != '200' || empty($responsePackages['products'])) 
        throw new Exception("Error : ".$responsePackages['message'], 1);
    
    $paramCurrency = [
        'brand_id' => $leadsApiObj->getBusinessId()
    ];
    $currencyName = 'USD';
    $responseCurrency = $leadsApiObj->callCurl('getBrandDefaultCurrency', $paramCurrency);
    if(isset($responseCurrency['statuscode']) && $responseCurrency['statuscode'] == '200'){
        $currencyName = $responseCurrency['data']['name'];
    }
    
    $product = $responsePackages['products'][0];
    
    $fdate = date('Y-m-d');
    $tdate = $product['exp_date'];
    $datetime1 = new DateTime($fdate);
    $datetime2 = new DateTime($tdate);
    $interval = $datetime1->diff($datetime2);
    $days = $interval->format('%a');//now do whatever you like with $days
    $revenue = number_format(($product['price']*$product['max_revenue_per_click'])/100,2);
    
    if($userUSDTBalance >= $product['price']){
        //if(isset($product['exp_date']) && $product['exp_date'] != '' && $product['exp_date'] != '0000-00-00' && strtotime($product['exp_date']) >= strtotime(date("Y-m-d"))){
        if(isset($product['exp_days']) && $product['exp_days'] != '' && $product['exp_days'] > 0){
            $response = $Balance->_saveMining($product['price'],$currencyName,$product['pId']);
            if(isset($response['statuscode']) && $response['statuscode'] == '200'){
                die(json_encode([
                    'success' => 1,
                    'msg' => "Your product has been successfully placed."
                ]));
            } else {
                throw new Exception("Error : Product order has not been successfully placed.", 1);
            }
             
        } else {
            throw new Exception("Error : Product is expired.", 1);
        }
        
    } else {
        throw new Exception("Error : You have not sufficient balance.", 1);
    }
} catch (Exception $e) {
    die(json_encode([
        'error' => 1,
        'msg' => $e->getMessage()
    ]));
}
?>

