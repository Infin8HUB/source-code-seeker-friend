<?php

/**
 * StockWatchingList item view
 *
 * @package Krypto
 * @author Ovrley <hello@ovrley.com>
 */

session_start();

require "../../../../../config/config.settings.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/vendor/autoload.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/MySQL/MySQL.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/User/User.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/App/App.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/App/AppModule.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoIndicators.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoGraph.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoHisto.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoCoin.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoApi.php";

// Load app modules
$App = new App(true);
$App->_loadModulesControllers();

try {

  // Check if user is logged
  $User = new User();
  if(!$User->_isLogged()) throw new Exception("User are not logged", 1);

  // Check args
  if(empty($_GET) || empty($_GET['symb'])) throw new Exception("Error : Args missing", 1);

  // Init CryptoApi object
  $CryptoApi = new CryptoApi(null, [$_GET['currency'], $_GET['currency']], $App, (isset($_GET['market']) ? $_GET['market'] : 'Forex'));

  // Get coin data
  $Coin = $CryptoApi->_getCoin($_GET['symb']);


  // If item need to be added --> add
  if(!empty($_GET['t']) && $_GET['t'] == "add"){
    // Init watching list
    $ForexWatchingList = new ForexWatchingList($CryptoApi, $User);
    $ForexWatchingList->_addItem($Coin->_getSymbol(), $_GET['currency'], (isset($_GET['market']) ? $_GET['market'] : 'Forex'));
  }

} catch (Exception $e) { // If error detected, show error
  die(json_encode([
    'error' => 1,
    'msg' => $e->getMessage()
  ]));
}

?>

<?php ($_REQUEST['market'] != '') ? $_REQUEST['market'] : 'CCCAGG'; ?>

<?php $className = 'cls'.strtolower($Coin->_getSymbol()).'s_s'. strtolower($CryptoApi->_getCurrency()).'s'; ?>

<!-- <li class="<?php echo $Coin->_getSymbol().'_'.$CryptoApi->_getCurrency(); ?>" kr-watchinglistpair="<?php echo $_REQUEST['market'].':'.$Coin->_getSymbol().'/'.$CryptoApi->_getCurrency(); ?>" class="<?php //echo ($i == 2 ? 'kr-wtchl-lst-selected' : ''); ?>">-->
<li class="<?= $className ?>" kr-watchinglistpair="<?php echo strtoupper($_REQUEST['market']).':'.$Coin->_getSymbol().'/'.$CryptoApi->_getCurrency(); ?>">
  <div>
    <span><?php echo $Coin->_getSymbol().'/'.$CryptoApi->_getCurrency(); ?></span>
  </div>
  <div>
    <span class="kr-watchinglistpair-price">0.00</span>
  </div>
  <div>
    <span class="kr-watchinglistpair-evolv">0.00%</span>
  </div>
  <div class="kr-wtchl-lst-remove">
    <svg class="lnr lnr-cross"><use xlink:href="#lnr-cross"></use></svg>
  </div>
</li>

<?php
/*
?>
<script>
    $(document).ready(function(){
        $.ajax({url: "https://min-api.cryptocompare.com/data/pricemultifull?fsyms=<?= $_REQUEST['symb'] ?>&tsyms=<?= $_REQUEST['currency'] ?>&e=<?= $_REQUEST['market'] ?>", success: function(result){
            var rowData = result.RAW;
            rowData = rowData.<?= $_REQUEST['symb'] ?>;
            rowData = rowData.<?= $_REQUEST['currency'] ?>;
            
            var priceCp = 0.00;
            if(rowData.PRICE > 10){
                priceCp = rowData.PRICE.toFixed(2);
            } else {
                priceCp = rowData.PRICE.toFixed(5);
            }
            
            $('.<?= $className ?> .kr-watchinglistpair-price').html(priceCp);
            
            $('.<?= $className ?> .kr-watchinglistpair-evolv').html(rowData.CHANGEPCT24HOUR.toFixed(2) +"%");
            
        }});
    });
</script>
<?php 
 */
?>