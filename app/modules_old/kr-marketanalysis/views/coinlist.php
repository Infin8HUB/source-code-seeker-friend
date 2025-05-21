<?php

/**
 * Coin list market analytic view
 *
 * @package Krypto
 * @author Ovrley <hello@ovrley.com>
 */

session_start();

require "../../../../config/config.settings.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/vendor/autoload.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/MySQL/MySQL.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/App/App.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/App/AppModule.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/User/User.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/Lang/Lang.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoGraph.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoHisto.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoCoin.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoApi.php";

// Load app modules
$App = new App(true);
$App->_loadModulesControllers();

// Check if user is logged
$User = new User();
if(!$User->_isLogged()) die("You are not logged");

// Init lang object
$Lang = new Lang($User->_getLang(), $App);

// Init CryptoApi object
$CryptoApi = new CryptoApi($User, null, $App);

?>
<div class="kr-marketcoinlist">

  <nav class="kr-marketnav">
    <ul>
      <li kr-navview="coinlist" class="kr-nav-selected"><?php echo $Lang->tr('Coin list'); ?></li>
      <li kr-navview="marketlist"><?php echo $Lang->tr('Market list'); ?></li>
      <li kr-navview="dashboard"><?php echo $Lang->tr('Heatmap'); ?></li>
    </ul>
    <form class="kr-search-coin" action="" method="post">
      <input type="text" name="kr-search-value" placeholder="Search coin ..." value="<?php echo (!isset($_POST['search']) || empty($_POST['search']) ? '' : $_POST['search']); ?>">
    </form>
  </nav>

  <div class="kr-marketlist" kr-currency-mm="<?php echo $CryptoApi->_getCurrency(); ?>" kr-currency-mm-symb="<?php echo $CryptoApi->_getCurrencySymbol(); ?>">
    <div class="kr-marketlist-header">
      <div class="kr-marketlist-n"></div>
      <div class="kr-marketlist-cellnumber kr-mono"><span><?php echo $Lang->tr('Price'); ?></span></div>
      <div class="kr-marketlist-cellnumber kr-mono kr-marketlist-cellnumber-f2"><span><?php echo $Lang->tr('Direct Vol. 24H'); ?></span></div>
      <div class="kr-marketlist-cellnumber kr-mono kr-marketlist-cellnumber-f3"><span><?php echo $Lang->tr('Total Vol. 24H'); ?></span></div>
      <div class="kr-marketlist-cellnumber kr-mono kr-marketlist-cellnumber-f2"><span><?php echo $Lang->tr('Market Cap'); ?></span></div>
      <div class="kr-marketlist-cellnumber kr-mono"><span><?php echo $Lang->tr('Chg. 24H'); ?></span></div>
      <div class="kr-marketlist-cellnumber kr-mono kr-marketlist-cellnumber-f1"><span><?php echo $Lang->tr('24h High/Low'); ?></span></div>
    </div>
    <?php

    foreach ($CryptoApi->_getCoinsList(30, true, false, (!isset($_POST['search']) || empty($_POST['search']) ? null : $_POST['search'])) as $Coin) {
        
      $icon = $Coin->_getIcon();

      ?>
      <div kr-symbol-mm="<?php echo $Coin->_getSymbol(); ?>" onclick="return false;" class="market-list-bb">
        <div class="kr-marketlist-n">
          <div class="kr-marketlist-n-nn">
            <label class="kr-mono"><?php echo $Coin->_getCoinName(); ?></label>
          </div>
        </div>
        <div class="kr-marketlist-cellnumber kr-mono">
            <span kr-mm-c="PRICE" kr-mm-cp="0.0000" class="price-bb">0.00000</span>
        </div>
        <div class="kr-marketlist-cellnumber kr-mono kr-marketlist-cellnumber-f2">
            <span kr-mm-c="VOLUME24HOURTO" class="volume24_hour_to-bb">0.00000</span>
        </div>
        <div class="kr-marketlist-cellnumber kr-mono kr-marketlist-cellnumber-f3">
          <span class="total_vol_24-bb">0.00000</span>
        </div>
        <div class="kr-marketlist-cellnumber kr-mono kr-marketlist-cellnumber-f2">
            <span class="market_cap-bb">0.00000</span>
        </div>
        <div class="kr-marketlist-cellnumber kr-mono">
          <span class="changepcthour-bb " kr-mm-c="CHANGE24HOURPCT">0.00%</span>
        </div>
        <div class="kr-marketlist-cellffhl kr-mono kr-marketlist-cellnumber-f1">
          <div class="kr-marketlist-ffhl">
            <div class="kr-marketlist-ffhl-progr">
                <div class="pcr_price_low_high-bb" style="width:0%;"></div>
            </div>
            <div class="kr-marketlist-ffhl-mm">
              <span class="low_24_hour-bb">0.00</span>
              <span class="high_24_hour-bb">0.00</span>
            </div>
          </div>
        </div>
      </div>
    <?php } ?>
  </div>

