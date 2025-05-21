<?php

/**
 * Load left coin infos
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
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/Lang/Lang.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/User/User.php";

require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoOrder.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoNotification.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoIndicators.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoGraph.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoHisto.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoCoin.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoApi.php";

try {

  $App = new App();

  $User = new User();
  if (!$User->_isLogged()) {
      throw new Exception("Permission denied", 1);
  }

  $Lang = new Lang($User->_getLang(), $App);

  if(empty($_REQUEST) || !isset($_REQUEST['symbol']) || !isset($_REQUEST['currency'])) die("Permission denied");

//  $CryptoApi = new CryptoApi(null, (isset($_REQUEST['currency']) ? [$_REQUEST['currency'], null] : null), $App, $_REQUEST['market']);
  $CryptoApi = new CryptoApi($User, (isset($_REQUEST['currency']) ? [$_REQUEST['currency'], null] : null), $App, $_REQUEST['market']);
//  $Coin = $CryptoApi->_getCoin($_REQUEST['symbol']);
  $Coin = new CryptoCoin($CryptoApi, $_REQUEST['symbol'], null, $_REQUEST['market'] );

//  $ListDetails = [
//    "Open day" => $App->_formatNumber($Coin->_getOpenDayMultiFull(), ($Coin->_getOpenDayMultiFull() > 10 ? 2 : 5)),
//    "Market Cap" => $Coin->_formatNumberCommarization($Coin->_getMarketCap()),
//    "Volume 24H" => $Coin->_formatNumberCommarization($Coin->_getTotal24VolMultiFull()),
//    "Direct Volume" => $Coin->_formatNumberCommarization($Coin->_getDirectVol24())
//  ];

} catch (\Exception $e) {
  die($e->getMessage());
}




?>
<?php ($_REQUEST['market'] != '') ? $_REQUEST['market'] : 'CCCAGG'; ?>
<?php
if($_REQUEST['market'] == 'US_STOCK'){
    $multiFullData = $Coin->_getStockAllMultiFullData();
    ?>
    <header kr-leftinfoisp="<?php echo strtoupper($_REQUEST['market']).':'.$Coin->_getSymbol().$CryptoApi->_getCurrency(); ?>"
        kr-leftinfois-makr="<?php echo strtoupper($_REQUEST['market']); ?>"
        kr-leftinfois-symbol="<?php echo strtoupper($Coin->_getSymbol()); ?>"
        kr-leftinfois-currency="<?php echo strtoupper($CryptoApi->_getCurrency()); ?>"
      >
        <span><?php echo $Lang->tr('Details'); ?></span>
        <button type="button" onclick="hideLeftInfosMoreDetails();" class="btn btn-small btn-green btn-autowidth"><?php echo $Lang->tr('Hide orders book'); ?></button>
      </header>
      <div class="kr-infoscurrencylf-header" style="<?php if($App->_getHideMarket()) echo 'min-height:13px;'; ?>">
        <h2><?php echo $Coin->_getCoinName(); ?> (<?php echo $Coin->_getCoinFullName(); ?>)</h2>
        <?php if(!$App->_getHideMarket()): ?>
          <span class="market-span">CCCAGG</span>
        <?php endif; ?>
      </div>
        <?php
            $percentage = 0;
            $openDay = (isset($multiFullData['OPEN'])) ? $multiFullData['OPEN'] : '0.00';
            $currentStockPrice = (isset($multiFullData['CURRENT'])) ? $multiFullData['CURRENT'] : 0;
            $high24Hour = (isset($multiFullData['HIGH'])) ? $multiFullData['HIGH'] : 0;
            $low24Hour = (isset($multiFullData['LOW'])) ? $multiFullData['LOW'] : 0;
            $max = $high24Hour - $low24Hour;
            if($max == 0) { 
                $percentage = 0;                
            } else {
                $percentage = 100 - abs(((($currentStockPrice - $low24Hour) - $max) / $max) * 100);
            }
            
            $evolAmount = $currentStockPrice - $openDay;
            $evolPercentage = ($evolAmount * 100) / $currentStockPrice;
            $color = 'green';
            if($evolAmount < 0){
                $color = 'red';
            }
            ?>
      <div class="kr-infoscurrencylf-price left_info_<?php echo strtoupper($Coin->_getSymbol()); ?>">
          <span class="kr-infoscurrencylf-price-cp_stock" style="font-size: 28px; font-weight: 700; line-height: 20px; margin-right: 10px; color: #c5cbce;"><?php echo (isset($multiFullData['CURRENT'])) ? $multiFullData['CURRENT'] : '0.00000' ?></span>
          <span class="kr-infoscurrencylf-price-evolv" style="color: <?= $color; ?>"><?= number_format($evolAmount, 2); ?> (<?= number_format($evolPercentage, 2) ?>%)</span>
      </div>
      <div class="kr-infoscurrencylf-range">
        <div class="kr-infoscurrencylf-range-bar">
            
            <div class="percentage_price_low_high_stock" style="width:<?= $percentage ?>%;">

          </div>
        </div>
        <div class="kr-infoscurrencylf-range-infos">
          <span class="kr-infoscurrencylf-range-infos-low_stock"><?php echo (isset($multiFullData['LOW'])) ? $multiFullData['LOW'] : '0.00' ?></span>
          <label><?php echo $Lang->tr('Day range'); ?></label>
          <span class="kr-infoscurrencylf-range-infos-high_stock"><?php echo (isset($multiFullData['HIGH'])) ? $multiFullData['HIGH'] : '0.00' ?></span>
        </div>
      </div>
      <ul>
        <!-- <?php
        //foreach ($ListDetails as $titleDetails => $valueDetails) {
          ?>
          <li>
            <span><?php //echo $Lang->tr($titleDetails); ?></span>
            <div></div>
            <span><?php //echo $valueDetails; ?></span>
          </li>
          <?php
        //}
        ?> -->

          <li>
            <span><?php echo $Lang->tr('Open day'); ?></span>
            <div></div>
            <span class="c_open_day"><?php echo $openDay ?></span>
          </li>
          <li>
            <span><?php echo $Lang->tr('PREVIOUS CLOSE'); ?></span>
            <div></div>
            <span class="c_market_cap"><?php echo (isset($multiFullData['PREVIOUS_CLOSE'])) ? $multiFullData['PREVIOUS_CLOSE'] : '0.00' ?></span>
          </li>
      </ul>
<?php
} else {
    ?>
    <header kr-leftinfoisp="<?php echo strtoupper($_REQUEST['market']).':'.$Coin->_getSymbol().$CryptoApi->_getCurrency(); ?>"
        kr-leftinfois-makr="<?php echo strtoupper($_REQUEST['market']); ?>"
        kr-leftinfois-symbol="<?php echo strtoupper($Coin->_getSymbol()); ?>"
        kr-leftinfois-currency="<?php echo strtoupper($CryptoApi->_getCurrency()); ?>"
      >
        <span><?php echo $Lang->tr('Details'); ?></span>
        <button type="button" onclick="hideLeftInfosMoreDetails();" class="btn btn-small btn-green btn-autowidth"><?php echo $Lang->tr('Hide orders book'); ?></button>
      </header>
      <div class="kr-infoscurrencylf-header" style="<?php if($App->_getHideMarket()) echo 'min-height:13px;'; ?>">
        <h2><?php echo $Coin->_getCoinName(); ?> / <?php echo $CryptoApi->_getCurrencyFullName(); ?></h2>
        <?php if(!$App->_getHideMarket()): ?>
          <span class="market-span">CCCAGG</span>
        <?php endif; ?>
      </div>
      <div class="kr-infoscurrencylf-price">
        <span class="kr-infoscurrencylf-price-cp">0.00000</span>
        <span class="kr-infoscurrencylf-price-evolv  ">0.00 (0.00%)</span>
      </div>
      <div class="kr-infoscurrencylf-range">
        <div class="kr-infoscurrencylf-range-bar">
            <div class="percentage_price_low_high" style="width:0%;">

          </div>
        </div>
        <div class="kr-infoscurrencylf-range-infos">
          <span class="kr-infoscurrencylf-range-infos-low">0.00</span>
          <label><?php echo $Lang->tr('Day range'); ?></label>
          <span class="kr-infoscurrencylf-range-infos-high">0.00</span>
        </div>
      </div>
      <ul>
        <!-- <?php
        //foreach ($ListDetails as $titleDetails => $valueDetails) {
          ?>
          <li>
            <span><?php //echo $Lang->tr($titleDetails); ?></span>
            <div></div>
            <span><?php //echo $valueDetails; ?></span>
          </li>
          <?php
        //}
        ?> -->

          <li>
            <span><?php echo $Lang->tr('Open day'); ?></span>
            <div></div>
            <span class="c_open_day">0.00</span>
          </li>

          <li>
            <span><?php echo $Lang->tr('Market Cap'); ?></span>
            <div></div>
            <span class="c_market_cap">0.00</span>
          </li>

          <li>
            <span><?php echo $Lang->tr('Volume 24H'); ?></span>
            <div></div>
            <span class="c_volume_24H">0.00</span>
          </li>

          <li>
            <span><?php echo $Lang->tr('Direct Volume'); ?></span>
            <div></div>
            <span class="c_direct_volume">0.00</span>
          </li>
      </ul>

      <div class="kr-infoscurrencylf-btn">
        <button type="button" onclick="loadLeftInfosMoreDetails();" class="btn btn-autowidth btn-green btn-small" name="button"><?php echo $Lang->tr('Load orders book'); ?></button>
      </div>

      <section class="kr-infoscurrencylf-orderbook">
        <?php
        foreach (['asks', 'bids'] as $key => $sideOrderBook) {
        ?>
          <div>
            <header>
              <ul>
                <li><?php echo $Lang->tr('Total'); ?></li>
                <li><?php echo $Lang->tr('Amount'); ?></li>
                <li><?php echo $Lang->tr('Price'); ?></li>
              </ul>
            </header>
            <section kr-orderbook-side="<?php echo $sideOrderBook; ?>">

            </section>
          </div>
        <?php } ?>
      </section>
    <?php
}
?>


<!--<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.js" integrity="sha256-WpOohJOqMqqyKL9FccASB9O0KwACQJpFTUBLTYOVvVU=" crossorigin="anonymous"></script>--> 
<?php if($Coin->_getMarket() == 'US_STOCK'){
    ?>
    <script>
    $(document).ready(function(){
        $('header').attr('kr-leftinfoisp', '<?= $Coin->_getMarket() ?>'+':'+'<?= $_REQUEST['symbol'].$_REQUEST['currency'] ?>');
        $('header').attr('kr-leftinfois-makr', '<?= $Coin->_getMarket() ?>');
        $('header').attr('kr-leftinfois-symbol', '<?= $_REQUEST['symbol'] ?>');
        $('header').attr('kr-leftinfois-currency', '<?= $_REQUEST['currency'] ?>');
        $('.market-span').html("<?= $Coin->_getMarket() ?>");
    });
    </script>
    <?php
} else {
    ?>
<script>
    $(document).ready(function(){
        $.ajax({url: "https://min-api.cryptocompare.com/data/pricemultifull?fsyms=<?= $_REQUEST['symbol'] ?>&tsyms=<?= $_REQUEST['currency'] ?>&e=<?= $_REQUEST['market'] ?>", success: function(result){
            var rowData = result.RAW;
            rowData = rowData.<?= $_REQUEST['symbol'] ?>;
            rowData = rowData.<?= $_REQUEST['currency'] ?>;
            //console.log(rowData);
            
            $('header').attr('kr-leftinfoisp', rowData.MARKET.toUpperCase()+':'+'<?= $_REQUEST['symbol'].$_REQUEST['currency'] ?>');
            $('header').attr('kr-leftinfois-makr', rowData.MARKET.toUpperCase());
            $('header').attr('kr-leftinfois-symbol', '<?= $_REQUEST['symbol'] ?>');
            $('header').attr('kr-leftinfois-currency', '<?= $_REQUEST['currency'] ?>');
            $('.market-span').html(rowData.MARKET.toUpperCase());
            
            var priceCp = 0.00;
            if(rowData.PRICE > 10){
                priceCp = rowData.PRICE.toFixed(2);
            } else {
                priceCp = rowData.PRICE.toFixed(5);
            }
            
            $('.kr-infoscurrencylf-price-cp').html(priceCp);
            
            var className = '';
            if(rowData.CHANGE24HOUR < 0){
                className = 'kr-hg-down';
            }
            if(rowData.CHANGE24HOUR > 0){
                className = 'kr-hg-up';
            }            
            $('.kr-infoscurrencylf-price-evolv').addClass(className);
            $('.kr-infoscurrencylf-price-evolv').html(rowData.CHANGE24HOUR.toFixed(2) +" ("+ rowData.CHANGEPCT24HOUR.toFixed(2) +"%)");
            
            $('.percentage_price_low_high').css('width', _getCurrentPercentagePriceLowHigh(rowData.PRICE, rowData.HIGH24HOUR, rowData.LOW24HOUR)+'%')
            
            var LOW24HOUR = '0.00';
            if(rowData.LOW24HOUR > 10){
                LOW24HOUR = rowData.LOW24HOUR.toFixed(2);
            } else {
                LOW24HOUR = rowData.LOW24HOUR.toFixed(5);
            }
            $('.kr-infoscurrencylf-range-infos-low').html(LOW24HOUR);
            
            var HIGH24HOUR = '0.00';
            if(rowData.HIGH24HOUR > 10){
                HIGH24HOUR = rowData.HIGH24HOUR.toFixed(2);
            } else {
                HIGH24HOUR = rowData.HIGH24HOUR.toFixed(5);
            }
            $('.kr-infoscurrencylf-range-infos-high').html(HIGH24HOUR);
            
            var OPEN24HOUR = '0.00';
            if(rowData.OPEN24HOUR > 10){
                OPEN24HOUR = rowData.OPEN24HOUR.toFixed(2);
            } else {
                OPEN24HOUR = rowData.OPEN24HOUR.toFixed(5);
            }
            $('.c_open_day').html(OPEN24HOUR);
            
            $('.c_market_cap').html(rowData.MKTCAP.toFixed(5));            
            $('.c_volume_24H').html(rowData.VOLUME24HOUR.toFixed(5));            
            $('.c_direct_volume').html(rowData.VOLUME24HOURTO.toFixed(5));
        }});
        
        
//        function _getCurrentPercentagePriceLowHigh(PRICE, HIGH24HOUR, LOW24HOUR){
//            var max = HIGH24HOUR - LOW24HOUR;
//            if(max == 0) return 0;
//
//            return 100 - Math.abs((((PRICE - LOW24HOUR) - max) / max) * 100);
//        }
    });
</script>
<?php
}
?>

<script>
function _getCurrentPercentagePriceLowHigh(PRICE, HIGH24HOUR, LOW24HOUR){
    var max = HIGH24HOUR - LOW24HOUR;
    if(max == 0) return 0;

    return 100 - Math.abs((((PRICE - LOW24HOUR) - max) / max) * 100);
}
</script>