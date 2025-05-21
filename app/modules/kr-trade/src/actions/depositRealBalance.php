<?php

/**
 * Load data balance
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

require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/User/User.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/Lang/Lang.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoOrder.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoNotification.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoIndicators.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoGraph.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoHisto.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoCoin.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoApi.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/Leads8Api/LeadsApi.php";

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

    if(!$App->_hiddenThirdpartyActive()) throw new Exception("Permission denied", 1);
    if(is_null($App->_hiddenThirdpartyServiceCfg()) || count($App->_hiddenThirdpartyServiceCfg()) == 0) throw new Exception("You must activate at least 1 exchange (Admin -> Trading)", 1);



    $Balance = new Balance($User, $App, 'real');

    $BalanceList = $Balance->_getBalanceListResum();
    $symbolFetched = array_keys($BalanceList)[0];
    if(!isset($_POST['symbol']) || empty($_POST['symbol'])) $_POST['symbol'] = "BTC";
    $typeFetched = 'symbol';
    if(!empty($_POST) && isset($_POST['symbol']) && (array_key_exists($_POST['symbol'], $BalanceList) || in_array($_POST['symbol'], $App->_getListCurrencyDepositAvailable()))) $symbolFetched = $_POST['symbol'];
    if(!empty($_POST) && isset($_POST['type']) && $_POST['type'] == "bank_transfert") {
      $typeFetched = $_POST['type'];
      $symbolFetched = "null";
    }

    $Widthdraw = new Widthdraw($User);

    $IsRealMoney = $Balance->_symbolIsMoney($symbolFetched);

    $PaymentMethodList = $Widthdraw->_getWidthdrawMethod(($IsRealMoney ? 'currency' : 'crypto'));

    $BalanceListDeposit = $Balance->_getDepositListAvailable();
    
    $leadsApiObj = new LeadsApi();
    $brandId = $leadsApiObj->getBusinessId();
    $param = [
        'brand_uid' => $brandId
    ];
    $gateWayData = $leadsApiObj->callCurl('get_brand_gateway_setting', $param);
    
    $l8UserId = $_SESSION['leads_userid'];
    $params = [
        'brand_id' => $brandId,
        'user_id' => $l8UserId
    ];
    $responseData = $leadsApiObj->callCurl('get_customer_details', $params);
    $userData = array();
    if(isset($responseData['statuscode']) && $responseData['statuscode'] == '200'){
        $userData = $responseData['data'];
    }
    
    $paramCustomField = [
        'brand_uid' => $brandId,
        'customer_uid' => $l8UserId
    ];
    $responseCustomFields = $leadsApiObj->callCurl('getCustomerCustomFields', $paramCustomField);
    $_SESSION['maxleverage'] = 0;
    $_SESSION['payneteasy_payment'] = 'disabled';    
    $_SESSION['gumballpay_payment'] = 'disabled';    
    if(isset($responseCustomFields['statuscode']) && $responseCustomFields['statuscode'] == '200'){
        $_SESSION['maxleverage'] = isset($responseCustomFields['data']['maxleverage']) ? $responseCustomFields['data']['maxleverage'] : 0;
        $_SESSION['payneteasy_payment'] = isset($responseCustomFields['data']['payneteasy']) ? $responseCustomFields['data']['payneteasy'] : 'disabled';
        $_SESSION['gumballpay_payment'] = isset($responseCustomFields['data']['gumballpay']) ? $responseCustomFields['data']['gumballpay'] : 'disabled';
    }
    
    $usStates = array(
        'AL'=>'Alabama',
        'AK'=>'Alaska',
        'AZ'=>'Arizona',
        'AR'=>'Arkansas',
        'CA'=>'California',
        'CO'=>'Colorado',
        'CT'=>'Connecticut',
        'DE'=>'Delaware',
        'DC'=>'District of Columbia',
        'FL'=>'Florida',
        'GA'=>'Georgia',
        'HI'=>'Hawaii',
        'ID'=>'Idaho',
        'IL'=>'Illinois',
        'IN'=>'Indiana',
        'IA'=>'Iowa',
        'KS'=>'Kansas',
        'KY'=>'Kentucky',
        'LA'=>'Louisiana',
        'ME'=>'Maine',
        'MD'=>'Maryland',
        'MA'=>'Massachusetts',
        'MI'=>'Michigan',
        'MN'=>'Minnesota',
        'MS'=>'Mississippi',
        'MO'=>'Missouri',
        'MT'=>'Montana',
        'NE'=>'Nebraska',
        'NV'=>'Nevada',
        'NH'=>'New Hampshire',
        'NJ'=>'New Jersey',
        'NM'=>'New Mexico',
        'NY'=>'New York',
        'NC'=>'North Carolina',
        'ND'=>'North Dakota',
        'OH'=>'Ohio',
        'OK'=>'Oklahoma',
        'OR'=>'Oregon',
        'PA'=>'Pennsylvania',
        'RI'=>'Rhode Island',
        'SC'=>'South Carolina',
        'SD'=>'South Dakota',
        'TN'=>'Tennessee',
        'TX'=>'Texas',
        'UT'=>'Utah',
        'VT'=>'Vermont',
        'VA'=>'Virginia',
        'WA'=>'Washington',
        'WV'=>'West Virginia',
        'WI'=>'Wisconsin',
        'WY'=>'Wyoming',
    );

    $canadaStates = array(
        'AB' => "Alberta",
        'BC' => "British Columbia",
        'MB' => "Manitoba",
        'NB' => "New Brunswick",
        'NL' => "Newfoundland",
        'NT' => "Northwest Territories",
        'NS' => "Nova Scotia",
        'NU' => "Nunavut",
        'ON' => "Ontario",
        'PE' => "Prince Edward Island",
        'QC' => "Quebec",
        'SK' => "Saskatchewan",
        'YT' => "Yukon",
      );
    
    $australianStates = array(
        "NSW" => "New South Wales",
        "VIC" => "Victoria",
        "QLD" => "Queensland",
        "TAS" => "Tasmania",
        "SA" => "South Australia",
        "WA" => "Western Australia",
        "NT" => "Northern Territory",
        "ACT" => "Australian Capital Territory"
    );
    $japanStates = array(
        "YN" => "Yamanashi",
        "NG" => "Nagano",
        "GF" => "Gifu",
        "SZ" => "Shizuoka",
        "AI" => "Aichi",
        "ME" => "Mie",
        "SG" => "Shiga",
        "KT" => "Kyoto",
        "OS" => "Osaka",
        "HG" => "Hyogo",
        "NR" => "Nara",
        "WK" => "Wakayama",
        "TT" => "Tottori",
        "SM" => "Shimane",
        "OK" => "Okayama",
        "HR" => "Hiroshima",
        "YG" => "Yamaguchi",
        "TS" => "Tokushima",
        "KG" => "Kagawa",
        "EH" => "Ehime",
        "KC" => "Kouchi",
        "FO" => "Fukuoka",
        "SA" => "Saga",
        "NS" => "Nagasaki",
        "KM" => "Kumamoto",
        "OI" => "Ooita",
        "MZ" => "Miyazaki",
        "KS" => "Kagoshima",
        "HK" => "Hokkaido",
        "MY" => "Miyagi",
        "FK" => "Fukushima",
        "GU" => "Gunma",
        "TK" => "Tokyo",
        "TY" => "Toyama",
        "AO" => "Aomori",
        "IW" => "Iwate",
        "AK" => "Akita",
        "YM" => "Yamagata",
        "IB" => "Ibaragi",
        "TC" => "Tochigi",
        "SI" => "Saitama",
        "CB" => "Chiba",
        "KN" => "Kanagawa",
        "NI" => "Niigata",
        "IS" => "Ishikawa",
        "FI" => "Fukui"
    );
} catch (\Exception $e) {
  echo '<b style="color:#fff;">'.$e->getMessage().'</b>';
  die();
}

?>
<div class="spinner" style="display:none;"></div>
<section class="kr-balance-credit-drel-cont" kr-bssymbol="<?php echo $symbolFetched; ?>">
    <?php
    $isAnyPaymentMethod = false;
    $sectionDisplay = 'display:block;';
    if(isset($gateWayData['gateway']) 
            && (in_array('paynet', $gateWayData['gateway']) || in_array('gumballpay', $gateWayData['gateway']) || in_array('neobank', $gateWayData['gateway']) || in_array('pound_pay', $gateWayData['gateway']) || in_array('ipasspay', $gateWayData['gateway']) || in_array('internet_cashbank', $gateWayData['gateway']) || in_array('syspg', $gateWayData['gateway']) || in_array('octovio', $gateWayData['gateway']) || in_array('amlnnode', $gateWayData['gateway']) || in_array('paystuido', $gateWayData['gateway']) || in_array('chargemoney', $gateWayData['gateway']) || in_array('kryptova', $gateWayData['gateway']) || in_array('eupaymentz', $gateWayData['gateway']))){ 
        ?>
    <nav>        
        <ul class="payment-ul">   
            <?php
            if(in_array('paynet', $gateWayData['gateway']) && ((isset($_SESSION['payneteasy_payment']) && $_SESSION['payneteasy_payment'] == 'enabled') 
                    || (isset($_SESSION['leads_managerid']) && $_SESSION['leads_managerid'] > 0))){
                $isAnyPaymentMethod = true;
            ?>
            <li class="svg-icon-deposit-balance kr-balance-widthdraw-selected" rel="paynet-section">
              <label><?php echo $Lang->tr('Paynet'); ?></label>
            </li>     
            <?php } ?>
            
            <?php
            if(in_array('gumballpay', $gateWayData['gateway']) && ((isset($_SESSION['gumballpay_payment']) && $_SESSION['gumballpay_payment'] == 'enabled') 
                    || (isset($_SESSION['leads_managerid']) && $_SESSION['leads_managerid'] > 0))){
                $isAnyPaymentMethod = true;
            ?>
            <li class="svg-icon-deposit-balance" rel="gumballpay-section">
              <label><?php echo $Lang->tr('Gumball Pay'); ?></label>
            </li>     
            <?php } ?>
            <?php
            if(in_array('ipasspay', $gateWayData['gateway'])){
                $isAnyPaymentMethod = true;
            ?>
            <li class="svg-icon-deposit-balance" rel="ipasspay-section">
              <label><?php echo $Lang->tr('iPassPay'); ?></label>
            </li>     
            <?php } ?>
            <?php
            if(in_array('neobank', $gateWayData['gateway'])){
                $isAnyPaymentMethod = true;
            ?>
            <li class="svg-icon-deposit-balance" rel="neobank-section">
              <label><?php echo $Lang->tr('Neo Bank'); ?></label>
            </li>     
            <?php } ?>
            <?php
            if(in_array('internet_cashbank', $gateWayData['gateway'])){
                $isAnyPaymentMethod = true;
            ?>
            <li class="svg-icon-deposit-balance" rel="internet_cashbank-section">
              <label><?php echo $Lang->tr('Internet Cashbank'); ?></label>
            </li>     
            <?php } ?>
            <?php
            if(in_array('pound_pay', $gateWayData['gateway'])){
                $isAnyPaymentMethod = true;
            ?>
            <li class="svg-icon-deposit-balance" rel="pound_pay-section">
              <label><?php echo $Lang->tr('Pound Pay'); ?></label>
            </li>     
            <?php } ?>
            <?php
            if(in_array('syspg', $gateWayData['gateway'])){
                $isAnyPaymentMethod = true;
            ?>
            <li class="svg-icon-deposit-balance" rel="syspg-section">
              <label><?php echo $Lang->tr('SysPay'); ?></label>
            </li>     
            <?php } ?>
            <?php
            if(in_array('octovio', $gateWayData['gateway'])){
                $isAnyPaymentMethod = true;
            ?>
            <li class="svg-icon-deposit-balance" rel="octovio-section">
              <label><?php echo $Lang->tr('Octovio'); ?></label>
            </li>     
            <?php } ?>

            <?php
            if(in_array('amlnnode', $gateWayData['gateway'])){
                $isAnyPaymentMethod = true;
            ?>
            <li class="svg-icon-deposit-balance" rel="amlnnode-section">
              <label><?php echo $Lang->tr('Amlnnode'); ?></label>
            </li>     
            <?php } ?>

            <?php
            if(in_array('paystudio', $gateWayData['gateway'])){
                $isAnyPaymentMethod = true;
            ?>
            <li class="svg-icon-deposit-balance" rel="paystudio-section">
              <label><?php echo $Lang->tr('Paystudio'); ?></label>
            </li>     
            <?php } ?>

            <?php
            if(in_array('chargemoney', $gateWayData['gateway'])){
                $isAnyPaymentMethod = true;
            ?>
            <li class="svg-icon-deposit-balance" rel="chargemoney-section">
              <label><?php echo $Lang->tr('Charge Money'); ?></label>
            </li>     
            <?php } ?>

            <?php
            if(in_array('kryptova', $gateWayData['gateway'])){
                $isAnyPaymentMethod = true;
            ?>
            <li class="svg-icon-deposit-balance" rel="kryptova-section">
              <label><?php echo $Lang->tr('Kryptova'); ?></label>
            </li>     
            <?php } ?>

            <?php
            if(in_array('eupaymentz', $gateWayData['gateway'])){
                $isAnyPaymentMethod = true;
            ?>
            <li class="svg-icon-deposit-balance" rel="eupaymentz-section">
              <label><?php echo $Lang->tr('EUPaymentz'); ?></label>
            </li>     
            <?php } ?>
        </ul>
    </nav>
    <?php
        if(in_array('paynet', $gateWayData['gateway']) && ((isset($_SESSION['payneteasy_payment']) && $_SESSION['payneteasy_payment'] == 'enabled') 
                || (isset($_SESSION['leads_managerid']) && $_SESSION['leads_managerid'] > 0))){
        ?>
    <section class="payment-section" id="paynet-section" style="<?= $sectionDisplay ?>">
        <h3><?php echo $Lang->tr('Deposit amount via Paynet'); ?></h3>
        <form role="form" action="<?= APP_URL ?>/app/modules/kr-trade/src/actions/payneteasypayment.php" method="post">
            <?php
            if(isset($userData['address']) && $userData['address'] == ''){
                ?>
            <div class="form-group" id="">
                <label for="address">Address</label>
                <div class="input-icon">
                    <i class="fa fa-user"></i>
                    <input type="text" class="credit-input" id="address" name="address" placeholder="Enter address" required />
                </div> 
            </div>
            <?php
            }
            ?>
            <?php
            if(isset($userData['city']) && $userData['city'] == ''){
                ?>
            <div class="form-group" id="">
                <label for="city">City</label>
                <div class="input-icon">
                    <i class="fa fa-user"></i>
                    <input type="text" class="credit-input" id="city" name="city" placeholder="Enter city" required />
                </div> 
            </div>
            <?php
            }
            ?>
            <?php
            if(isset($userData['state']) && $userData['state'] == ''){
                ?>
            <div class="form-group" id="">
                <label for="state">State</label>
                <div class="input-icon">
                    <i class="fa fa-user"></i>
                    <?php
                    if(isset($userData['country_code']) && $userData['country_code'] == 'US'){
                        ?>
                    <select class="credit-input" name="state" required>
                        <option value="">Select State</option>
                        <?php
                            foreach ($usStates as $key => $value){
                                echo "<option value='".$key."'>".$value."</option>";
                            }
                        ?>
                    </select>
                    <?php
                    } elseif (isset($userData['country_code']) && $userData['country_code'] == 'CA'){
                        ?>
                    <select class="credit-input" name="state" required>
                        <option value="">Select State</option>
                        <?php
                            foreach ($canadaStates as $key => $value){
                                echo "<option value='".$key."'>".$value."</option>";
                            }
                        ?>
                    </select>
                    <?php
                    } elseif (isset($userData['country_code']) && $userData['country_code'] == 'AU') {
                        ?>
                    <select class="credit-input" name="state" required>
                        <option value="">Select State</option>
                        <?php
                            foreach ($australianStates as $key => $value){
                                echo "<option value='".$key."'>".$value."</option>";
                            }
                        ?>
                    </select>
                    <?php
                    } else {
                        ?>
                    
                    <?php
                    }
                    ?>                    
                </div> 
            </div>
            <?php
            }
            ?>
            <?php
            if(isset($userData['zipcode']) && $userData['zipcode'] == ''){
                ?>
            <div class="form-group" id="">
                <label for="zipcode">Zipcode</label>
                <div class="input-icon">
                    <i class="fa fa-user"></i>
                    <input type="text" class="credit-input" id="zipcode" name="zipcode" placeholder="Enter zipcode" required />
                </div> 
            </div>
            <?php
            }
            ?>
            <div class="form-group" id="amountDiv">
                <label for="amount">Amount</label>
                <div class="input-icon">
                    <i class="fa fa-user"></i>
                    <input type="number" min="250" class="credit-input" id="amount" name="amount" placeholder="Enter amount" required>
                </div> 
            </div>

            <div class="text-center mrg-top-10">
                <button class=" btn btn-success" id="" type="submit"> <b> <i class="fa fa-money"></i> PAY NOW </b></button>
            </div>
        </form>
    </section> 
    <?php 
        $sectionDisplay = 'display:none;';   
            } ?>
    
    
    <?php
        if(in_array('gumballpay', $gateWayData['gateway']) && ((isset($_SESSION['gumballpay_payment']) && $_SESSION['gumballpay_payment'] == 'enabled') 
                || (isset($_SESSION['leads_managerid']) && $_SESSION['leads_managerid'] > 0))){
        ?>
        <section class="payment-section" id="gumballpay-section" style="<?= $sectionDisplay ?>">
        <h3><?php echo $Lang->tr('Deposit amount via Gumball pay'); ?></h3>
        <form role="form" action="<?= APP_URL ?>/app/modules/kr-trade/src/actions/gumballpay.php" method="post">
            <?php
            if(isset($userData['address']) && $userData['address'] == ''){
                ?>
            <div class="form-group" id="">
                <label for="address">Address</label>
                <div class="input-icon">
                    <i class="fa fa-user"></i>
                    <input type="text" class="credit-input" id="address" name="address" placeholder="Enter address" required />
                </div> 
            </div>
            <?php
            }
            ?>
            <?php
            if(isset($userData['city']) && $userData['city'] == ''){
                ?>
            <div class="form-group" id="">
                <label for="city">City</label>
                <div class="input-icon">
                    <i class="fa fa-user"></i>
                    <input type="text" class="credit-input" id="city" name="city" placeholder="Enter city" required />
                </div> 
            </div>
            <?php
            }
            ?>
            <?php
            if(isset($userData['state']) && $userData['state'] == ''){
                ?>
            <div class="form-group" id="">
                <label for="state">State</label>
                <div class="input-icon">
                    <i class="fa fa-user"></i>
                    <?php
                    if(isset($userData['country_code']) && $userData['country_code'] == 'US'){
                        ?>
                    <select class="credit-input" name="state" required>
                        <option value="">Select State</option>
                        <?php
                            foreach ($usStates as $key => $value){
                                echo "<option value='".$key."'>".$value."</option>";
                            }
                        ?>
                    </select>
                    <?php
                    } elseif (isset($userData['country_code']) && $userData['country_code'] == 'CA'){
                        ?>
                    <select class="credit-input" name="state" required>
                        <option value="">Select State</option>
                        <?php
                            foreach ($canadaStates as $key => $value){
                                echo "<option value='".$key."'>".$value."</option>";
                            }
                        ?>
                    </select>
                    <?php
                    } elseif (isset($userData['country_code']) && $userData['country_code'] == 'AU') {
                        ?>
                    <select class="credit-input" name="state" required>
                        <option value="">Select State</option>
                        <?php
                            foreach ($australianStates as $key => $value){
                                echo "<option value='".$key."'>".$value."</option>";
                            }
                        ?>
                    </select>
                    <?php
                    } else {
                        ?>
                    
                    <?php
                    }
                    ?>                    
                </div> 
            </div>
            <?php
            }
            ?>
            <?php
            if(isset($userData['zipcode']) && $userData['zipcode'] == ''){
                ?>
            <div class="form-group" id="">
                <label for="zipcode">Zipcode</label>
                <div class="input-icon">
                    <i class="fa fa-user"></i>
                    <input type="text" class="credit-input" id="zipcode" name="zipcode" placeholder="Enter zipcode" required />
                </div> 
            </div>
            <?php
            }
            ?>
            <div class="form-group" id="amountDiv">
                <label for="amount">Amount</label>
                <div class="input-icon">
                    <i class="fa fa-user"></i>
                    <input type="number" min="250" class="credit-input" id="amount" name="amount" placeholder="Enter amount" required>
                </div> 
            </div>

            <div class="text-center mrg-top-10">
                <button class=" btn btn-success" id="" type="submit"> <b> <i class="fa fa-money"></i> PAY NOW </b></button>
            </div>
        </form>
    </section> 
    <?php 
        $sectionDisplay = 'display:none;';  
            } ?>

    <?php
        if(in_array('ipasspay', $gateWayData['gateway'])){
    ?>
        <section class="payment-section" id="ipasspay-section" style="<?= $sectionDisplay ?>">
            <h3><?php echo $Lang->tr('Deposit amount via iPassPay'); ?></h3>
            <form role="form" action="<?= APP_URL ?>/app/modules/kr-trade/src/actions/ipasspay.php" method="post">
                <?php
                if(isset($userData['address']) && $userData['address'] == ''){
                    ?>
                <div class="form-group" id="">
                    <label for="address">Address</label>
                    <div class="input-icon">
                        <i class="fa fa-user"></i>
                        <input type="text" class="credit-input" id="address" name="address" placeholder="Enter address" required />
                    </div> 
                </div>
                <?php
                }
                ?>
                <?php
                if(isset($userData['city']) && $userData['city'] == ''){
                    ?>
                <div class="form-group" id="">
                    <label for="city">City</label>
                    <div class="input-icon">
                        <i class="fa fa-user"></i>
                        <input type="text" class="credit-input" id="city" name="city" placeholder="Enter city" required />
                    </div> 
                </div>
                <?php
                }
                ?>
                <?php
                if(isset($userData['state']) && $userData['state'] == ''){
                    ?>
                <div class="form-group" id="">
                    <label for="state">State</label>
                    <div class="input-icon">
                        <i class="fa fa-user"></i>
                        <?php
                        if(isset($userData['country_code']) && $userData['country_code'] == 'US'){
                            ?>
                        <select class="credit-input" name="state" required>
                            <option value="">Select State</option>
                            <?php
                                foreach ($usStates as $key => $value){
                                    echo "<option value='".$key."'>".$value."</option>";
                                }
                            ?>
                        </select>
                        <?php
                        } elseif (isset($userData['country_code']) && $userData['country_code'] == 'CA'){
                            ?>
                        <select class="credit-input" name="state" required>
                            <option value="">Select State</option>
                            <?php
                                foreach ($canadaStates as $key => $value){
                                    echo "<option value='".$key."'>".$value."</option>";
                                }
                            ?>
                        </select>
                        <?php
                        } elseif (isset($userData['country_code']) && $userData['country_code'] == 'AU') {
                            ?>
                        <select class="credit-input" name="state" required>
                            <option value="">Select State</option>
                            <?php
                                foreach ($australianStates as $key => $value){
                                    echo "<option value='".$key."'>".$value."</option>";
                                }
                            ?>
                        </select>
                        <?php
                        } elseif (isset($userData['country_code']) && $userData['country_code'] == 'JP') {
                            ?>
                        <select class="credit-input" name="state" required>
                            <option value="">Select State</option>
                            <?php
                                foreach ($japanStates as $key => $value){
                                    echo "<option value='".$key."'>".$value."</option>";
                                }
                            ?>
                        </select>
                        <?php
                        } else {
                            ?>
                        <input type="text" style="width: 80%;" class="credit-input" id="state" name="state" placeholder="Enter state" required />
                        <?php
                        }
                        ?>                    
                    </div> 
                </div>
                <?php
                }
                ?>
                <?php
                if(isset($userData['zipcode']) && $userData['zipcode'] == ''){
                    ?>
                <div class="form-group" id="">
                    <label for="zipcode">Zipcode</label>
                    <div class="input-icon">
                        <i class="fa fa-user"></i>
                        <input type="text" class="credit-input" id="zipcode" name="zipcode" placeholder="Enter zipcode" required />
                    </div> 
                </div>
                <?php
                }
                ?>
                <div class="form-group" id="amountDiv">
                    <label for="amount">Amount(Between $250 To $1000)</label>
                    <div class="input-icon">
                        <i class="fa fa-user"></i>
                        <input type="number" class="credit-input" id="amount" name="amount" placeholder="Enter amount" min="250" max="1000" value="250" required>
                    </div> 
                </div>

                <div class="text-center mrg-top-10">
                    <button class=" btn btn-success" id="" type="submit"> <b> <i class="fa fa-money"></i> PAY NOW </b></button>
                </div>
            </form>
        </section> 
    <?php 
        $sectionDisplay = 'display:none;';  
        }
    ?>        
    
    <?php
        if(in_array('neobank', $gateWayData['gateway'])){
        ?>
    <section class="payment-section" id="neobank-section" style="<?= $sectionDisplay ?> overflow-y: scroll; height: 72%;">
        <h3><?php echo $Lang->tr('Deposit amount via Neobank'); ?></h3>
        
        <form role="form" class="neobank-form">
            <div class="form-group" id="amountDiv">
                <label for="amount">Amount</label>
                <div class="input-icon">
                    <i class="fa fa-user"></i>
                    <input type="text" class="credit-input" id="amount" name="amount" placeholder="Enter amount" required>
                </div> 
            </div>
            <div class="form-group" id="usernameDiv">
                <label for="username">Full Name (on the card)</label>
                <div class="input-icon">
                    <i class="fa fa-user"></i>
                    <input type="text" class="credit-input" id="username" name="username" placeholder="Enter Name" required="">
                </div> 
            </div>
            
            <?php
            if(isset($userData['address']) && $userData['address'] == ''){
                ?>
            <div class="form-group" id="addressDiv">
                <label for="address">Address</label>
                <div class="input-icon">
                    <i class="fa fa-user"></i>
                    <input type="text" class="credit-input" id="address" name="address" placeholder="Enter address" required />
                </div> 
            </div>
            <?php
            }
            ?>
            <?php
            if((isset($userData['city']) && $userData['city'] == '') || (isset($userData['state']) && $userData['state'] == '') || (isset($userData['zipcode']) && $userData['zipcode'] == '')){
                ?>
            <div class="form-group" id="" style="display: inline-block;">
                <?php
                if(isset($userData['city']) && $userData['city'] == ''){
                    ?>
                    <div id="cityDiv" style="width: 33.33%; float: left;">
                        <label for="city" style="color: #fff; font-size: 14px;">City</label>
                        <div class="input-icon">
                            <i class="fa fa-user"></i>
                            <input type="text" style="width: 80%;" class="credit-input" id="city" name="city" placeholder="Enter city" required />
                        </div>
                    </div>
                <?php } ?>
                <?php
                if(isset($userData['state']) && $userData['state'] == ''){
                    ?>
                    <div id="stateDiv" style="width: 33.33%; float: left;">
                        <label for="state" style="color: #fff; font-size: 14px;">State</label>
                        <div class="input-icon">
                            <i class="fa fa-user"></i>
                            <?php
                            if(isset($userData['country_code']) && $userData['country_code'] == 'US'){
                                ?>
                            <select class="credit-input" id="state" name="state" required style="width: 80%;">
                                <option value="">Select State</option>
                                <?php
                                    foreach ($usStates as $key => $value){
                                        echo "<option value='".$key."'>".$value."</option>";
                                    }
                                ?>
                            </select>
                            <?php
                            } elseif (isset($userData['country_code']) && $userData['country_code'] == 'CA'){
                                ?>
                            <select class="credit-input" id="state" name="state" required style="width: 80%;">
                                <option value="">Select State</option>
                                <?php
                                    foreach ($canadaStates as $key => $value){
                                        echo "<option value='".$key."'>".$value."</option>";
                                    }
                                ?>
                            </select>
                            <?php
                            } elseif (isset($userData['country_code']) && $userData['country_code'] == 'AU') {
                                ?>
                            <select class="credit-input" id="state" name="state" required style="width: 80%;">
                                <option value="">Select State</option>
                                <?php
                                    foreach ($australianStates as $key => $value){
                                        echo "<option value='".$key."'>".$value."</option>";
                                    }
                                ?>
                            </select>
                            <?php
                            } else {
                                ?>
                            <input type="text" style="width: 80%;" class="credit-input" id="state" name="state" placeholder="Enter state" required />
                            <?php
                            }
                            ?>                    
                        </div>
                    </div>
                <?php } ?>
                <?php
                if(isset($userData['zipcode']) && $userData['zipcode'] == ''){
                    ?>
                    <div id="zipcodeDiv" style="width: 33.33%; float: left;">
                        <label for="zipcode" style="color: #fff; font-size: 14px;">Zipcode</label>
                        <div class="input-icon">
                            <i class="fa fa-user"></i>
                            <input type="text" style="width: 80%;" class="credit-input" id="zipcode" name="zipcode" placeholder="Enter zipcode" required />
                        </div> 
                    </div>
                <?php } ?>
            </div>
            <?php
            }
            ?>
            
            <div class="credit-f-l form-group">                
                <div class="" id="card-number-field">
                    <label for="cardNumber" style="color: #fff; font-size: 14px;">Card Number</label>
                    <input data-inputmask="'mask': '9999 9999 9999 9999'"  type="text" class="credit-input" id="cardNumber" name="cardNumber" placeholder="Enter Credit Card Number">
                </div> 
                
                <div class="">
                    <div class="" id="credit_cards">
                        <img src="<?= APP_URL ?>/images/visa.png" id="visa">
                        <img src="<?= APP_URL ?>/images/mastercard.png" id="mastercard">
                        <img src="<?= APP_URL ?>/images/amex.png" id="amex">
                        <img src="<?= APP_URL ?>/images/discover.png" id="discover">
                    </div>
                    <input type="hidden" name="cardtype" value="0" id="cardtype"/>
                </div>
            </div>    

            <div class="credit-f-l">
                <div class="mrg-left-0 credit-exp form-group">
                    <label><span class="hidden-xs">Expiration</span> </label>
                    <div class="" id="expiration-date">    
                            <select class="credit-input credit-select" id="exp_month" name="exp_month" style="width:45%">
                                <option value="">MM</option>
                                <option value="01">January</option>
                                <option value="02">February </option>
                                <option value="03">March</option>
                                <option value="04">April</option>
                                <option value="05">May</option>
                                <option value="06">June</option>
                                <option value="07">July</option>
                                <option value="08">August</option>
                                <option value="09">September</option>
                                <option value="10">October</option>
                                <option value="11">November</option>
                                <option value="12">December</option>
                            </select>
                            
                            <select class="credit-input credit-select" id="exp_year" name="exp_year" style="width:45%">
                                <option value="">YY</option>
                                <?php
                                $year = date("Y");
                                echo "<option value='$year'>$year</option>";
                                for($i = 1; $i <= 25; $i++){
                                    $year = date("Y") + $i;
                                    echo "<option value='$year'>$year</option>";
                                }
                                ?>                                                                          
                            </select>
                    </div>
                </div>
                <div class="form-group" id="cvvDiv">                    
                        <label data-toggle="tooltip" title="" data-original-title="3 digits code on back side of the card">CVV <i class="fa fa-question-circle"></i></label>
                        <input data-inputmask="'mask': '999'" class="credit-input" placeholder="CVV" name="cvv" id="cvv" required="" type="text">                    
                </div>
            </div> <!-- row.// -->
            
            
            
            <div class="text-center mrg-top-10">
                <button class="subscribe btn btn-success" id="confirm-purchase" type="button"> <b> <i class="fa fa-money"></i> PAY NOW </b></button>
            </div>
        </form>
        
    </section> 
    <?php 
        $sectionDisplay = 'display:none;';  
            } ?>  

     <?php
        if(in_array('internet_cashbank', $gateWayData['gateway'])){
        ?>
    <section class="payment-section" id="internet_cashbank-section" style="<?= $sectionDisplay ?>">
        <h3><?php echo $Lang->tr('Deposit amount via Internet Cashbank'); ?></h3>
        <form role="form" action="<?= APP_URL ?>/app/modules/kr-trade/src/actions/internetCashbank.php" method="post">
            <div class="form-group" id="amountDiv">
                <label for="amount">Amount</label>
                <div class="input-icon">
                    <i class="fa fa-user"></i>
                    <input type="number" min="0" class="credit-input" id="amount" name="amount" placeholder="Enter amount" required>
                </div> 
            </div>

            <div class="text-center mrg-top-10">
                <button class=" btn btn-success" id="" type="submit"> <b> <i class="fa fa-money"></i> PAY NOW </b></button>
            </div>
        </form>
    </section> 
    <?php 
        $sectionDisplay = 'display:none;';   
            } ?>   

    <?php
        if(in_array('pound_pay', $gateWayData['gateway'])){
    ?>
        <section class="payment-section" id="pound_pay-section" style="<?= $sectionDisplay ?>">
            <h3><?php echo $Lang->tr('Deposit amount via Pound Pay'); ?></h3>
            <form role="form" action="<?= APP_URL ?>/app/modules/kr-trade/src/actions/poundpay.php" method="post">
                <?php
                if(isset($userData['address']) && $userData['address'] == ''){
                    ?>
                <div class="form-group" id="">
                    <label for="address">Address</label>
                    <div class="input-icon">
                        <i class="fa fa-user"></i>
                        <input type="text" class="credit-input" id="address" name="address" placeholder="Enter address" required />
                    </div> 
                </div>
                <?php
                }
                ?>
                <?php
                if(isset($userData['city']) && $userData['city'] == ''){
                    ?>
                <div class="form-group" id="">
                    <label for="city">City</label>
                    <div class="input-icon">
                        <i class="fa fa-user"></i>
                        <input type="text" class="credit-input" id="city" name="city" placeholder="Enter city" required />
                    </div> 
                </div>
                <?php
                }
                ?>
                <?php
                if(isset($userData['state']) && $userData['state'] == ''){
                    ?>
                <div class="form-group" id="">
                    <label for="state">State</label>
                    <div class="input-icon">
                        <i class="fa fa-user"></i>
                        <?php
                        if(isset($userData['country_code']) && $userData['country_code'] == 'US'){
                            ?>
                        <select class="credit-input" name="state" required>
                            <option value="">Select State</option>
                            <?php
                                foreach ($usStates as $key => $value){
                                    echo "<option value='".$key."'>".$value."</option>";
                                }
                            ?>
                        </select>
                        <?php
                        } elseif (isset($userData['country_code']) && $userData['country_code'] == 'CA'){
                            ?>
                        <select class="credit-input" name="state" required>
                            <option value="">Select State</option>
                            <?php
                                foreach ($canadaStates as $key => $value){
                                    echo "<option value='".$key."'>".$value."</option>";
                                }
                            ?>
                        </select>
                        <?php
                        } elseif (isset($userData['country_code']) && $userData['country_code'] == 'AU') {
                            ?>
                        <select class="credit-input" name="state" required>
                            <option value="">Select State</option>
                            <?php
                                foreach ($australianStates as $key => $value){
                                    echo "<option value='".$key."'>".$value."</option>";
                                }
                            ?>
                        </select>
                        <?php
                        } elseif (isset($userData['country_code']) && $userData['country_code'] == 'JP') {
                            ?>
                        <select class="credit-input" name="state" required>
                            <option value="">Select State</option>
                            <?php
                                foreach ($japanStates as $key => $value){
                                    echo "<option value='".$key."'>".$value."</option>";
                                }
                            ?>
                        </select>
                        <?php
                        } else {
                            ?>
                        <input type="text" style="width: 80%;" class="credit-input" id="state" name="state" placeholder="Enter state" required />
                        <?php
                        }
                        ?>                    
                    </div> 
                </div>
                <?php
                }
                ?>
                <?php
                if(isset($userData['zipcode']) && $userData['zipcode'] == ''){
                    ?>
                <div class="form-group" id="">
                    <label for="zipcode">Zipcode</label>
                    <div class="input-icon">
                        <i class="fa fa-user"></i>
                        <input type="text" class="credit-input" id="zipcode" name="zipcode" placeholder="Enter zipcode" required />
                    </div> 
                </div>
                <?php
                }
                ?>
                <div class="form-group" id="amountDiv">
                    <label for="amount">Amount</label>
                    <div class="input-icon">
                        <i class="fa fa-user"></i>
                        <input type="number" class="credit-input" id="amount" name="amount" placeholder="Enter amount" value="250" required>
                    </div> 
                </div>

                <div class="text-center mrg-top-10">
                    <button class=" btn btn-success" id="" type="submit"> <b> <i class="fa fa-money"></i> PAY NOW </b></button>
                </div>
            </form>
        </section> 
    <?php 
        $sectionDisplay = 'display:none;';  
        }
    ?>

    <?php
        if(in_array('syspg', $gateWayData['gateway'])){
    ?>
        <section class="payment-section" id="syspg-section" style="<?= $sectionDisplay ?>">
            <h3><?php echo $Lang->tr('Deposit amount via Sys Pay'); ?></h3>
            <form role="form" action="<?= APP_URL ?>/app/modules/kr-trade/src/actions/syspg.php" method="post">
                <?php
                if(isset($userData['address']) && $userData['address'] == ''){
                    ?>
                <div class="form-group" id="">
                    <label for="address">Address</label>
                    <div class="input-icon">
                        <i class="fa fa-user"></i>
                        <input type="text" class="credit-input" id="address" name="address" placeholder="Enter address" required />
                    </div> 
                </div>
                <?php
                }
                ?>
                <?php
                if(isset($userData['city']) && $userData['city'] == ''){
                    ?>
                <div class="form-group" id="">
                    <label for="city">City</label>
                    <div class="input-icon">
                        <i class="fa fa-user"></i>
                        <input type="text" class="credit-input" id="city" name="city" placeholder="Enter city" required />
                    </div> 
                </div>
                <?php
                }
                ?>
                <?php
                if(isset($userData['state']) && $userData['state'] == ''){
                    ?>
                <div class="form-group" id="">
                    <label for="state">State</label>
                    <div class="input-icon">
                        <i class="fa fa-user"></i>
                        <?php
                        if(isset($userData['country_code']) && $userData['country_code'] == 'US'){
                            ?>
                        <select class="credit-input" name="state" required>
                            <option value="">Select State</option>
                            <?php
                                foreach ($usStates as $key => $value){
                                    echo "<option value='".$key."'>".$value."</option>";
                                }
                            ?>
                        </select>
                        <?php
                        } elseif (isset($userData['country_code']) && $userData['country_code'] == 'CA'){
                            ?>
                        <select class="credit-input" name="state" required>
                            <option value="">Select State</option>
                            <?php
                                foreach ($canadaStates as $key => $value){
                                    echo "<option value='".$key."'>".$value."</option>";
                                }
                            ?>
                        </select>
                        <?php
                        } elseif (isset($userData['country_code']) && $userData['country_code'] == 'AU') {
                            ?>
                        <select class="credit-input" name="state" required>
                            <option value="">Select State</option>
                            <?php
                                foreach ($australianStates as $key => $value){
                                    echo "<option value='".$key."'>".$value."</option>";
                                }
                            ?>
                        </select>
                        <?php
                        } elseif (isset($userData['country_code']) && $userData['country_code'] == 'JP') {
                            ?>
                        <select class="credit-input" name="state" required>
                            <option value="">Select State</option>
                            <?php
                                foreach ($japanStates as $key => $value){
                                    echo "<option value='".$key."'>".$value."</option>";
                                }
                            ?>
                        </select>
                        <?php
                        } else {
                            ?>
                        <input type="text" style="width: 80%;" class="credit-input" id="state" name="state" placeholder="Enter state" required />
                        <?php
                        }
                        ?>                    
                    </div> 
                </div>
                <?php
                }
                ?>
                <?php
                if(isset($userData['zipcode']) && $userData['zipcode'] == ''){
                    ?>
                <div class="form-group" id="">
                    <label for="zipcode">Zipcode</label>
                    <div class="input-icon">
                        <i class="fa fa-user"></i>
                        <input type="text" class="credit-input" id="zipcode" name="zipcode" placeholder="Enter zipcode" required />
                    </div> 
                </div>
                <?php
                }
                ?>
                <div class="form-group" id="amountDiv">
                    <label for="amount">Amount</label>
                    <div class="input-icon">
                        <i class="fa fa-user"></i>
                        <input type="number" class="credit-input" id="amount" name="amount" placeholder="Enter amount" value="250" required>
                    </div> 
                </div>

                <div class="text-center mrg-top-10">
                    <button class=" btn btn-success" id="" type="submit"> <b> <i class="fa fa-money"></i> PAY NOW </b></button>
                </div>
            </form>
        </section> 
    <?php 
        $sectionDisplay = 'display:none;';  
        }
    ?>  

    <?php
        if(in_array('octovio', $gateWayData['gateway'])){
    ?>
        <section class="payment-section" id="octovio-section" style="<?= $sectionDisplay ?>">
            <h3><?php echo $Lang->tr('Deposit amount via Octovio Pay'); ?></h3>
            <form role="form" action="<?= APP_URL ?>/app/modules/kr-trade/src/actions/octovio.php" method="post">
                <div class="form-group" id="amountDiv">
                    <label for="amount">Amount</label>
                    <div class="input-icon">
                        <i class="fa fa-user"></i>
                        <input type="number" class="credit-input" id="amount" min="1" name="amount" placeholder="Enter amount" value="250" required>
                    </div> 
                </div>

                <div class="text-center mrg-top-10">
                    <button class=" btn btn-success" id="" type="submit"> <b> <i class="fa fa-money"></i> PAY NOW </b></button>
                </div>
            </form>
        </section> 
    <?php 
        $sectionDisplay = 'display:none;';  
        }
    ?>

    <?php
        if(in_array('amlnnode', $gateWayData['gateway'])){
        ?>
    <section class="payment-section" id="amlnnode-section" style="<?= $sectionDisplay ?>">
        <h3><?php echo $Lang->tr('Deposit amount via Amlnnode'); ?></h3>
        <form role="form" action="<?= APP_URL ?>/app/modules/kr-trade/src/actions/amlnnode.php" method="post">
            <div class="text-center mrg-top-10">
                <button class=" btn btn-success" id="" type="submit"> <b> <i class="fa fa-money"></i> PAY NOW </b></button>
            </div>
        </form>
    </section> 
    <?php 
        $sectionDisplay = 'display:none;';   
            } ?> 

            
    <?php
        if(in_array('paystudio', $gateWayData['gateway'])){
    ?>
        <section class="payment-section" id="paystudio-section" style="<?= $sectionDisplay ?>">
            <h3><?php echo $Lang->tr('Deposit amount via Paystudio'); ?></h3>
            <form target="_blank" role="form" action="<?= APP_URL ?>/app/modules/kr-trade/src/actions/paystudio.php" method="post">
                <?php
                if(isset($userData['address']) && $userData['address'] == ''){
                    ?>
                <div class="form-group" id="">
                    <label for="address">Address</label>
                    <div class="input-icon">
                        <i class="fa fa-user"></i>
                        <input type="text" class="credit-input" id="address" name="address" placeholder="Enter address" required />
                    </div> 
                </div>
                <?php
                }
                ?>
                <?php
                if(isset($userData['city']) && $userData['city'] == ''){
                    ?>
                <div class="form-group" id="">
                    <label for="city">City</label>
                    <div class="input-icon">
                        <i class="fa fa-user"></i>
                        <input type="text" class="credit-input" id="city" name="city" placeholder="Enter city" required />
                    </div> 
                </div>
                <?php
                }
                ?>
                <?php
                if(isset($userData['state']) && $userData['state'] == ''){
                    ?>
                <div class="form-group" id="">
                    <label for="state">State</label>
                    <div class="input-icon">
                        <i class="fa fa-user"></i>
                        <?php
                        if(isset($userData['country_code']) && $userData['country_code'] == 'US'){
                            ?>
                        <select class="credit-input" name="state" required>
                            <option value="">Select State</option>
                            <?php
                                foreach ($usStates as $key => $value){
                                    echo "<option value='".$key."'>".$value."</option>";
                                }
                            ?>
                        </select>
                        <?php
                        } elseif (isset($userData['country_code']) && $userData['country_code'] == 'CA'){
                            ?>
                        <select class="credit-input" name="state" required>
                            <option value="">Select State</option>
                            <?php
                                foreach ($canadaStates as $key => $value){
                                    echo "<option value='".$key."'>".$value."</option>";
                                }
                            ?>
                        </select>
                        <?php
                        } elseif (isset($userData['country_code']) && $userData['country_code'] == 'AU') {
                            ?>
                        <select class="credit-input" name="state" required>
                            <option value="">Select State</option>
                            <?php
                                foreach ($australianStates as $key => $value){
                                    echo "<option value='".$key."'>".$value."</option>";
                                }
                            ?>
                        </select>
                        <?php
                        } elseif (isset($userData['country_code']) && $userData['country_code'] == 'JP') {
                            ?>
                        <select class="credit-input" name="state" required>
                            <option value="">Select State</option>
                            <?php
                                foreach ($japanStates as $key => $value){
                                    echo "<option value='".$key."'>".$value."</option>";
                                }
                            ?>
                        </select>
                        <?php
                        } else {
                            ?>
                        <input type="text" style="width: 80%;" class="credit-input" id="state" name="state" placeholder="Enter state" required />
                        <?php
                        }
                        ?>                    
                    </div> 
                </div>
                <?php
                }
                ?>
                <?php
                if(isset($userData['zipcode']) && $userData['zipcode'] == ''){
                    ?>
                <div class="form-group" id="">
                    <label for="zipcode">Zipcode</label>
                    <div class="input-icon">
                        <i class="fa fa-user"></i>
                        <input type="text" class="credit-input" id="zipcode" name="zipcode" placeholder="Enter zipcode" required />
                    </div> 
                </div>
                <?php
                }
                ?>
                <div class="form-group" id="amountDiv">
                    <label for="amount">Amount</label>
                    <div class="input-icon">
                        <i class="fa fa-user"></i>
                        <input type="number" class="credit-input" id="amount" name="amount" placeholder="Enter amount" value="250" required>
                    </div> 
                </div>

                <div class="text-center mrg-top-10">
                    <button class=" btn btn-success" id="" type="submit"> <b> <i class="fa fa-money"></i> PAY NOW </b></button>
                </div>
            </form>
        </section> 
    <?php 
        $sectionDisplay = 'display:none;';  
        }
    ?>

    <?php
        if(in_array('chargemoney', $gateWayData['gateway'])){
    ?>
        <section class="payment-section" id="chargemoney-section" style="<?= $sectionDisplay ?>">
            <h3><?php echo $Lang->tr('Deposit amount via Paystudio'); ?></h3>
            <form target="_blank" role="form" action="<?= APP_URL ?>/app/modules/kr-trade/src/actions/chargemoney.php" method="post">
                <?php
                if(isset($userData['address']) && $userData['address'] == ''){
                    ?>
                <div class="form-group" id="">
                    <label for="address">Address</label>
                    <div class="input-icon">
                        <i class="fa fa-user"></i>
                        <input type="text" class="credit-input" id="address" name="address" placeholder="Enter address" required />
                    </div> 
                </div>
                <?php
                }
                ?>
                <?php
                if(isset($userData['city']) && $userData['city'] == ''){
                    ?>
                <div class="form-group" id="">
                    <label for="city">City</label>
                    <div class="input-icon">
                        <i class="fa fa-user"></i>
                        <input type="text" class="credit-input" id="city" name="city" placeholder="Enter city" required />
                    </div> 
                </div>
                <?php
                }
                ?>
                <?php
                if(isset($userData['state']) && $userData['state'] == ''){
                    ?>
                <div class="form-group" id="">
                    <label for="state">State</label>
                    <div class="input-icon">
                        <i class="fa fa-user"></i>
                        <?php
                        if(isset($userData['country_code']) && $userData['country_code'] == 'US'){
                            ?>
                        <select class="credit-input" name="state" required>
                            <option value="">Select State</option>
                            <?php
                                foreach ($usStates as $key => $value){
                                    echo "<option value='".$key."'>".$value."</option>";
                                }
                            ?>
                        </select>
                        <?php
                        } elseif (isset($userData['country_code']) && $userData['country_code'] == 'CA'){
                            ?>
                        <select class="credit-input" name="state" required>
                            <option value="">Select State</option>
                            <?php
                                foreach ($canadaStates as $key => $value){
                                    echo "<option value='".$key."'>".$value."</option>";
                                }
                            ?>
                        </select>
                        <?php
                        } elseif (isset($userData['country_code']) && $userData['country_code'] == 'AU') {
                            ?>
                        <select class="credit-input" name="state" required>
                            <option value="">Select State</option>
                            <?php
                                foreach ($australianStates as $key => $value){
                                    echo "<option value='".$key."'>".$value."</option>";
                                }
                            ?>
                        </select>
                        <?php
                        } elseif (isset($userData['country_code']) && $userData['country_code'] == 'JP') {
                            ?>
                        <select class="credit-input" name="state" required>
                            <option value="">Select State</option>
                            <?php
                                foreach ($japanStates as $key => $value){
                                    echo "<option value='".$key."'>".$value."</option>";
                                }
                            ?>
                        </select>
                        <?php
                        } else {
                            ?>
                        <input type="text" style="width: 80%;" class="credit-input" id="state" name="state" placeholder="Enter state" required />
                        <?php
                        }
                        ?>                    
                    </div> 
                </div>
                <?php
                }
                ?>
                <?php
                if(isset($userData['zipcode']) && $userData['zipcode'] == ''){
                    ?>
                <div class="form-group" id="">
                    <label for="zipcode">Zipcode</label>
                    <div class="input-icon">
                        <i class="fa fa-user"></i>
                        <input type="text" class="credit-input" id="zipcode" name="zipcode" placeholder="Enter zipcode" required />
                    </div> 
                </div>
                <?php
                }
                ?>
                <div class="form-group" id="amountDiv">
                    <label for="amount">Amount</label>
                    <div class="input-icon">
                        <i class="fa fa-user"></i>
                        <input type="number" class="credit-input" id="amount" name="amount" placeholder="Enter amount" value="250" required>
                    </div> 
                </div>

                <div class="text-center mrg-top-10">
                    <button class=" btn btn-success" id="" type="submit"> <b> <i class="fa fa-money"></i> PAY NOW </b></button>
                </div>
            </form>
        </section> 
    <?php 
        $sectionDisplay = 'display:none;';  
        }
    ?>         

    <?php
        if(in_array('kryptova', $gateWayData['gateway'])){
    ?>
        <section class="payment-section" id="kryptova-section" style="<?= $sectionDisplay ?>">
            <h3><?php echo $Lang->tr('Deposit amount via Paystudio'); ?></h3>
            <form target="_blank" role="form" action="<?= APP_URL ?>/app/modules/kr-trade/src/actions/kryptova.php" method="post">
                <?php
                if(isset($userData['address']) && $userData['address'] == ''){
                    ?>
                <div class="form-group" id="">
                    <label for="address">Address</label>
                    <div class="input-icon">
                        <i class="fa fa-user"></i>
                        <input type="text" class="credit-input" id="address" name="address" placeholder="Enter address" required />
                    </div> 
                </div>
                <?php
                }
                ?>
                <?php
                if(isset($userData['city']) && $userData['city'] == ''){
                    ?>
                <div class="form-group" id="">
                    <label for="city">City</label>
                    <div class="input-icon">
                        <i class="fa fa-user"></i>
                        <input type="text" class="credit-input" id="city" name="city" placeholder="Enter city" required />
                    </div> 
                </div>
                <?php
                }
                ?>
                <?php
                if(isset($userData['state']) && $userData['state'] == ''){
                    ?>
                <div class="form-group" id="">
                    <label for="state">State</label>
                    <div class="input-icon">
                        <i class="fa fa-user"></i>
                        <?php
                        if(isset($userData['country_code']) && $userData['country_code'] == 'US'){
                            ?>
                        <select class="credit-input" name="state" required>
                            <option value="">Select State</option>
                            <?php
                                foreach ($usStates as $key => $value){
                                    echo "<option value='".$key."'>".$value."</option>";
                                }
                            ?>
                        </select>
                        <?php
                        } elseif (isset($userData['country_code']) && $userData['country_code'] == 'CA'){
                            ?>
                        <select class="credit-input" name="state" required>
                            <option value="">Select State</option>
                            <?php
                                foreach ($canadaStates as $key => $value){
                                    echo "<option value='".$key."'>".$value."</option>";
                                }
                            ?>
                        </select>
                        <?php
                        } elseif (isset($userData['country_code']) && $userData['country_code'] == 'AU') {
                            ?>
                        <select class="credit-input" name="state" required>
                            <option value="">Select State</option>
                            <?php
                                foreach ($australianStates as $key => $value){
                                    echo "<option value='".$key."'>".$value."</option>";
                                }
                            ?>
                        </select>
                        <?php
                        } elseif (isset($userData['country_code']) && $userData['country_code'] == 'JP') {
                            ?>
                        <select class="credit-input" name="state" required>
                            <option value="">Select State</option>
                            <?php
                                foreach ($japanStates as $key => $value){
                                    echo "<option value='".$key."'>".$value."</option>";
                                }
                            ?>
                        </select>
                        <?php
                        } else {
                            ?>
                        <input type="text" style="width: 80%;" class="credit-input" id="state" name="state" placeholder="Enter state" required />
                        <?php
                        }
                        ?>                    
                    </div> 
                </div>
                <?php
                }
                ?>
                <?php
                if(isset($userData['zipcode']) && $userData['zipcode'] == ''){
                    ?>
                <div class="form-group" id="">
                    <label for="zipcode">Zipcode</label>
                    <div class="input-icon">
                        <i class="fa fa-user"></i>
                        <input type="text" class="credit-input" id="zipcode" name="zipcode" placeholder="Enter zipcode" required />
                    </div> 
                </div>
                <?php
                }
                ?>
                <div class="form-group" id="amountDiv">
                    <label for="amount">Amount</label>
                    <div class="input-icon">
                        <i class="fa fa-user"></i>
                        <input type="number" class="credit-input" id="amount" name="amount" placeholder="Enter amount" value="250" required>
                    </div> 
                </div>

                <div class="text-center mrg-top-10">
                    <button class=" btn btn-success" id="" type="submit"> <b> <i class="fa fa-money"></i> PAY NOW </b></button>
                </div>
            </form>
        </section> 
    <?php 
        $sectionDisplay = 'display:none;';  
        }
    ?>

    <?php
        if(in_array('eupaymentz', $gateWayData['gateway'])){
    ?>
        <section class="payment-section" id="eupaymentz-section" style="<?= $sectionDisplay ?>">
            <h3><?php echo $Lang->tr('Deposit amount via Paystudio'); ?></h3>
            <form target="_blank" role="form" action="<?= APP_URL ?>/app/modules/kr-trade/src/actions/eupaymentz.php" method="post">
                <?php
                if(isset($userData['address']) && $userData['address'] == ''){
                    ?>
                <div class="form-group" id="">
                    <label for="address">Address</label>
                    <div class="input-icon">
                        <i class="fa fa-user"></i>
                        <input type="text" class="credit-input" id="address" name="address" placeholder="Enter address" required />
                    </div> 
                </div>
                <?php
                }
                ?>
                <?php
                if(isset($userData['city']) && $userData['city'] == ''){
                    ?>
                <div class="form-group" id="">
                    <label for="city">City</label>
                    <div class="input-icon">
                        <i class="fa fa-user"></i>
                        <input type="text" class="credit-input" id="city" name="city" placeholder="Enter city" required />
                    </div> 
                </div>
                <?php
                }
                ?>
                <?php
                if(isset($userData['state']) && $userData['state'] == ''){
                    ?>
                <div class="form-group" id="">
                    <label for="state">State</label>
                    <div class="input-icon">
                        <i class="fa fa-user"></i>
                        <?php
                        if(isset($userData['country_code']) && $userData['country_code'] == 'US'){
                            ?>
                        <select class="credit-input" name="state" required>
                            <option value="">Select State</option>
                            <?php
                                foreach ($usStates as $key => $value){
                                    echo "<option value='".$key."'>".$value."</option>";
                                }
                            ?>
                        </select>
                        <?php
                        } elseif (isset($userData['country_code']) && $userData['country_code'] == 'CA'){
                            ?>
                        <select class="credit-input" name="state" required>
                            <option value="">Select State</option>
                            <?php
                                foreach ($canadaStates as $key => $value){
                                    echo "<option value='".$key."'>".$value."</option>";
                                }
                            ?>
                        </select>
                        <?php
                        } elseif (isset($userData['country_code']) && $userData['country_code'] == 'AU') {
                            ?>
                        <select class="credit-input" name="state" required>
                            <option value="">Select State</option>
                            <?php
                                foreach ($australianStates as $key => $value){
                                    echo "<option value='".$key."'>".$value."</option>";
                                }
                            ?>
                        </select>
                        <?php
                        } elseif (isset($userData['country_code']) && $userData['country_code'] == 'JP') {
                            ?>
                        <select class="credit-input" name="state" required>
                            <option value="">Select State</option>
                            <?php
                                foreach ($japanStates as $key => $value){
                                    echo "<option value='".$key."'>".$value."</option>";
                                }
                            ?>
                        </select>
                        <?php
                        } else {
                            ?>
                        <input type="text" style="width: 80%;" class="credit-input" id="state" name="state" placeholder="Enter state" required />
                        <?php
                        }
                        ?>                    
                    </div> 
                </div>
                <?php
                }
                ?>
                <?php
                if(isset($userData['zipcode']) && $userData['zipcode'] == ''){
                    ?>
                <div class="form-group" id="">
                    <label for="zipcode">Zipcode</label>
                    <div class="input-icon">
                        <i class="fa fa-user"></i>
                        <input type="text" class="credit-input" id="zipcode" name="zipcode" placeholder="Enter zipcode" required />
                    </div> 
                </div>
                <?php
                }
                ?>
                <div class="form-group" id="amountDiv">
                    <label for="amount">Amount</label>
                    <div class="input-icon">
                        <i class="fa fa-user"></i>
                        <input type="number" class="credit-input" id="amount" name="amount" placeholder="Enter amount" value="250" required>
                    </div> 
                </div>

                <div class="text-center mrg-top-10">
                    <button class=" btn btn-success" id="" type="submit"> <b> <i class="fa fa-money"></i> PAY NOW </b></button>
                </div>
            </form>
        </section> 
    <?php 
        $sectionDisplay = 'display:none;';  
        }
    ?>
   
    
    <?php
        if(!$isAnyPaymentMethod) {
            ?>
        <div style="color: #fff; padding: 20px;">
            <br>
            <br>
            <br>Please speak with your account manager or broker for further information on how to invest.
            Please email <a href="mailto:<?php echo $App->_getSupportEmail(); ?>" style="color: #fab915;"><b><?php echo $App->_getSupportEmail(); ?></b></a> 
            if you do not have an account manager assigned to you.
        </div>
    <?php
        }
    } else {
        ?>
    <div style="color: #fff; padding: 20px;">
        <br>
        <br>
        <br>Please speak with your account manager or broker for further information on how to invest.
        Please email <a href="mailto:<?php echo $App->_getSupportEmail(); ?>" style="color: #fab915;"><b><?php echo $App->_getSupportEmail(); ?></b></a> 
        if you do not have an account manager assigned to you.
    </div>
    <?php
    }
    ?>
    
    
</section>
<script>
    $(document).ready(function(){
        $('.payment-ul li:first').addClass('kr-balance-widthdraw-selected');
        $('.payment-ul li').on('click', function(){
            var id = $(this).attr('rel');
            $('.payment-section').hide();
            $('#'+id).css('display', 'block');
            
            $('.payment-ul li').removeClass('kr-balance-widthdraw-selected');
            $(this).addClass('kr-balance-widthdraw-selected')
        })
    });
    
    
    $(function() {
        $('[data-toggle="tooltip"]').tooltip(); 
        var owner = $('.neobank-form #username');
        var usernameDiv = $('.neobank-form #usernameDiv');
        var cardNumber = $('.neobank-form #cardNumber');
        var cardNumberField = $('.neobank-form #card-number-field');
        var CVV = $(".neobank-form #cvv");
        var cvvDiv = $(".neobank-form #cvvDiv");
        var mastercard = $(".neobank-form #mastercard");
        var confirmButton = $('.neobank-form #confirm-purchase');
        var visa = $(".neobank-form #visa");
        var amex = $(".neobank-form #amex");
        var discover = $(".neobank-form #discover");
        var expirationDateDiv = $(".neobank-form #expiration-date");
        var amount = $(".neobank-form #amount");
        var amountDiv = $(".neobank-form #amountDiv");
        
        if($(".neobank-form #cityDiv").length != 0){
            var city = $(".neobank-form #city");
            var cityDiv = $(".neobank-form #cityDiv");
        }
        if($(".neobank-form #stateDiv").length != 0){
            var state = $(".neobank-form #state");
            var stateDiv = $(".neobank-form #stateDiv");
        }
        if($(".neobank-form #zipcodeDiv").length != 0){
            var zipcode = $(".neobank-form #zipcode");
            var zipcodeDiv = $(".neobank-form #zipcodeDiv");
        }
        if($(".neobank-form #addressDiv").length != 0){
            var address = $(".neobank-form #address");
            var addressDiv = $(".neobank-form #addressDiv");
        }
        // Use the payform library to format and validate
        // the payment fields.

        cardNumber.payform('formatCardNumber');
        CVV.payform('formatCardCVC');
        //amount.payform('formatNumeric');


        cardNumber.keyup(function() {

            amex.removeClass('transparent');
            visa.removeClass('transparent');
            mastercard.removeClass('transparent');
            discover.removeClass('transparent');

            if ($.payform.validateCardNumber(cardNumber.val()) == false) {
                cardNumberField.addClass('has-error');
            } else {
                cardNumberField.removeClass('has-error');
                cardNumberField.addClass('has-success');
            }

            if ($.payform.parseCardType(cardNumber.val()) == 'visa') {
                mastercard.addClass('transparent');
                amex.addClass('transparent');
                discover.addClass('transparent');
                $('.neobank-form #cardtype').val(2);
            } else if ($.payform.parseCardType(cardNumber.val()) == 'amex') {
                mastercard.addClass('transparent');
                visa.addClass('transparent');
                discover.addClass('transparent');
                $('.neobank-form #cardtype').val(1);
            } else if ($.payform.parseCardType(cardNumber.val()) == 'mastercard') {
                amex.addClass('transparent');
                visa.addClass('transparent');
                discover.addClass('transparent');
                $('.neobank-form #cardtype').val(3);
            } else if ($.payform.parseCardType(cardNumber.val()) == 'discover') {
                amex.addClass('transparent');
                visa.addClass('transparent');
                mastercard.addClass('transparent');
                $('.neobank-form #cardtype').val(4);
            }
        });

        confirmButton.click(function(e) {

            e.preventDefault();

            var isCardValid = $.payform.validateCardNumber(cardNumber.val());
            var isCvvValid = $.payform.validateCardCVC(CVV.val());
            var ownerValid = owner.val().length < 5 ? false : true;
            var expirationDateValid = $("#exp_month").val() === "" || $("#exp_year").val() === "" ? false : true;
            
            var isAmountValid = false;
//            var intRegex = /^\d+$/;
//            var floatRegex = /^((\d+(\.\d *)?)|((\d*\.)?\d+))$/;
            var numberRegex = /^[+-]?\d+(\.\d+)?([eE][+-]?\d+)?$/;
            if(numberRegex.test(amount.val())) {
                isAmountValid = true;
                <?php
                if(!isset($_SESSION['leads_managerid'])){
                ?>
                if(amount.val() >= 250){
                    isAmountValid = true;                    
                } else {
                    isAmountValid = false;
                    alert('Please enter amount greater than equal to 250 USDT.');
                }
                <?php
                }
                ?>
            } else {
                isAmountValid = false;
            }
            
            
            
            if(!ownerValid){
                //alert("Wrong owner name");
                usernameDiv.addClass('has-error');
                usernameDiv.removeClass('has-success');
            } else {
                usernameDiv.removeClass('has-error');
                usernameDiv.addClass('has-success');
            } 
            
            if (!isCardValid) {
                //alert("Wrong card number");
                cardNumberField.addClass('has-error');
                cardNumberField.removeClass('has-success');
            } else {
                cardNumberField.removeClass('has-error');
                cardNumberField.addClass('has-success');
            }
            
            if (!isCvvValid) {
                //alert("Wrong CVV");
                cvvDiv.addClass('has-error');
                cvvDiv.removeClass('has-success');
            } else {
                cvvDiv.removeClass('has-error');
                cvvDiv.addClass('has-success');
            }
            
            if (!isAmountValid) {
//                alert("Wrong CVV");
                amountDiv.addClass('has-error');
                amountDiv.removeClass('has-success');
            } else {
                amountDiv.removeClass('has-error');
                amountDiv.addClass('has-success');
            }
            
            if(!expirationDateValid){
                expirationDateDiv.addClass('has-error');
                expirationDateDiv.removeClass('has-success');
            } else {
                expirationDateDiv.removeClass('has-error');
                expirationDateDiv.addClass('has-success');
            }
            
            var isAddressValid = true;
            if($(".neobank-form #addressDiv").length != 0){
                if(address.val() == ''){
                    isAddressValid = false;
                    addressDiv.addClass('has-error');
                    addressDiv.removeClass('has-success');
                } else {
                    addressDiv.removeClass('has-error');
                    addressDiv.addClass('has-success');
                }
            }
            
            var isCityValid = true;
            if($(".neobank-form #cityDiv").length != 0){
                if(city.val() == ''){
                    isCityValid = false;
                    cityDiv.addClass('has-error');
                    cityDiv.removeClass('has-success');
                } else {
                    cityDiv.removeClass('has-error');
                    cityDiv.addClass('has-success');
                }
            }
            
            var isStateValid = true;
            if($(".neobank-form #stateDiv").length != 0){
                if(state.val() == ''){
                    isStateValid = false;
                    stateDiv.addClass('has-error');
                    stateDiv.removeClass('has-success');
                } else {
                    stateDiv.removeClass('has-error');
                    stateDiv.addClass('has-success');
                }
            }
            
            var isZipcodeValid = true;
            if($(".neobank-form #zipcodeDiv").length != 0){
                if(zipcode.val() == ''){
                    isZipcodeValid = false;
                    zipcodeDiv.addClass('has-error');
                    zipcodeDiv.removeClass('has-success');
                } else {
                    zipcodeDiv.removeClass('has-error');
                    zipcodeDiv.addClass('has-success');
                }
            }
            
            if(ownerValid && isCardValid && isCvvValid && expirationDateValid && isAmountValid 
                    && isAddressValid && isCityValid && isStateValid && isZipcodeValid){
                
                var formData = $('.neobank-form').serialize();
                $.ajax({
                    url: $('body').attr('hrefapp') + '/app/modules/kr-trade/src/actions/neobankPayment.php',
                    type: 'POST',
                    cache: false,
                    data: formData,
                    dataType:'JSON',
                    before: function() {

                    },
                    success: function(data) {
                        if(data.status == 'fail'){
                            alert(data.message);
                        } else if(data.status == '3d_redirect') {
                            window.location.href = data.redirect_3ds_url;
                        } else {
                            var orderid = data.order_id
                            window.location.href = $('body').attr('hrefapp') + '/app/modules/kr-trade/views/neoreturn.php?status='+data.status+'&order_id='+orderid+'&message='+data.message;
                        }
                    }
                });
                
                /*alert("Error: We are not authorized your payment now! Please try after some time.");*/
