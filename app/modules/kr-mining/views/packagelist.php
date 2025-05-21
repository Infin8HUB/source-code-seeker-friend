<?php
/**
 * Coin list market analytic view
 *
 * @package Krypto
 * @author Ovrley <hello@ovrley.com>
 */
session_start();

require "../../../../config/config.settings.php";
require $_SERVER['DOCUMENT_ROOT'] . FILE_PATH . "/vendor/autoload.php";
require $_SERVER['DOCUMENT_ROOT'] . FILE_PATH . "/app/src/MySQL/MySQL.php";
require $_SERVER['DOCUMENT_ROOT'] . FILE_PATH . "/app/src/App/App.php";
require $_SERVER['DOCUMENT_ROOT'] . FILE_PATH . "/app/src/App/AppModule.php";
require $_SERVER['DOCUMENT_ROOT'] . FILE_PATH . "/app/src/User/User.php";
require $_SERVER['DOCUMENT_ROOT'] . FILE_PATH . "/app/src/Lang/Lang.php";
require $_SERVER['DOCUMENT_ROOT'] . FILE_PATH . "/app/src/CryptoApi/CryptoGraph.php";
require $_SERVER['DOCUMENT_ROOT'] . FILE_PATH . "/app/src/CryptoApi/CryptoHisto.php";
require $_SERVER['DOCUMENT_ROOT'] . FILE_PATH . "/app/src/CryptoApi/CryptoCoin.php";
require $_SERVER['DOCUMENT_ROOT'] . FILE_PATH . "/app/src/CryptoApi/CryptoApi.php";

// Load app modules
$App = new App(true);
$App->_loadModulesControllers();

// Check if user is logged
$User = new User();
if (!$User->_isLogged())
    die("You are not logged");

// Init lang object
$Lang = new Lang($User->_getLang(), $App);

// Init CryptoApi object
$CryptoApi = new CryptoApi($User, null, $App);

$pagenum = 1;
if (!empty($_POST) && !empty($_POST['page']) && is_numeric($_POST['page'])) {
    $pagenum = $_POST['page'];
}
$common_search = "";
if (!empty($_POST) && !empty($_POST['common_search'])) {
    $common_search = $_POST['common_search'];
}
$category_id = 0;
if (!empty($_POST) && !empty($_POST['category_id']) && is_numeric($_POST['category_id'])) {
    $category_id = $_POST['category_id'];
}
$limit = 25;

$leadsApiObj = new LeadsApi();
$param = [
    'brand_uid' => $leadsApiObj->getBusinessId(),
    'common_search' => $common_search,
    'category_id' => $category_id,
    'page_index' => $pagenum,
    'per_page' => $limit
];
$responsePackages = $leadsApiObj->callCurl('productList', $param);
//$responsePackageCategory = $leadsApiObj->callCurl('productCategoryList', $param);
$responsePackageCategory = [];
//echo "<pre>";print_r($responsePackages);exit;

$leadsApiObj = new LeadsApi();
$paramCurrency = [
    'brand_id' => $leadsApiObj->getBusinessId()
];
$currencyName = 'USD';
$responseCurrency = $leadsApiObj->callCurl('getBrandDefaultCurrency', $paramCurrency);
if (isset($responseCurrency['statuscode']) && $responseCurrency['statuscode'] == '200') {
    $currencyName = $responseCurrency['data']['name'];
}
?>
<div class="kr-marketcoinlist">

    <nav class="kr-marketnav">

        <ul>
            <li class="kr-nav-selected">Products List</li>
        </ul>
        <form class="kr-search-coin" onsubmit="search_product()" action="" method="post">
          <!--<input type="text" id="search-product-input" name="kr-search-value" placeholder="Search product ..." onkeyup="if(this.value.length > 3) search_product()" value="<?php echo $common_search; ?>" >-->
            <input type="text" id="search-product-input" name="kr-search-value" placeholder="Search product ..." value="<?php echo $common_search; ?>" autofocus="">
