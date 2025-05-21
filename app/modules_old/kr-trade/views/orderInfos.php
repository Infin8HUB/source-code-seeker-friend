<?php

/**
 * Load order book
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
    // Check if user is logged
    $User = new User();
    if (!$User->_isLogged()) {
        throw new Exception("Error : User is not logged", 1);
    }

    if(empty($_REQUEST) || !isset($_REQUEST['order_id']) || empty($_REQUEST['order_id'])) throw new Exception("Permission denied", 1);

    if($App->_hiddenThirdpartyActive()){
      $OrderID = explode('-', App::encrypt_decrypt('decrypt', $_REQUEST['order_id']));
      if(count($OrderID) != 2) throw new Exception("Permission denied", 1);

      $Lang = new Lang($User->_getLang(), $App);

      

      $Balance = new Balance($User, $App);

      $CurrentBalance = $Balance->_getCurrentBalance();

      $OrderInfos = $CurrentBalance->_getOrderInfos($OrderID[1]);
      if($OrderInfos['thirdparty_internal_order'] == 'usstock'){
          $CryptoApi = new CryptoApi($User, null, $App, 'US_STOCK');
      } else {
          $CryptoApi = new CryptoApi($User, null, $App);
      }
            
      $CoinFrom = $CryptoApi->_getCoin($OrderInfos['symbol_internal_order']);
      $CoinTo = $CryptoApi->_getCoin($OrderInfos['to_internal_order']);
      
    } else {

      $Trade = new Trade($User, $App);
      $selectedThirdParty = $Trade->_getSelectedThirdparty();

      die();
    }


} catch(Exception $e){
  echo '<script>closeOrderInfos();</script>';
  die($e->getMessage());

}

?>
<header>
  <div>
    <div>
      <img src="<?php echo $CoinFrom->_getIcon(); ?>" alt="">
      <span><?php echo $CoinFrom->_getCoinName(); ?> / <?php echo $CoinTo->_getCoinName(); ?></span>
      
      <?php
      if($_SESSION['is_manager_login'] == true && $OrderInfos['is_show'] == 0){
          echo "<span class='dot_label_".$OrderInfos['id_internal_order']."' style='width: 10px;height: 10px;background-color: red;margin-right: 5px;border: 1px solid red;border-radius: 100%;display: inline-block;margin-left: 4px;'>&nbsp;</span>";
      } else {
          echo "<span class='dot_label_".$OrderInfos['id_internal_order']."' style='width: 10px;height: 10px;background-color: transparent;margin-right: 5px;border: 1px solid transparent;border-radius: 100%;display: inline-block;margin-left: 4px;'>&nbsp;</span>";
      }
      ?>
      
    </div>
    <svg onclick="closeOrderInfos();" class="lnr lnr-cross"><use xlink:href="#lnr-cross"></use></svg>
  </div>
</header>
<?php
if($OrderInfos['type_internal_order'] == "mining"){ 
$leadsApiObj = new LeadsApi();       
$param = [
    'brand_uid' => $leadsApiObj->getBusinessId(),
    'product_id' => $OrderInfos['mining_product_id']
];

$responsePackages = $leadsApiObj->callCurl('productList', $param);   
if($responsePackages['statuscode'] != '200' || empty($responsePackages['products'])) 
   throw new Exception("Error : ".$responsePackages['message'], 1);
    
$product = $responsePackages['products'][0];
$days = $product['exp_days'];
$fdate = date('Y-m-d');
$tdate = date('Y-m-d', strtotime(date('Y-m-d', $OrderInfos['date_internal_order']). ' + '.$days.' days'));
//$tdate = $product['exp_date'];
/*$datetime1 = new DateTime($fdate);
$datetime2 = new DateTime($tdate);
$interval = $datetime1->diff($datetime2);
$days = $interval->format('%a');*/
$revenue = number_format(($product['price']*$product['max_revenue_per_click'])/100,2);
$paybackPeriod = ($product['min_revenue_per_click']-100)."%";
?>
<section>
  
    <section class="kr-orderinfoside-dt">
      <span><?php echo 'DATA MINING ORDER'; ?></span>
      <?php
      if($OrderInfos['status_internal_order'] == "1"):
      ?>
      <div>
        <svg class="lnr lnr-flag"><use xlink:href="#lnr-flag"></use></svg>
        <span><?php echo date('d/m/Y H:i:s', $OrderInfos['date_internal_order']); ?></span>
      </div>
    <?php else: ?>
      <div>
        <svg class="lnr lnr-clock"><use xlink:href="#lnr-clock"></use></svg>
        <span><?php echo $Lang->tr('Not fullfiled ...'); ?></span>
      </div>
    <?php endif; ?>
    </section>
  
  
  <ul class="kr-orderinfoside-cinout">
      <!--<li>
        <span>+ <?php echo $App->_formatNumber($OrderInfos['usd_amount_internal_order'] - $OrderInfos['fees_internal_order'], 8); ?></span>
        <label><?php echo $OrderInfos['symbol_internal_order']; ?></label>
      </li>-->
      <li>
        <span>- <?php echo rtrim($App->_formatNumber($OrderInfos['amount_internal_order'], 8), "0"); ?></span>
        <label><?php echo $OrderInfos['symbol_internal_order']; ?></label>
      </li>
    
  </ul>
  <ul class="kr-orderinfoside-minf">
    <li>
      <span><?php echo $Lang->tr('Order Ref.'); ?></span>
      <div></div>
      <span><?php echo (strlen($OrderInfos['ref_internal_order']) > 0 ? $OrderInfos['ref_internal_order'] : $OrderInfos['id_user'].'-'.$OrderInfos['id_internal_order']); ?></span>
    </li>
    <li>
      <span><?php echo $Lang->tr('Fees'); ?></span>
      <div></div>
      <span><?php echo rtrim($App->_formatNumber($OrderInfos['fees_internal_order'], 12), "0").' '.($OrderInfos['side_internal_order'] == "BUY" ? $OrderInfos['symbol_internal_order'] : $OrderInfos['to_internal_order']); ?></span>
    </li>
    <li>
      <?php if($OrderInfos['side_internal_order'] == "BUY"): ?>
        <span><?php echo $OrderInfos['to_internal_order']; ?> <?php echo $Lang->tr('Amount'); ?></span>
        <div></div>
        <span><?php echo rtrim($App->_formatNumber($OrderInfos['amount_internal_order'], 8), "0").' '.$OrderInfos['to_internal_order']; ?></span>
      <?php else: ?>
        <span><?php echo $OrderInfos['symbol_internal_order']; ?> <?php echo $Lang->tr('Amount'); ?></span>
        <div></div>
        <span><?php echo rtrim($App->_formatNumber($OrderInfos['amount_internal_order'], 8), "0").' '.$OrderInfos['symbol_internal_order']; ?></span>
      <?php endif; ?>
    </li>
    <li>
      <span><?php echo $Lang->tr('Name'); ?></span>
      <div></div>
      <span><?php echo rtrim($product['name']); ?></span>
    </li>
    <li>
      <span><?php echo $Lang->tr('Description'); ?></span>
      <div></div>
      <span><?php echo rtrim($product['short_descr']); ?></span>
    </li>
    <!--<li>
      <span><?php echo $Lang->tr('Category'); ?></span>
      <div></div>
      <span><?php echo rtrim($product['catName']); ?></span>
    </li>-->
    <li>
      <span><?php echo $Lang->tr('Expire On'); ?></span>
      <div></div>
      <span><?php echo rtrim($tdate); ?></span>
    </li>
    <li>
      <span><?php echo $Lang->tr('Payback Period'); ?></span>
      <div></div>
      <span><?php echo rtrim($days); ?> Days</span>
    </li>
