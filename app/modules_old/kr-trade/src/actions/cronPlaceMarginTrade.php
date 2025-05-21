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

    $orderData = MySQL::querySqlRequest("SELECT * FROM internal_order_krypto WHERE side_internal_order=:side_internal_order AND is_sold=:is_sold AND margin_val>:margin_val ORDER BY RAND() LIMIT 10", [
                    'is_sold' => '0',
                    'margin_val' => 1,
                    'side_internal_order' => 'BUY'
        ]);
    
    if(!empty($orderData)){
        
        $thirdPartyChoosen = null;
        
        foreach ($orderData as $orderInfo){
            $userId = $orderInfo['id_user'];
            
            $User = new User($userId);
            $Trade = new Trade($User, $App);
            $CurrentBalance = null;
            
            $Balance = new Balance($User, $App);
            $CurrentBalance = $Balance->_getCurrentBalance();
            if ($CurrentBalance->_isPractice() && !$App->_getTradingEnablePracticeAccount())
                continue;
            
            $thirdPartyChoosen = $Trade->_getThirdParty($App->_hiddenThirdpartyServiceCfg()[strtolower($orderInfo['thirdparty_internal_order'])])[strtolower($orderInfo['thirdparty_internal_order'])];
            
            $sellCoinBalance = $CurrentBalance->getBalanceByCoin($orderInfo['symbol_internal_order']);  
            if($sellCoinBalance <= 0){
                continue;
            }
            $coinPair = $orderInfo['symbol_internal_order']."/".$orderInfo['to_internal_order'];
            $PriceInfos = $thirdPartyChoosen->_getPriceTrade($coinPair, 1);
            
            if($sellCoinBalance >= $orderInfo['usd_amount_internal_order']){
                $oldTradeAmount = ($orderInfo['amount_internal_order'] / $orderInfo['usd_amount_internal_order']) * $orderInfo['usd_amount_internal_order'];
                $currentTradeAmount = $PriceInfos * $orderInfo['usd_amount_internal_order'];
            } else {
                $oldTradeAmount = ($orderInfo['amount_internal_order'] / $orderInfo['usd_amount_internal_order']) * $sellCoinBalance;
                $currentTradeAmount = $PriceInfos * $sellCoinBalance;
            }

            $buyCoinBalance = $CurrentBalance->getBalanceByCoin($orderInfo['to_internal_order']);
            
            $marginAmount = ($currentTradeAmount - $oldTradeAmount) * $orderInfo['margin_val'];
            $totalAmount = $buyCoinBalance + $currentTradeAmount + $marginAmount;
            $profit = $currentTradeAmount + $marginAmount;
            $usd_amount_internal_order = $currentTradeAmount + $marginAmount;
            
            if($totalAmount <= 0){
                // TO DO FOR ADD SELL ORDER
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
            }
            
//            echo '$buyCoinBalance:' . (float)$buyCoinBalance;
//            echo "<br>";
//            echo '$oldTradeAmount:' . $oldTradeAmount;
//            echo "<br>";
//            echo '$currentTradeAmount:' . $currentTradeAmount;
//            echo "<br>";
//            echo "Margin: ".$marginAmount = ($currentTradeAmount - $oldTradeAmount) * $orderInfo['margin_val'];
//            $totalAmount = $buyCoinBalance + $currentTradeAmount + $marginAmount;
//            echo "<br>";
//            echo "Total: ".$totalAmount;
//            echo "<br>";
//            echo "Profit1: ".($currentTradeAmount + $marginAmount);
//            echo "<br> ==============================================================<br>";
        }
    }
} catch (\Exception $e) {
    die(json_encode([
        'error' => 1,
        'msg' => $e->getMessage()
    ]));
}