<?php
if (isset($responsePackageCategory['category']) && !empty($responsePackageCategory['category']) && isset($responsePackageCategory['statuscode']) && $responsePackageCategory['statuscode'] == '200') {
    ?>
                <select class="" id="search-product-category-select" name="search-product-select" onchange="search_product()" >
                    <option value="">Select Category...</option>
    <?php
    foreach ($responsePackageCategory['category'] as $Category) {
        ?>
                        <option <?= $category_id == $Category['id'] ? 'selected' : ''; ?> value="<?= $Category['id'] ?>"><?= $Category['name'] ?></option>
                        <?php
                    }
                    ?>
                </select>
                <?php } ?>
        </form>
    </nav>

    <div class="kr-marketlist" kr-currency-mm="<?php echo $CryptoApi->_getCurrency(); ?>" kr-currency-mm-symb="<?php echo $CryptoApi->_getCurrencySymbol(); ?>">
        <div class="kr-marketlist-header">
            <div class="kr-marketlist-n"></div>
            <div class="kr-marketlist-n"><span><?php echo $Lang->tr('Name'); ?></span></div>
            <div class="kr-marketlist-n"><span><?php echo $Lang->tr('Short Description'); ?></span></div>
            <!--<div class="kr-marketlist-n"><span><?php echo $Lang->tr('Category'); ?></span></div>-->
            <div class="kr-marketlist-n"><span><?php echo $Lang->tr('Price'); ?></span></div>
            <div class="kr-marketlist-n"><span><?php echo $Lang->tr('Payback Period'); ?></span></div>
            <div class="kr-marketlist-n"><span><?php echo $Lang->tr('Interest'); ?></span></div>
            <div class="kr-marketlist-n"><span><?php echo $Lang->tr('Return Per Period'); ?></span></div>
            <div class="kr-marketlist-n"><span><?php echo $Lang->tr('Action'); ?></span></div>

        </div>