<!--    <li>
      <span><?php //echo $Lang->tr('Interest'); ?></span>
      <div></div>
      <span><?php //echo rtrim($paybackPeriod); ?></span>
    </li>-->
    
  </ul>
  
</section>
<?php    
} else {
?>
<section>
  <?php
  if($OrderInfos['type_internal_order'] == "market"):
  ?>
    <section class="kr-orderinfoside-dt">
      <span><?php echo $Lang->tr($OrderInfos['side_internal_order'].' ORDER DONE'); ?></span>
      <div>
        <svg class="lnr lnr-flag"><use xlink:href="#lnr-flag"></use></svg>
        <span><?php echo date('d/m/Y H:i:s', $OrderInfos['date_internal_order']); ?></span>
      </div>
    </section>
  <?php else: ?>
    <section class="kr-orderinfoside-dt">
      <span><?php echo $Lang->tr($OrderInfos['side_internal_order'].' LIMIT ORDER'); ?></span>
      <?php
      if($OrderInfos['status_internal_order'] == "1"):
      ?>
      <div>
        <svg class="lnr lnr-flag"><use xlink:href="#lnr-flag"></use></svg>
        <span><?php echo date('d/m/Y H:i:s', $OrderInfos['date_internal_order']); ?></span>
      </div>
    <?php else: ?>
      <div>
        <svg class="lnr lnr-clock"><use xlink:href="#lnr-clock"></use></svg>
        <span><?php echo $Lang->tr('Not fullfiled ...'); ?></span>
      </div>
    <?php endif; ?>
    </section>
  <?php endif; ?>
    
    <?php
  if($_SESSION['is_manager_login'] == true):      
      $btnName = ($OrderInfos['is_show'] == 1) ? "Hide" : "Show";
  ?>
    <ul class="kr-orderinfoside-action">
      <li>
        <input type="button" class="btn btn-small btn-autowidth hide_show_btn_<?= $OrderInfos['id_internal_order'] ?>" onclick="_hideShowOrder('<?php echo $OrderInfos['id_internal_order']; ?>');" name="" value="<?= $btnName; ?>">
      </li>
    </ul>    
  <?php endif; ?>
<!--     For margin sell button-->
    <?php
    $buyCoinBalance = $Balance->getBalanceByCoin($OrderInfos['symbol_internal_order']);
    if($buyCoinBalance > 0 && $OrderInfos['side_internal_order'] == 'BUY' && $OrderInfos['margin_val'] > 1 && $OrderInfos['is_sold'] == 0):
        $Precision = 2;
        $SymbolTrade = $OrderInfos['to_internal_order'];
        $thirdPartyChoosen = null;
        $Trade = new Trade($User, $App);
        $thirdPartyChoosen = $Trade->_getThirdParty($App->_hiddenThirdpartyServiceCfg()[strtolower($OrderInfos['thirdparty_internal_order'])])[strtolower($OrderInfos['thirdparty_internal_order'])];
        $coinPair = $OrderInfos['symbol_internal_order']."/".$OrderInfos['to_internal_order'];
        $priceMarketUnit = $thirdPartyChoosen->_getPriceTrade($coinPair, 1);
        $infosPairMarket = $thirdPartyChoosen->_getInfosPair($OrderInfos['symbol_internal_order'], $OrderInfos['to_internal_order']);
        
    ?>
    <ul class="kr-orderinfoside-action sell_margin_div_<?= $OrderInfos['id_internal_order'] ?>" style="padding-top: 5px;" id="sell_margin_div_<?= $OrderInfos['id_internal_order'] ?>">
        <li style="position: relative;">
          <input type="button" class="btn btn-small btn-autowidth btn-red btn-sell-margin" onclick="" name="" value="Sell With x<?= (int)$OrderInfos['margin_val'] ?> Leverage">
          
          <style>
            .kr-dash-pan-action-confirm1 {
                background: #1c2030;
                width: 261px;
                position: absolute;
                left: 0px;
/*                bottom: 0;*/
                cursor: default;
                box-shadow: 0px 0px 12px 0px #0000004f;
                padding: 12px;
                display: none;
                z-index: 9999999;
                top: 33px;
            }
            .kr-dash-pan-action-confirm1 > header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-bottom: 17px;
            }
            .kr-dash-pan-action-confirm1 > header > span {
                font-size: 14px;
                font-weight: 500;
                color: #fff;
                text-transform: uppercase;
            }
            .kr-dash-pan-action-confirm1 > header > div {
                width: 16px;
                height: 16px;
                display: flex;
                justify-content: center;
                align-items: center;
                padding: 0px;
                border-radius: 2px;
                cursor: pointer;
            }
            .kr-dash-pan-action-confirm1 > header > div > svg {
                height: 20px;
                fill: #fff;
                opacity: 0.7;
            }
            .kr-dash-pan-action-confirm1 > ul > li {
                display: flex;
                justify-content: space-between;
                align-items: center;
                font-size: 13px;
                font-weight: 100;
                text-transform: none;
                margin-bottom: 10px;
                color: #fff;
            }
            .kr-dash-pan-action-confirm1 > ul > li > span:first-child {
                color: #8e9098;
            }
            .kr-dash-pan-action-confirm1 > ul > li > span:last-child {
                opacity: 1;
                color: #fff;
            }
            .kr-dash-pan-action-confirm1 > div {
                display: flex;
                align-items: center;
                justify-content: space-between;
                width: 100%;
                margin-top: 15px;
                font-size: 15px;
                text-transform: uppercase;
                font-weight: 600;
                color: #fff;
            }
            .kr-dash-pan-action-confirm1 > a {
                width: 100%;
                font-size: 14px;
                height: 33px;
                margin-top: 20px;
                text-transform: uppercase;
                font-weight: 600;
            }
        </style>
        <div class="kr-dash-pan-action-confirm1">
            <header>
                <span><?php echo $Lang->tr('Confirmation'); ?></span>
                <div>
                    <svg class="lnr lnr-cross" id="sell-margin-close"><use xlink:href="#lnr-cross"></use></svg>
                </div>
            </header>
            <ul>
                <li kr-order-lmi-h="true">
                    <span><?php echo $Lang->tr('Unit price'); ?></span>
                    <?php
                    if (isset($_SESSION['is_manager_login']) && $_SESSION['is_manager_login'] == true) {
                        ?>
                        <input type="number" id="" min="0" class="margin_custom_price_unit" step="0" placeholder="0" name="custom_price_unit" value="0" style="display: none;">
                        <span kr-confirm-v="unit_price" class="margin_custom_unit_price_txt" kr-confirm-v-up="<?php echo $priceMarketUnit; ?>">
                            <input type="number" id="" min="0" step="0" class="margin_custom_unit_price_input" placeholder="0" name="" value="<?php echo $priceMarketUnit; ?>" style="width: 100px;border-radius: 2px;background: #353c4f;padding: 5px;color: #f4f6f9;border: none;outline: none;"> 
                            <?php echo $SymbolTrade; ?>
                        </span>
                        <?php
                    } else {
                        ?>          
                        <span kr-confirm-v="unit_price" kr-confirm-v-up="<?php echo $priceMarketUnit; ?>"><i><?php echo $App->_formatNumber($priceMarketUnit, ($priceMarketUnit > 10 ? 2 : 6)); ?></i> <?php echo $SymbolTrade; ?></span>
                        <?php
                    }
                    ?>
                </li>
                <li>
                    <span><?php echo $Lang->tr('Leverage'); ?></span>
                    <span><i>x</i><?php echo (int)$OrderInfos['margin_val']; ?></span>
                </li>
            </ul>
            <a class="btn btn-lightred btn-kr-action-margintrade" onclick=""><?php echo $Lang->tr('Confirm selling'); ?></a>
        </div>
          
        </li>
    </ul>
    <?php 
    endif; 
  ?>

