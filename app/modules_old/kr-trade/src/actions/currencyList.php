<?php

session_start();

require "../../../../../config/config.settings.php";

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

$User = new User(6235);
if (!$User->_isLogged())
    die('Error : User not logged');

$Lang = new Lang($User->_getLang(), $App);

try {
    // Init CryptoApi object
    $CryptoApi = new CryptoApi($User, ['BTC', 'BTC'], $App);

    $Balance = new Balance($User, $App);
    $BitcoinBalanceEstimation = 0;
    $BalanceEstimationSymbol = $Balance->_getEstimationSymbol();
    $BalanceEstimation = 0;
    if ($App->_hiddenThirdpartyActive()) {

        if (!$App->_hiddenThirdpartyNotConfigured())
            throw new Exception("You must activate at least 1 exchange (Admin -> Trading)", 1);


        $Balance = $Balance->_getCurrentBalance();

        $BitcoinBalanceEstimation = $Balance->_getEstimationBalance('BTC');
        $BalanceEstimationSymbol = $Balance->_getEstimationSymbol();
        $BalanceEstimation = $Balance->_getEstimationBalance();
    } else {
        $Trade = new Trade($User, $App);
        $listThirdParty = $Trade->_getThirdPartyListAvailable();

        $selectedThirdParty = $listThirdParty[0];

        $BalanceList = $selectedThirdParty->_getBalance(true);

        $BitcoinBalanceEstimation = $selectedThirdParty->_getBalanceEstimation('BTC', $Balance);
        $BalanceEstimation = $selectedThirdParty->_getBalanceEstimation($Balance->_getEstimationSymbol(true), $Balance);
    }

    if ($App->_getIdentityEnabled())
        $Identity = new Identity($User);
} catch (\Exception $e) {
    die("<span style='color:#f4f6f9;'>" . $e->getMessage() . "</span>");
}
$response = array();
if ($App->_hiddenThirdpartyActive()) {

    $DepositSymbolAllowed = array_values($Balance->_getDepositListAvailable());
    $DepositSymbolAllowed = array_merge($DepositSymbolAllowed, $App->_getCoinGateCryptoCurrencyDepositAllowed());

    foreach ($Balance->_getBalanceListResum() as $key => $value) {
        $value = 1;
        $NameCoin = "NULL";
        $CurrencySymbol = $key;
        $DecimalShown = 8;
        if ($Balance->_symbolIsMoney($key)) {
            $InfosCurrency = $Balance->_getInfosMoney($key);
            $NameCoin = $InfosCurrency['name_currency'];
            $CurrencySymbol = $InfosCurrency['symbol_currency'];
            $DecimalShown = 2;
            $SymbolConvertPrice = 0;
            $SymbolConvertPriceIndicatif = 0;
            if ($value > 0) {
                $SymbolConvertPrice = $Balance->_convertCurrency($value, $key, 'BTC');
                $SymbolConvertPriceIndicatif = $Balance->_convertCurrency($SymbolConvertPrice, 'BTC', $Balance->_getEstimationSymbol(true));
            }
        } else {
            try {
                $Coin = $CryptoApi->_getCoin($key);
                $NameCoin = $Coin->_getCoinName();
                $SymbolConvertPrice = 0;
                if ($value > 0)
                    $SymbolConvertPrice = $Coin->_convertTo('BTC', $value);

                $SymbolConvertPriceIndicatif = 0;
                if ($value > 0)
                    $SymbolConvertPriceIndicatif = $Coin->_convertTo($Balance->_getEstimationSymbol(true), $SymbolConvertPrice, 'BTC');
            } catch (\Exception $e) {
                continue;
            }
        }
        // Make response array //
        $currencyData['key'] = $key;
        $currencyData['name'] = $NameCoin;
        $currencyData['btc'] = $SymbolConvertPrice;
        $currencyData['usd'] = $SymbolConvertPriceIndicatif;
        $response[] = $currencyData;
    }
} else {
    foreach ($BalanceList as $key => $value) {
        $value = 1;
        $infosValue = $value;
        $value = $value['free'] + $value['used'];
        $NameCoin = "NULL";
        $CurrencySymbol = $key;
        $DecimalShown = 8;
        if ($Balance->_symbolIsMoney($key)) {
            $InfosCurrency = $Balance->_getInfosMoney($key);
            $NameCoin = $InfosCurrency['name_currency'];
            $CurrencySymbol = $InfosCurrency['symbol_currency'];
            $DecimalShown = 2;
            $SymbolConvertPrice = 0;
            $SymbolConvertPriceIndicatif = 0;
            if ($value > 0) {
                $SymbolConvertPrice = $Balance->_convertCurrency($value, $key, 'BTC');
                $SymbolConvertPriceIndicatif = $Balance->_convertCurrency($SymbolConvertPrice, 'BTC', $Balance->_getEstimationSymbol(true));
            }
        } else {
            try {
                $Coin = $CryptoApi->_getCoin($key);
                $NameCoin = $Coin->_getCoinName();
                $SymbolConvertPrice = 0;
                if ($value > 0)
                    $SymbolConvertPrice = $Coin->_convertTo('BTC', $value);

                $SymbolConvertPriceIndicatif = 0;
                if ($value > 0)
                    $SymbolConvertPriceIndicatif = $Coin->_convertTo($Balance->_getEstimationSymbol(true), $SymbolConvertPrice, 'BTC');
            } catch (\Exception $e) {
                continue;
            }
        }
        
        // Make response array //
        $currencyData['key'] = $key;
        $currencyData['name'] = $NameCoin;
        $currencyData['btc'] = $SymbolConvertPrice;
        $currencyData['usd'] = $SymbolConvertPriceIndicatif;
        $response[] = $currencyData;
    }
}

echo json_encode($response);
exit;