</div>


<script>
    $(document).ready(function(){
        $('.market-list-bb').each(function(){
            var marketListObj = $(this);
            var symbol = $(this).attr('kr-symbol-mm');
            var url = "https://min-api.cryptocompare.com/data/pricemultifull?fsyms="+symbol+"&tsyms=<?php echo $CryptoApi->_getCurrency(); ?>";            
            $.ajax({url: url, async: false, success: function(result){
                var rowData = result.RAW;
                rowData = rowData[symbol];
                rowData = rowData.<?php echo $CryptoApi->_getCurrency(); ?>;
                
                var priceCp = 0.00;
                if(rowData.PRICE > 10){
                    priceCp = rowData.PRICE.toFixed(2);
                } else {
                    priceCp = rowData.PRICE.toFixed(5);
                }
                marketListObj.find('.price-bb').attr('kr-mm-cp', rowData.PRICE);
                marketListObj.find('.price-bb').html(priceCp);
                
                marketListObj.find('.volume24_hour_to-bb').html(test(rowData.VOLUME24HOURTO.toFixed(2)));
                marketListObj.find('.total_vol_24-bb').html(test(rowData.TOTALVOLUME24HTO.toFixed(2)));
                marketListObj.find('.market_cap-bb').html(test(rowData.MKTCAP.toFixed(2)));
                
                if(parseFloat(rowData.CHANGE24HOURPCT) < 0){
                    marketListObj.find('.changepcthour-bb').addClass('kr-marketlist-cellnumber-negativ');
                }
                if(parseFloat(rowData.CHANGE24HOURPCT) > 0){
                    marketListObj.find('.changepcthour-bb').addClass('kr-marketlist-cellnumber-positiv');
                }
                marketListObj.find('.changepcthour-bb').html(rowData.CHANGEPCT24HOUR.toFixed(2)+'%');
                
                marketListObj.find('.pcr_price_low_high-bb').css('width', _getCurrentPercentagePriceLowHigh(rowData.PRICE, rowData.HIGH24HOUR, rowData.LOW24HOUR)+'%');
                
                marketListObj.find('.low_24_hour-bb').html(rowData.LOW24HOUR.toFixed(2));
                marketListObj.find('.high_24_hour-bb').html(rowData.HIGH24HOUR.toFixed(2));
            }});
            
            addSubscribtion(symbol, '<?php echo $CryptoApi->_getCurrency(); ?>');
            
        });
        
        
        function _getCurrentPercentagePriceLowHigh(PRICE, HIGH24HOUR, LOW24HOUR){
            var max = HIGH24HOUR - LOW24HOUR;
            if(max == 0) return 0;

            return 100 - Math.abs((((PRICE - LOW24HOUR) - max) / max) * 100);
        }
        function test (labelValue) 
        {
            // Nine Zeroes for Billions
            return Math.abs(Number(labelValue)) >= 1.0e+9

            ? (Math.abs(Number(labelValue)) / 1.0e+9).toFixed(2) + " B"
            // Six Zeroes for Millions 
            : Math.abs(Number(labelValue)) >= 1.0e+6

            ? (Math.abs(Number(labelValue)) / 1.0e+6).toFixed(2) + " M"
            // Three Zeroes for Thousands
            : Math.abs(Number(labelValue)) >= 1.0e+3

            ? (Math.abs(Number(labelValue)) / 1.0e+3).toFixed(2) + " K"

            : (Math.abs(Number(labelValue))).toFixed(2);
        }
    });
</script>