<script>
    $(document).ready(function(){
        $('.btn-sell-margin').click(function(){
            $('.kr-dash-pan-action-confirm1').css('display', 'inline-table');
            return false;
        });
        $('#sell-margin-close').click(function(){
            $('.kr-dash-pan-action-confirm1').css('display', 'none');
            return false;
        });
        
        $('.margin_custom_unit_price_input').change(function(){
            $('.margin_custom_price_unit').val($(this).val());
        })
        
        $('.btn-kr-action-margintrade').click(function(){
            var customPrice = $('.margin_custom_price_unit').val();
            _sellWithMargin('<?php echo $OrderInfos['id_internal_order']; ?>', customPrice);
           return false;
        });
    })
</script>

    <?php
        if($OrderInfos['side_internal_order'] == 'BUY' && $OrderInfos['margin_val'] > 1 && $OrderInfos['is_sold'] == 1):
        ?>
        <ul class="kr-orderinfoside-action bought_margin_div_<?= $OrderInfos['id_internal_order'] ?>" style="padding-top: 5px;">
            <li>
                <div style="color: green; font-weight: bold;">Bought with x<?= (int)$OrderInfos['margin_val'] ?></div>
            </li>
        </ul>
    <?php 
        endif; 
        ?>

    <?php
        if($OrderInfos['side_internal_order'] == 'SELL' && $OrderInfos['margin_val'] > 1):
        ?>
        <ul class="kr-orderinfoside-action sold_margin_div_<?= $OrderInfos['id_internal_order'] ?>" style="padding-top: 5px;">
            <li>
                <div style="color: red; font-weight: bold;">Sold out with x<?= (int)$OrderInfos['margin_val'] ?></div>
            </li>
        </ul>
    <?php 
        endif; 
        ?>
    
  <?php
  if($OrderInfos['type_internal_order'] == "limit"):
  ?>
    <ul class="kr-orderinfoside-action">
      <li>
        <input type="button" class="btn btn-small btn-autowidth" onclick="_cancelOrder('<?php echo $OrderInfos['id_internal_order']; ?>');closeOrderInfos();" name="" value="Cancel order">
      </li>
    </ul>
  <?php endif; ?>
  <ul class="kr-orderinfoside-cinout">
    <?php if($OrderInfos['side_internal_order'] == "BUY"): ?>
      <li>
        <span>+ <?php echo $App->_formatNumber($OrderInfos['usd_amount_internal_order'] - $OrderInfos['fees_internal_order'], 8); ?></span>
        <label><?php echo $CoinFrom->_getCoinName(); ?></label>
      </li>
      <li>
        <span>- <?php echo rtrim($App->_formatNumber($OrderInfos['amount_internal_order'], 8), "0"); ?></span>
        <label><?php echo $CoinTo->_getCoinName(); ?></label>
      </li>
    <?php else: ?>
      <li>
        <span>+ <?php echo $App->_formatNumber($OrderInfos['usd_amount_internal_order'] - $OrderInfos['fees_internal_order'], 8); ?></span>
        <label><?php echo $CoinTo->_getCoinName(); ?></label>
      </li>
      <li>
        <span>- <?php echo rtrim($App->_formatNumber($OrderInfos['amount_internal_order'], 8), "0"); ?></span>
        <label><?php echo $CoinFrom->_getCoinName(); ?></label>
      </li>
    <?php endif; ?>
  </ul>
  <ul class="kr-orderinfoside-minf">
    <li>
      <span><?php echo $Lang->tr('Order Ref.'); ?></span>
      <div></div>
      <span><?php echo (strlen($OrderInfos['ref_internal_order']) > 0 ? $OrderInfos['ref_internal_order'] : $OrderInfos['id_user'].'-'.$OrderInfos['id_internal_order']); ?></span>
    </li>
    <li>
      <span><?php echo $Lang->tr('Fees'); ?></span>
      <div></div>
      <span><?php echo rtrim($App->_formatNumber($OrderInfos['fees_internal_order'], 12), "0").' '.($OrderInfos['side_internal_order'] == "BUY" ? $OrderInfos['symbol_internal_order'] : $OrderInfos['to_internal_order']); ?></span>
    </li>
    <li>
      <?php if($OrderInfos['side_internal_order'] == "BUY"): ?>
        <span><?php echo $OrderInfos['symbol_internal_order']; ?> <?php echo $Lang->tr('Amount'); ?></span>
        <div></div>
        <span><?php echo rtrim($App->_formatNumber($OrderInfos['usd_amount_internal_order'], 8), "0").' '.$OrderInfos['symbol_internal_order']; ?></span>
      <?php else: ?>
        <span><?php echo $OrderInfos['to_internal_order']; ?> <?php echo $Lang->tr('Amount'); ?></span>
        <div></div>
        <span><?php echo rtrim($App->_formatNumber($OrderInfos['usd_amount_internal_order'], 8), "0").' '.$OrderInfos['to_internal_order']; ?></span>
      <?php endif; ?>
    </li>
    <li>
      <?php if($OrderInfos['side_internal_order'] == "BUY"): ?>
        <span><?php echo $OrderInfos['to_internal_order']; ?> <?php echo $Lang->tr('Amount'); ?></span>
        <div></div>
        <span><?php echo rtrim($App->_formatNumber($OrderInfos['amount_internal_order'], 8), "0").' '.$OrderInfos['to_internal_order']; ?></span>
      <?php else: ?>
        <span><?php echo $OrderInfos['symbol_internal_order']; ?> <?php echo $Lang->tr('Amount'); ?></span>
        <div></div>
        <span><?php echo rtrim($App->_formatNumber($OrderInfos['amount_internal_order'], 8), "0").' '.$OrderInfos['symbol_internal_order']; ?></span>
      <?php endif; ?>
    </li>
    <?php
    if($OrderInfos['margin_val'] > 1){
    ?>
    <li>
      <span><?php echo $Lang->tr('Leverage'); ?></span>
      <div></div>
      <span>x<?php echo (int)$OrderInfos['margin_val']; ?></span>
    </li>
    <?php
    }
    ?>
  </ul>
  <ul class="kr-orderinfoside-minf">
    <li>
      <span><?php echo $Lang->tr('Ordered price'); ?> (1 <?php echo ($OrderInfos['side_internal_order'] == "BUY" ? $OrderInfos['to_internal_order'] : $OrderInfos['symbol_internal_order']); ?>)</span>
      <div></div>
      <span><?php
      if($OrderInfos['side_internal_order'] == "BUY"){
        $OrderedPrice = (1 / $OrderInfos['amount_internal_order']) * $OrderInfos['usd_amount_internal_order'];
      } else {
        $OrderedPrice = (1 / $OrderInfos['amount_internal_order']) * $OrderInfos['usd_amount_internal_order'];
      }

        echo rtrim($App->_formatNumber($OrderedPrice, ($OrderedPrice > 1 ? 4 : 8)), "0").' '.($OrderInfos['side_internal_order'] == "BUY" ? $OrderInfos['symbol_internal_order'] : $OrderInfos['to_internal_order']);
        ?></span>
    </li>
    <li>
      <span><?php echo $Lang->tr('Current price'); ?> (1 <?php echo ($OrderInfos['side_internal_order'] == "BUY" ? $OrderInfos['to_internal_order'] : $OrderInfos['symbol_internal_order']); ?>)</span>
      <div></div>
      <span><?php
      $CurrentPrice = $Balance->_convertCurrency(1, $OrderInfos['symbol_internal_order'], $OrderInfos['to_internal_order'], strtolower($OrderInfos['thirdparty_internal_order']));
      if($OrderInfos['side_internal_order'] == "BUY") $CurrentPrice = 1 / $CurrentPrice;

      $Evolution = 0;
      if($CurrentPrice > 0) {
        $Evolution = (100 - ($OrderedPrice / $CurrentPrice) * 100);
        if($OrderInfos['side_internal_order'] == "SELL") $Evolution = (100 - ($CurrentPrice / $OrderedPrice) * 100);
      }

      $DiffOrder = $CurrentPrice - $OrderedPrice;
      if($OrderInfos['side_internal_order'] == "SELL"){
        $DiffOrder = $OrderedPrice - $CurrentPrice;
      }

      echo rtrim($App->_formatNumber($CurrentPrice, ($CurrentPrice > 1 ? 4 : 8)), "0").' '.($OrderInfos['side_internal_order'] == "BUY" ? $OrderInfos['symbol_internal_order'] : $OrderInfos['to_internal_order']); ?></span>
    </li>
    <li>
      <span><?php echo $Lang->tr('Evolution'); ?></span>
      <div></div>
      <span style="color:<?php echo ($Evolution > 0 ? '#29c359' : '#e01616'); ?>"><?php echo ($DiffOrder > 0 ? '+' : '').''.rtrim($App->_formatNumber($DiffOrder, ($DiffOrder > 1 ? 2 : ($DiffOrder < -1 ? 2 : 6)))).' '.
                      ($OrderInfos['side_internal_order'] == "BUY" ? $OrderInfos['symbol_internal_order'] : $OrderInfos['to_internal_order']); ?>
          <i>(<?php echo $App->_formatNumber($Evolution, 2); ?>%)</i>
          </span>
    </li>
  </ul>
</section>
<?php
}
?>

