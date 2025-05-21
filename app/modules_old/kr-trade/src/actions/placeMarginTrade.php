<?php

/**
 * Load chart data
 *
 * @package Krypto
 * @author Ovrley <hello@ovrley.com>
 */
session_start();

require "../../../../../config/config.settings.php";

require $_SERVER['DOCUMENT_ROOT'] . FILE_PATH . "/vendor/autoload.php";

require $_SERVER['DOCUMENT_ROOT'] . FILE_PATH . "/app/src/MySQL/MySQL.php";

require $_SERVER['DOCUMENT_ROOT'] . FILE_PATH . "/app/src/App/App.php";
require $_SERVER['DOCUMENT_ROOT'] . FILE_PATH . "/app/src/App/AppModule.php";

require $_SERVER['DOCUMENT_ROOT'] . FILE_PATH . "/app/src/User/User.php";

require $_SERVER['DOCUMENT_ROOT'] . FILE_PATH . "/app/src/CryptoApi/CryptoOrder.php";
require $_SERVER['DOCUMENT_ROOT'] . FILE_PATH . "/app/src/CryptoApi/CryptoNotification.php";
require $_SERVER['DOCUMENT_ROOT'] . FILE_PATH . "/app/src/CryptoApi/CryptoIndicators.php";
require $_SERVER['DOCUMENT_ROOT'] . FILE_PATH . "/app/src/CryptoApi/CryptoGraph.php";
require $_SERVER['DOCUMENT_ROOT'] . FILE_PATH . "/app/src/CryptoApi/CryptoHisto.php";
require $_SERVER['DOCUMENT_ROOT'] . FILE_PATH . "/app/src/CryptoApi/CryptoCoin.php";
require $_SERVER['DOCUMENT_ROOT'] . FILE_PATH . "/app/src/CryptoApi/CryptoApi.php";

// Load app modules
$App = new App(true);
$App->_loadModulesControllers();