<?php
if (isset($responsePackages['products']) && !empty($responsePackages['products']) && isset($responsePackages['statuscode']) && $responsePackages['statuscode'] == '200') {
    $totalProductCount = $responsePackages['num_products'];
    foreach ($responsePackages['products'] as $product) {

        $days = $product['exp_days'];
        /* $fdate = date('Y-m-d');
          $tdate = date('Y-m-d', strtotime($fdate. ' + '.$days.' days'));
          $datetime1 = new DateTime($fdate);
          $datetime2 = new DateTime($tdate);
          $interval = $datetime1->diff($datetime2);
          $days = $interval->format('%a'); */
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
                <div kr-symbol-mm="<?= $product['pId'] ?>" onclick="_showPackagePopup('<?= $product['pId'] ?>')" style="height: auto; min-height: fit-content;">
                    <div class="kr-marketlist-n">
                        <div class="kr-marketlist-n-nn">
                            <img src="<?= $product['p_image'] ?>">
                        </div>
                    </div>
                    <div class="kr-marketlist-n">
                        <span><?= $product['name'] ?></span>
                    </div>

                    <div class="kr-marketlist-n" style="width: 1px;">
                        <span><?= $product['short_descr'] ?></span>
                    </div>
                    <!--<div class="kr-marketlist-n">
                        <span><?= $product['catName'] ?></span>
                    </div>-->
                    <div class="kr-marketlist-n">
                        <span><?= number_format($product['price']) . ' ' . $currencyName ?></span>
                    </div>
                    <div class="kr-marketlist-n">
                        <span><?= $days ?> Days</span>
                    </div>
                    <div class="kr-marketlist-n">
                        <span><?= $interest_desc ?></span>
                    </div>
                    <div class="kr-marketlist-n">
                        <span><?= $revenue_desc . ' ' . $currencyName ?></span>
                    </div>
                    <div class="kr-marketlist-n">
                        <span><button type="button" class="btn btn-autowidth btn-green btn-small" name="button" onclick="_showPackagePopup('<?= $product['pId'] ?>')">Buy Now</button></span>
                    </div>


                </div>  
        <?php
    }

    if ($totalProductCount > $limit) {
        ?>
                <ul class="kr-admin-pagination kr-mining-pagination-coins" style="margin-top: 5%;">
                <?php
                for ($i = 0; $i < (ceil($totalProductCount) / $limit); $i++) { // Pagination system
                    echo '<li onclick="callmining_pagination(' . ($i + 1) . ')" style="max-width: 5%;' . ($pagenum == ($i + 1) ? 'background-color:#ef6c00;color:#fff;' : '') . '" kr-page="' . ($i + 1) . '">' . ($i + 1) . '</li>';
                }
                ?>
                </ul>  
                <?php
            }
        } else {
            
        }
        ?>

        <!--<div kr-symbol-mm="BTC" onclick="_showWithdrawMethod(1)" style="height: auto; min-height: fit-content;">
            <div class="kr-marketlist-n">
                <div class="kr-marketlist-n-nn">
                  <img src="https://media.istockphoto.com/photos/data-mining-concept-picture-id682618568">
                </div>
            </div>
            <div class="kr-marketlist-n">
                <span>Bitcoin</span>
            </div>
            
            <div class="kr-marketlist-n" style="width: 1px;">
                <span>some text goes here. some text goes here. some text goes here. some text goes here. some text goes here.</span>
            </div>
            <div class="kr-marketlist-n">
                <span>Package</span>
            </div>
            <div class="kr-marketlist-n">
              <span>$11,769.79</span>
            </div>
            <div class="kr-marketlist-n">
              <span>12 Days</span>
            </div>
            <div class="kr-marketlist-n">
              <span>$4.92</span>
            </div>
            <div class="kr-marketlist-n">
              <span><button type="button" class="btn btn-autowidth btn-green btn-small" name="button" onclick="_showWithdrawMethod(1)">Buy Now</button></span>
            </div>
            
            
          </div>  
        <div kr-symbol-mm="ETH" onclick="_showWithdrawMethod(1)" style="height: auto; min-height: fit-content;">
            <div class="kr-marketlist-n">
                <div class="kr-marketlist-n-nn">
                  <img src="https://content3.jdmagicbox.com/comp/bangalore/l7/080pxx80.xx80.171016185257.y6l7/catalogue/kushi-travels-kstdc-tour-packages-bangalore-phdh9oyoks.jpg">
                </div>
            </div>
            <div class="kr-marketlist-n">
                <span>Ethereum</span>
            </div>
            
            <div class="kr-marketlist-n" style="width: 1px;">
                <span>some text goes here.</span>
            </div>
            <div class="kr-marketlist-n">
                <span>Package</span>
            </div>
            <div class="kr-marketlist-n">
              <span>$211.43</span>
            </div>
            <div class="kr-marketlist-n">
              <span>10 Days</span>
            </div>
            <div class="kr-marketlist-n">
              <span>$1.52</span>
            </div>
            <div class="kr-marketlist-n">
              <span><button type="button" class="btn btn-autowidth btn-green btn-small" name="button" onclick="_showWithdrawMethod(1)">Buy Now</button></span>
            </div>
            
          </div>  
        <div kr-symbol-mm="BTC" onclick="_showWithdrawMethod(1)" style="height: auto; min-height: fit-content;">
            <div class="kr-marketlist-n">
                <div class="kr-marketlist-n-nn">
                  <img src="https://media.istockphoto.com/photos/business-analytics-technology-concept-icons-businessman-data-mining-picture-id850854330">
                </div>
            </div>
            <div class="kr-marketlist-n">
                <span>Bitcoin</span>
            </div>
            
            <div class="kr-marketlist-n" style="width: 1px;">
                <span>some text goes here. some text goes here. some text goes here. some text goes here. some text goes here.</span>
            </div>
            <div class="kr-marketlist-n">
                <span>Package</span>
            </div>
            <div class="kr-marketlist-n">
              <span>$11,769.79</span>
            </div>
            <div class="kr-marketlist-n">
              <span>12 Days</span>
            </div>
            <div class="kr-marketlist-n">
              <span>$4.92</span>
            </div>
            <div class="kr-marketlist-n">
              <span><button type="button" class="btn btn-autowidth btn-green btn-small" name="button" onclick="_showWithdrawMethod(1)">Buy Now</button></span>
            </div>
            
            
          </div> 
        <div kr-symbol-mm="ETH" onclick="_showWithdrawMethod(1)" style="height: auto; min-height: fit-content;">
            <div class="kr-marketlist-n">
                <div class="kr-marketlist-n-nn">
                  <img src="https://content3.jdmagicbox.com/comp/bangalore/l7/080pxx80.xx80.171016185257.y6l7/catalogue/kushi-travels-kstdc-tour-packages-bangalore-phdh9oyoks.jpg">
                </div>
            </div>
            <div class="kr-marketlist-n">
                <span>Ethereum</span>
            </div>
            
            <div class="kr-marketlist-n" style="width: 1px;">
                <span>some text goes here.</span>
            </div>
            <div class="kr-marketlist-n">
                <span>Package</span>
            </div>
            <div class="kr-marketlist-n">
              <span>$211.43</span>
            </div>
            <div class="kr-marketlist-n">
              <span>10 Days</span>
            </div>
            <div class="kr-marketlist-n">
              <span>$1.52</span>
            </div>
            <div class="kr-marketlist-n">
              <span><button type="button" class="btn btn-autowidth btn-green btn-small" name="button" onclick="_showWithdrawMethod(1)">Buy Now</button></span>
            </div>
            
          </div> -->  
    </div>

</div>
