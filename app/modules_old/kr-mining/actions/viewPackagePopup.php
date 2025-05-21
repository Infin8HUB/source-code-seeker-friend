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

    /* $Widthdraw = new Widthdraw();
      $WidthdrawConfiguration = $Widthdraw->_getWidthdrawMethod();

      $infos = $Widthdraw->_getInformationWithdrawMethod(App::encrypt_decrypt('decrypt', $_GET['id']));

      if(!$infos) throw new Exception("Error : Withdraw method not found", 1);

      if(!array_key_exists($infos['type_user_widthdraw'], $WidthdrawConfiguration)) throw new Exception("Permission denied", 1);
      $WidthdrawConfiguration = $WidthdrawConfiguration[$infos['type_user_widthdraw']];

      $FieldWithdrawMethod = json_decode($infos['value_user_widthdraw'], true);
      */
    $Balance = new Balance($User, $App);
    //$Balance = $Balance->_getCurrentBalance(); 
    $userUSDTBalance = $Balance->getBalanceByCoin("USDT"); 
    
    $leadsApiObj = new LeadsApi();       
    $paramCurrency = [
        'brand_id' => $leadsApiObj->getBusinessId()
    ];
    $currencyName = 'USD';
    $responseCurrency = $leadsApiObj->callCurl('getBrandDefaultCurrency', $paramCurrency);
    if(isset($responseCurrency['statuscode']) && $responseCurrency['statuscode'] == '200'){
        $currencyName = $responseCurrency['data']['name'];
    }
    
    $leadsApiObj = new LeadsApi();       
    $param = [
        'brand_uid' => $leadsApiObj->getBusinessId(),
        'product_id' => $_GET['id']
    ];
    $responsePackages = $leadsApiObj->callCurl('productList', $param);
    //echo "<pre>";print_r($responsePackages);exit;
    if($responsePackages['statuscode'] != '200' || empty($responsePackages['products'])) 
        throw new Exception("Error : ".$responsePackages['message'], 1);
    
    $product = $responsePackages['products'][0];
    $days = $product['exp_days'];
    $fdate = date('Y-m-d');
    $tdate = date('Y-m-d', strtotime($fdate. ' + '.$days.' days'));
    /*$tdate = $product['exp_date'];
    $datetime1 = new DateTime($fdate);
    $datetime2 = new DateTime($tdate);
    $interval = $datetime1->diff($datetime2);
    $days = $interval->format('%a');*/
    $revenue = number_format(($product['price'] * $product['min_revenue_per_click']) / 100, 0);
    $revenue_max = number_format(($product['price'] * $product['max_revenue_per_click']) / 100, 0);
    $paybackPeriod = ($product['min_revenue_per_click'] - 100);
    $paybackPeriod_max = ($product['max_revenue_per_click'] - 100);
    $interest_desc = "";
    if ($paybackPeriod_max > $paybackPeriod) {
        $interest_desc = "upto<br>" . $paybackPeriod_max.'%';
        $revenue_desc = "upto<br>" . $revenue_max;
    } else if($paybackPeriod_max == $paybackPeriod) {
        $interest_desc = $paybackPeriod.'%';
        $revenue_desc = $revenue;
    } else {
        $interest_desc = "upto<br>".$paybackPeriod.'%';
        $revenue_desc = $revenue;
    }
	
    ?>
    <section class="kr-contact-zone kr-ov-nblr">
    <section>
        <!--<div class="kr-contact-zone-image">-->
        <div style="padding: 20px;">
            <img src="<?=$product['p_image']?>" style="width: 100%;"/>
        </div>
        <div>
            <? /* 
			<header style="justify-content:center;padding: 0px;margin: 0px;">
                <h3><?php echo $Lang->tr('Product'); ?></h3>
            </header>
			*/ ?>
            <h2 style="margin-top: 15px;">
                <?=$product['name']?>
                <br>
                <span style="font-size: 11px;text-transform: none;"><?=$product['short_descr']?></span>
            </h2>
            <ul>
                <!--<li>
                    <span><?php echo $Lang->tr('Category'); ?></span>
                    <span><?=$product['catName']?></span>
                </li>-->
                <li>
                    <span><?php echo $Lang->tr('Expire On'); ?></span>
                    <span><?=$tdate?></span>
                </li>
                <li>
                    <span><?php echo $Lang->tr('Payback Period'); ?></span>
                    <span><?=$days?> Days</span>
                </li>
                <li>
                    <span><?php echo $Lang->tr('Interest'); ?></span>
                    <span><?=$interest_desc?></span>
                </li>
                <li>
                    <span><?php echo $Lang->tr('Price'); ?></span>
                    <span><?=number_format($product['price']).' '.$currencyName?></span>
                </li>
                <li>
                    <span><?php echo $Lang->tr('Return Per Period'); ?></span>
                    <span><?=$revenue_desc.' '.$currencyName?></span>
                </li>
                
            </ul>
            <div style="margin-bottom: 10px;">
                <span style="float: left;">
                    <img src="<?=APP_URL.'/assets/img/icons/crypto/USDT.svg'?>" style="width: 34px;height: 34px;"/>
                </span>
                <span style="float: left;margin-top: 7px;margin-left: 2px;">You have <b><?=$userUSDTBalance.' '.$currencyName?></b> in real balance.</span>
            </div>
            <div>
                <button type="button" onclick="_closeContactPopup();" class="btn-welcome-cfg-gdax-dil btn btn-shadow btn-grey btn-autowidth" style="float: left;"><?php echo $Lang->tr('Close'); ?></button>
                <button type="button" onclick="_buyPackage('<?=$product['pId']?>');" class="btn btn-shadow btn-autowidth" style="float: right;margin-right: 12px;">Buy Now</button>
            </div>
            
        </div>
    </section>
</section>    
<?php
} catch (Exception $e) {
    die(json_encode([
        'error' => 1,
        'msg' => $e->getMessage()
    ]));
}
?>