try {

    // Check if user is logged
    $User = new User();
    if (!$User->_isLogged()) {
        throw new Exception("Error : User is not logged", 1);
    }
    
//    if($_SESSION['is_manager_login'] != true){
//        throw new Exception("Error : You are not able to trade using margin", 1);
//    }
    
    $thirdPartyChoosen = null;
    $Trade = new Trade($User, $App);
    $CurrentBalance = null;
    
    if (empty($_POST) || !isset($_POST['orderid']))
        throw new Exception("Permission denied", 1);
    
    $orderId = $_POST['orderid'];
//    $orderId = 33;
    
    
    if ($App->_getIdentityEnabled())
        $Identity = new Identity($User);
    
    if ($App->_hiddenThirdpartyActive()) {
        
        $Balance = new Balance($User, $App);
        $CurrentBalance = $Balance->_getCurrentBalance();
        
        if ($CurrentBalance->_isPractice() && !$App->_getTradingEnablePracticeAccount())
            throw new Exception("Real account is not enable");
        
        if (!$CurrentBalance->_isPractice() && !$App->_getTradingEnableRealAccount())
            throw new Exception("Real account is not enable");
        
        if ($CurrentBalance->_getBalanceType() == "real" && $App->_getIdentityEnabled() && $App->_getIdentityTradeBlocked() && !$Identity->_identityVerified()) {
            die(json_encode([
                'error' => 9,
                'msg' => 'Identity not verified'
            ]));
        }
        
        $orderInfo = $CurrentBalance->_getOrderInfos($orderId);
        
        $thirdPartyChoosen = $Trade->_getThirdParty($App->_hiddenThirdpartyServiceCfg()[strtolower($orderInfo['thirdparty_internal_order'])])[strtolower($orderInfo['thirdparty_internal_order'])];
        
        if($orderInfo['is_sold'] == 1 || $orderInfo['margin_val'] <= 1){
            throw new Exception("Error : Already sold or you has not been selected margin for this trade", 1);
        }
        
        $sellCoinBalance = $CurrentBalance->getBalanceByCoin($orderInfo['symbol_internal_order']);        
        if($sellCoinBalance <= 0){
            throw new Exception("Error : Insuffcient funds (0 ".$orderInfo['symbol_internal_order'].")", 1);
        }
                
        $coinPair = $orderInfo['symbol_internal_order']."/".$orderInfo['to_internal_order'];
        $PriceInfos = (isset($_POST['custom_price']) && $_POST['custom_price'] > 0) ? $_POST['custom_price'] : $thirdPartyChoosen->_getPriceTrade($coinPair, 1);
        
        if($sellCoinBalance >= $orderInfo['usd_amount_internal_order']){
            $oldTradeAmount = ($orderInfo['amount_internal_order'] / $orderInfo['usd_amount_internal_order']) * $orderInfo['usd_amount_internal_order'];
            $currentTradeAmount = $PriceInfos * $orderInfo['usd_amount_internal_order'];
        } else {
            $oldTradeAmount = ($orderInfo['amount_internal_order'] / $orderInfo['usd_amount_internal_order']) * $sellCoinBalance;
            $currentTradeAmount = $PriceInfos * $sellCoinBalance;
        }
        
        $buyCoinBalance = $CurrentBalance->getBalanceByCoin($orderInfo['to_internal_order']);
        
//        $oldTradeAmount = $oldTradeAmount + 50;
        
        $marginAmount = ($currentTradeAmount - $oldTradeAmount) * $orderInfo['margin_val'];
        $totalAmount = $buyCoinBalance + $currentTradeAmount + $marginAmount;
        $profit = $currentTradeAmount + $marginAmount;
        
//        echo '$buyCoinBalance:' . (float)$buyCoinBalance;
//        echo "<br>";
//        echo '$oldTradeAmount:' . $oldTradeAmount;
//        echo "<br>";
//        echo '$currentTradeAmount:' . $currentTradeAmount;
//        echo "<br>";
//        echo "Margin: ".$marginAmount = ($currentTradeAmount - $oldTradeAmount) * $orderInfo['margin_val'];
//        $totalAmount = $buyCoinBalance + $currentTradeAmount + $marginAmount;
//        echo "<br>";
//        echo "Total: ".$totalAmount;
//        echo "<br>";
//        echo "Profit1: ".($currentTradeAmount + $marginAmount);
//        echo "<br>";
//        echo "TEst";
//        print_r($orderInfo);
        
        $usd_amount_internal_order = $currentTradeAmount + $marginAmount;
        
//        if($totalAmount > 0){
//            $usd_amount_internal_order = $currentTradeAmount + $marginAmount;
//        } else {
//            $usd_amount_internal_order = $buyCoinBalance * -1;
//        }
        
        $order = ['id' => '0'];
        if($marginAmount >= 0) {
            $marginText = '(<span style="color:green;">Won x'.(int)$orderInfo['margin_val'].'</span>)';
        } else {
            $marginText = '(<span style="color:red;">Loss x'.(int)$orderInfo['margin_val'].'</span>)';
        } 
        
        $refOrderId = $orderInfo['id_internal_order'];
                
        $CurrentBalance->_saveOrder($thirdPartyChoosen, $orderInfo['usd_amount_internal_order'], $usd_amount_internal_order, 'SELL', $orderInfo['symbol_internal_order'], $order, $orderInfo['to_internal_order'], 'market', null, $orderInfo['margin_val'], $marginText, $refOrderId, $marginAmount);
        
        $r = MySQL::execSqlRequest("UPDATE internal_order_krypto SET
                                is_sold=:is_sold
                                WHERE id_internal_order=:id_internal_order AND id_user=:id_user AND id_balance=:id_balance",
                                [
                                  'id_internal_order' => $orderInfo['id_internal_order'],
                                  'id_user' => $orderInfo['id_user'],
                                  'id_balance' => $orderInfo['id_balance'],
                                  'is_sold' => 1
                                ]);
        
        if(!$r) throw new Exception('Error when updating order : '.$orderInfo['id_internal_order'].' - user : '.$orderInfo['id_user'].' - balance : '.$orderInfo['id_balance'].', SQL Error');
        
        die(json_encode([
            'error' => 0,
            'msg' => 'Success !'
        ]));
        
    } else {
        throw new Exception("Sorry, somethings went wrong.", 1);
    }
    
} catch (\Exception $e) {
    die(json_encode([
        'error' => 1,
        'msg' => $e->getMessage()
    ]));
}