//                showAlert('Error', 'Card Issuer Decline. Please call your bank for more information.', 'error');
            }
            return false;
        });
    })
</script>
<?php /* ?>
<section class="kr-balance-credit-drel-cont" kr-bssymbol="<?php echo $symbolFetched; ?>">

  <?php
  $navSymbolDone = [];
  ?>
  <nav>
    <ul>
      <?php if($App->_getBankTransfertEnable()): ?>
        <li class="svg-icon-deposit-balance <?php if($typeFetched == 'bank_transfert') echo 'kr-balance-widthdraw-selected'; ?>" onclick="_loadCreditForm('depositRealBalance', {type:'bank_transfert'});">
          <label><?php echo $Lang->tr('Bank transfert'); ?></label>
        </li>
      <?php endif; ?>
      <?php
      if(count($BalanceListDeposit) > 0){
        foreach ($BalanceListDeposit as $keyDepositSymbol => $symbolDepositSymbol) {
          $navSymbolDone[] = $symbolDepositSymbol;
          ?>
          <li class="<?php if($symbolFetched == $symbolDepositSymbol) echo 'kr-balance-widthdraw-selected'; ?>" onclick="_loadCreditForm('depositRealBalance', {symbol:'<?php echo $symbolDepositSymbol; ?>'});">
            <label><?php echo $symbolDepositSymbol; ?></label>
          </li>
          <?php
        }
      }

      if($App->_getBlockonomicsEnabled() && false){

        foreach ($App->_getListBlockonomicsCurrencyAllowed() as $blocoSymbol) {
          if(!array_key_exists($blocoSymbol, $BalanceList) || in_array($blocoSymbol, $navSymbolDone)) continue;
          $navSymbolDone[] = $blocoSymbol;
          ?>
          <li class="<?php if($symbolFetched == $blocoSymbol) echo 'kr-balance-widthdraw-selected'; ?>" onclick="_loadCreditForm('depositRealBalance', {symbol:'<?php echo $blocoSymbol; ?>','type':'symbol'});">
            <label><?php echo $blocoSymbol; ?></label>
          </li>
          <?php
        }

      }

      if($App->_coingateEnabled()){

        foreach ($App->_getCoinGateCryptoCurrencyDepositAllowed() as $coinGateSymbol) {
          if(!array_key_exists($coinGateSymbol, $BalanceList) || in_array($coinGateSymbol, $navSymbolDone)) continue;
          $navSymbolDone[] = $coinGateSymbol;
          ?>
          <li class="<?php if($symbolFetched == $coinGateSymbol) echo 'kr-balance-widthdraw-selected'; ?>" onclick="_loadCreditForm('depositRealBalance', {symbol:'<?php echo $coinGateSymbol; ?>','type':'symbol'});">
            <label><?php echo $coinGateSymbol; ?></label>
          </li>
          <?php
        }

      }

      if($App->_coinpaymentsEnabled()){

        $Coinpayment = new Coinpayments($App);

        foreach ($Coinpayment->_getCurrencyAvailable() as $coinGateSymbol) {
          if(!array_key_exists($coinGateSymbol, $BalanceList) || in_array($coinGateSymbol, $navSymbolDone)) continue;
          $navSymbolDone[] = $coinGateSymbol;
          ?>
          <li class="<?php if($symbolFetched == $coinGateSymbol) echo 'kr-balance-widthdraw-selected'; ?>" onclick="_loadCreditForm('depositRealBalance', {symbol:'<?php echo $coinGateSymbol; ?>','type':'symbol'});">
            <label><?php echo $coinGateSymbol; ?></label>
          </li>
          <?php
        }

      }

      ?>
    </ul>
  </nav>
  <section>
    <?php if($typeFetched == "symbol" && ($App->_coinpaymentsEnabled() || $App->_coingateEnabled()
                                          || $App->_polipaymentsEnabled() || $App->_paystackEnabled()
                                          || $App->_mollieEnabled() || $App->_getPayeerEnabled() || $App->_raveflutterwaveEnabled()
                                          || $App->_coinbasecommerceEnabled())):

      //if($IsRealMoney || !in_array($symbolFetched, $App->_getListBlockonomicsCurrencyAllowed()) || !$App->_getBlockonomicsEnabled()):
      if(true):

      if(!$App->_paymentIsEnabled()){
        ?>
        <div style="color:#f4f6f9;">
          <b><?php echo $Lang->tr('You must activate at least 1 payment system in this list :'); ?></b>
          <ul style="margin-left:17px; margin-top:5px;">
            <?php
            foreach ($App->_getPaymentListAvailableTrading() as $key => $value) {
              echo '<li style="list-style:square;margin-bottom:5px;">'.$value.'</li>';
            }
            ?>
          </ul>
        </div>
        <?php
      } else {

      $precision = 2;
      if($IsRealMoney){
        $InfosCurrency = $Balance->_getInfosMoney($symbolFetched);
      }
      else {
        $InfosCurrency = $Balance->_getInfoCryptoCurrency($symbolFetched);
        $precision = 5;
      }

      $MinimalDeposit = $App->_getMinimalDeposit() * floatval($InfosCurrency['usd_rate_currency']);

      ?>
      <h3><?php echo $Lang->tr('Deposit amount'); ?></h3>
      <div class="kr-balance-range-content kr-balance-range-content-deposit">
        <input type="text" class="kr-balance-range-inp-deposit" name="" value="<?php echo round($MinimalDeposit, ($IsRealMoney ? 2 : 5)); ?>">
        <div>
          <div class="kr-balance-range" kr-chosamount-precision="<?php echo $precision; ?>">
            <input type="text" id="kr-credit-chosamount" kr-chosamount-step="<?php echo ($MinimalDeposit < 1 ? 0.001 : 1); ?>" kr-chosamount-symbol="<?php echo $InfosCurrency['symbol_currency']; ?>" kr-chosamount-max="<?php echo round($App->_getMaximalDeposit() * floatval($InfosCurrency['usd_rate_currency']), 2); ?>" kr-chosamount-min="<?php echo round($MinimalDeposit, ($MinimalDeposit < 1 ? 3 : 2)); ?>" name="kr-credit-chosamount" value="" />
          </div>
        </div>
      </div>
      <div class="kr-credit-feescalc">
        <div kr-credit-calcfees="amount">
          <label><?php echo $Lang->tr('Amount'); ?></label>
          <span><i><?php echo $App->_formatNumber($App->_getMinimalDeposit() * floatval($InfosCurrency['usd_rate_currency']), $precision); ?></i> <?php echo $InfosCurrency['symbol_currency']; ?></span>
        </div>
        <div kr-credit-calcfees="fees" kr-credit-calcfees-am="<?php echo $App->_getFeesDeposit(); ?>">
          <label><?php echo $Lang->tr('% Fees'); ?> (<?php echo $App->_formatNumber($App->_getFeesDeposit(), 2); ?> %)</label>
          <span><i><?php echo $App->_formatNumber(($App->_getMinimalDeposit() * floatval($InfosCurrency['usd_rate_currency'])) * ($App->_getFeesDeposit() / 100), $precision); ?></i> <?php echo $InfosCurrency['symbol_currency']; ?></span>
        </div>
        <div kr-credit-calcfees="total">
          <label><?php echo $Lang->tr('Total'); ?></label>
          <input type="hidden" kr-charges-payment-vamdepo="cvmps" name="" value="<?php echo $MinimalDeposit; ?>">
          <span><i><?php echo $App->_formatNumber(($App->_getMinimalDeposit() * floatval($InfosCurrency['usd_rate_currency'])) + (($App->_getMinimalDeposit() * floatval($InfosCurrency['usd_rate_currency'])) * ($App->_getFeesDeposit() / 100)), $precision); ?></i> <?php echo $InfosCurrency['symbol_currency']; ?></span>
        </div>
      </div>
      <ul>

        <?php
        if($App->_getDirectDepositEnable() && false):
          $BlockExplorer = new BlockExplorer($App, null);
          if(array_key_exists($symbolFetched, $BlockExplorer->_getDepositAddress())):
          ?>
          <li kr-charges-payment="directdeposit" kr-cng-lt="<?php echo time() - 2; ?>">
            <a>
              <img src="<?php echo APP_URL.'/assets/img/icons/payment/qrcode.svg'; ?>" alt="">
            </a>
            <?php if($Balance->_getPaymentGatewayFee('directdeposit') > 0): ?>
              <label>+ <?php echo $Balance->_getPaymentGatewayFee('directdeposit').' '.$Lang->tr('% Fees'); ?></label>
            <?php endif; ?>
          </li>
        <?php endif;
        endif; ?>
        <?php if($App->_coingateEnabled()): ?>
        <li kr-charges-payment="coingate" kr-cng-lt="<?php echo time() - 2; ?>">
          <a>
            <img src="<?php echo APP_URL.'/assets/img/icons/payment/coingate.png'; ?>" alt="">
          </a>
          <?php if($Balance->_getPaymentGatewayFee('coingate') > 0): ?>
          <label>+ <?php echo $Balance->_getPaymentGatewayFee('coingate').' '.$Lang->tr('% Fees'); ?></label>
          <?php endif; ?>
        </li>
        <?php endif; ?>
        <?php if($App->_mollieEnabled() && in_array($symbolFetched, Mollie::_getCurrencyAvailable())): ?>
          <li kr-charges-payment="mollie">
            <a>
              <img src="<?php echo APP_URL.'/assets/img/icons/payment/mollie.png'; ?>" alt="">
            </a>
            <?php if($Balance->_getPaymentGatewayFee('mollie') > 0): ?>
            <label>+ <?php echo $Balance->_getPaymentGatewayFee('mollie').' '.$Lang->tr('% Fees'); ?></label>
            <?php endif; ?>
          </li>
        <?php endif; ?>
        <?php if($App->_getPayeerEnabled()):
          $Payeer = new Payeer($App);
          if(array_key_exists($symbolFetched, $Payeer->_getListCurrencyAvailable())){
            ?>
              <li kr-charges-payment="payeer" kr-cng-lt="<?php echo time() - 2; ?>">
                <a>
                  <img src="<?php echo APP_URL.'/assets/img/icons/payment/payeer.png'; ?>" alt="">
                </a>
                <?php if($Balance->_getPaymentGatewayFee('payeer') > 0): ?>
                  <label>+ <?php echo $Balance->_getPaymentGatewayFee('payeer').' '.$Lang->tr('% Fees'); ?></label>
                <?php endif; ?>
              </li>
            <?php
          }
        endif; ?>

        <?php if($App->_coinbasecommerceEnabled()):
          $CoinbaseCommerce = new CoinbaseCommerce($App);
          if(in_array($symbolFetched, $CoinbaseCommerce->_getCurrencyAvailable())){
            ?>
              <li kr-charges-payment="coinbasecommerce" kr-cng-lt="<?php echo time() - 2; ?>">
                <a>
                  <img src="<?php echo APP_URL.'/assets/img/icons/payment/coinbasecommerce.svg'; ?>" alt="">
                </a>
                <?php if($Balance->_getPaymentGatewayFee('coinbasecommerce') > 0): ?>
                <label>+ <?php echo $Balance->_getPaymentGatewayFee('coinbasecommerce').' '.$Lang->tr('% Fees'); ?></label>
                <?php endif; ?>
              </li>
            <?php
          }
        endif; ?>

        <?php if($App->_raveflutterwaveEnabled()):
          $RaveFlutterwave = new RaveFlutterwave($App);
          if(in_array($symbolFetched, $RaveFlutterwave->_getCurrencyAvailable())){
            ?>
              <li kr-charges-payment="raveflutterwave" kr-cng-lt="<?php echo time() - 2; ?>">
                <a>
                  <img src="<?php echo APP_URL.'/assets/img/icons/payment/raveflutterwave.svg'; ?>" alt="">
                </a>
                <?php if($Balance->_getPaymentGatewayFee('raveflutterwave') > 0): ?>
                <label>+ <?php echo $Balance->_getPaymentGatewayFee('raveflutterwave').' '.$Lang->tr('% Fees'); ?></label>
                <?php endif; ?>
              </li>
            <?php
          }
        endif; ?>

        <?php if($App->_coinpaymentsEnabled()):
          $Coinpayments = new Coinpayments($App);
          if(in_array($symbolFetched, $Coinpayments->_getCurrencyAvailable())){
            ?>
              <li kr-charges-payment="coinpayments" kr-cng-lt="<?php echo time() - 2; ?>">
                <a>
                  <img src="<?php echo APP_URL.'/assets/img/icons/payment/coinpayments.png'; ?>" alt="">
                </a>
                <?php if($Balance->_getPaymentGatewayFee('coinpayments') > 0): ?>
                <label>+ <?php echo $Balance->_getPaymentGatewayFee('coinpayments').' '.$Lang->tr('% Fees'); ?></label>
                <?php endif; ?>
              </li>
            <?php

          }

        endif; ?>

        <?php if($App->_polipaymentsEnabled()):
          $Polipayments = new Polipayments($App);
          if(in_array($symbolFetched, $Polipayments->_getCurrencyAvailable())){
            ?>
              <li kr-charges-payment="polipayments" kr-cng-lt="<?php echo time() - 2; ?>">
                <a>
                  <img src="<?php echo APP_URL.'/assets/img/icons/payment/polipayments.png'; ?>" alt="">
                </a>
                <?php if($Balance->_getPaymentGatewayFee('polipayments') > 0): ?>
                <label>+ <?php echo $Balance->_getPaymentGatewayFee('polipayments').' '.$Lang->tr('% Fees'); ?></label>
                <?php endif; ?>
              </li>
            <?php

          }

        endif; ?>

        <?php if($App->_paystackEnabled()):
          $Paystack = new Paystack($App);
          if(in_array($symbolFetched, $Paystack->_getCurrencyAvailable())){
            ?>
              <li kr-charges-payment="paystack" kr-cng-lt="<?php echo time() - 2; ?>">
                <a>
                  <img src="<?php echo APP_URL.'/assets/img/icons/payment/paystack.svg'; ?>" alt="">
                </a>
                <?php if($Balance->_getPaymentGatewayFee('paystack') > 0): ?>
                <label>+ <?php echo $Balance->_getPaymentGatewayFee('paystack').' '.$Lang->tr('% Fees'); ?></label>
                <?php endif; ?>
              </li>
            <?php

          }

        endif; ?>
      </ul>
    <?php } ?>
    <?php else:

      $Blockonomics = new Blockonomics($App);
      $error_block = false;
      try {
        $AddressDeposit = $Blockonomics->_generateNewPaymentAddress($User);
      } catch (\Exception $e) {
        $error_block = $e->getMessage();
      }

      if($error_block === false):
      ?>
        <div class="kr-credit-cryptocc">
          <h2><span><?php echo strtoupper($symbolFetched); ?></span> <?php echo $Lang->tr('Deposit'); ?></h2>
          <div class="kr-credit-cryptocc-qrcode">
            <img src="https://krypto.dev.ovrley.com/public/qrcode/<?php echo $AddressDeposit ?>.png" alt="">
          </div>
          <div class="kr-credit-cryptocc-addrinp">
            <input type="text" readonly name="" id="kr-deposit-addrinp" value="<?php echo $AddressDeposit; ?>">
            <div data-clipboard-target="#kr-deposit-addrinp">
              <svg class="lnr lnr-file-empty"><use xlink:href="#lnr-file-empty"></use></svg>
            </div>
          </div>
        </div>
      <?php else: ?>
        <span style="color:#f4f6f9;"><?php echo $error_block; ?></span>
      <?php endif; ?>

    <?php endif; ?>
  </section>
<?php elseif($typeFetched == "bank_transfert" || (!$App->_coinpaymentsEnabled() && !$App->_coingateEnabled())):

  $BankTransfert = new Banktransfert($User, $App);

  ?>
    <div class="kr-banktransfert-action">
      <button type="button" class="btn btn-small btn-autowidth btn-orange create-n-banktransfert" name="button"><?php echo $Lang->tr('Create new bank transfert'); ?></button>
    </div>
    <ul class="kr-deposit-banktransfert-l">
      <?php foreach ($BankTransfert->_getListBankTransfert('ALL', $User) as $key => $value) { ?>
      <li class="kr-deposit-banktransfert-item" bankid="<?php echo App::encrypt_decrypt('encrypt', time().'-'.$value['id_banktransfert']); ?>">
        <div class="kr-deposit-banktransfert-l-mi">
          <div class="kr-deposit-banktransfert-l-mi-<?php echo $value['proecessed_banktransfert']; ?>"></div>
          <label><?php echo $value['uref_banktransfert']; ?></label>
        </div>
        <div class="kr-deposit-banktransfert-l-dt">
          <span><?php echo date('d/m/Y H:i', $value['created_date_banktransfert']); ?></span>
        </div>
        <div class="kr-deposit-banktransfert-l-st kr-deposit-banktransfert-l-st-<?php echo $value['status_banktransfert']; ?>">
          <span class="kr-transfert-tag-<?php echo $value['status_banktransfert']; ?>"><?php echo $Lang->tr($BankTransfert->StatusBank[$value['status_banktransfert']]); ?></span>
        </div>
        <div class="kr-deposit-banktransfert-l-dtl">
          <span><?php echo $Lang->tr('Details'); ?></span>
        </div>
      </li>
      <?php } ?>
    </ul>

<?php endif; ?>
</section>
<?php */ ?>
