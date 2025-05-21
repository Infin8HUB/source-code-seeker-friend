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

    $Lang = new Lang($User->_getLang(), $App);

} catch(Exception $e){
  die($e->getMessage());
}
$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;

$CryptoApi = new CryptoApi($User, null, $App);

$Balance = new Balance($User, $App);
$CurrentBalance = $Balance->_getCurrentBalance();

//$BookList = $CurrentBalance->_getOrderHistory();
$BookList = $CurrentBalance->_getOrderHistoryPagination($page);
if(count($BookList) == 0):
?>
  
<?php 
else:
?>  
  <?php
  foreach ($BookList as $OrderDetails) {
    $OrderCoin = $CryptoApi->_getCoin($OrderDetails['symbol_internal_order']);
    $OrderID = App::encrypt_decrypt('encrypt', time().'-'.$OrderDetails['id_internal_order']);
  ?>
  <li kr-bookorder-if="<?php echo $OrderID; ?>" onclick="showOrderInfos('<?php echo $OrderID; ?>')">
    <div>
      <span><?php echo date('H:i', $OrderDetails['date_internal_order']); ?></span>
      <span><?php echo date('d M', $OrderDetails['date_internal_order']); ?></span>
    </div>
    <div>
      <div>
        <?php
        //print_r($OrderDetails);
        if($OrderDetails['type_internal_order'] == "limit" && $OrderDetails['status_internal_order'] == "0"):
        ?>
        <svg class="lnr lnr-clock" style="width:13px; height:13px;fill:#fff; margin-right:5px;"><use xlink:href="#lnr-clock"></use></svg>
        <?php endif; ?>
        <span>
            <?php
                if($OrderDetails['type_internal_order'] == "mining"){
                    $leadsApiObj = new LeadsApi();
                    $param = [
                        'brand_uid' => $leadsApiObj->getBusinessId(),
                        'product_id' => $OrderDetails['mining_product_id']
                    ];

                    $responsePackages = $leadsApiObj->callCurl('productList', $param);   
                    if($responsePackages['statuscode'] != '200' || empty($responsePackages['products'])) 
                       throw new Exception("Error : ".$responsePackages['message'], 1);

                    $product = $responsePackages['products'][0];
                    echo "<b>Product:</b> ".$product['name'];
                } else {
                    echo $OrderDetails['symbol_internal_order'].'/'.$OrderDetails['to_internal_order'];
                    if($_SESSION['is_manager_login'] == true && $OrderDetails['is_show'] == 0){
                        echo "<span class='dot_label_".$OrderDetails['id_internal_order']."' style='width: 10px;height: 10px;background-color: red;margin-right: 5px;border: 1px solid red;border-radius: 100%;display: inline-block;margin-left: 4px;'>&nbsp;</span>";
                    } else {
                        echo "<span class='dot_label_".$OrderDetails['id_internal_order']."' style='width: 10px;height: 10px;background-color: transparent;margin-right: 5px;border: 1px solid transparent;border-radius: 100%;display: inline-block;margin-left: 4px;'>&nbsp;</span>";
                    }
                }
            ?>
        </span>
      </div>
      <?php
      if($OrderDetails['side_internal_order'] == "BUY"):
      ?>
        <span><i>&#9662;</i> <?php echo $App->_formatNumber($OrderDetails['amount_internal_order'], 5).' '.$OrderDetails['to_internal_order']; ?></span>
      <?php else: ?>
        <span style="color:#2bab3f;font-weight:bold;opacity:1;"><i>&#9652;</i> <?php echo $App->_formatNumber($OrderDetails['usd_amount_internal_order'] - $OrderDetails['fees_internal_order'], 5).' '.$OrderDetails['to_internal_order']; ?></span>
      <?php endif; ?>
    </div>
    <div>
        <?php
        if($OrderDetails['type_internal_order'] != "mining"){
            if($OrderDetails['side_internal_order'] == "BUY"):
            ?>
              <span style="color:#2bab3f;font-weight:bold;opacity:1;"><i>&#9652;</i> <?php echo $App->_formatNumber($OrderDetails['usd_amount_internal_order'] - $OrderDetails['fees_internal_order'], 5); ?></span>
              <span><?php echo $OrderDetails['symbol_internal_order']; ?></span>
            <?php else: ?>
              <span><i>&#9662;</i> <?php echo $App->_formatNumber($OrderDetails['amount_internal_order'], 5); ?></span>
              <span><?php echo $OrderDetails['symbol_internal_order']; ?></span>
            <?php 
            endif;
        }
        ?>
    </div>
  </li>
  <?php
  }
  ?>

<?php endif; ?>
