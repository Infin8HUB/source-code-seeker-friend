<?php
session_start();

require "../../../config/config.settings.php";

require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/vendor/autoload.php";

require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/MySQL/MySQL.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/App/App.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/App/AppModule.php";

require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/User/User.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/Lang/Lang.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/Leads8Api/LeadsApi.php";

$App = new App(true);
$App->_loadModulesControllers();

$User = new User();

$Lang = new Lang(null, $App);

if(!$App->_allowSignup()) die('Permission denied');

$ip_address = '127.0.0.1';
if (!empty($_SERVER['HTTP_CLIENT_IP']))   
{
  $ip_address = $_SERVER['HTTP_CLIENT_IP'];
}
//whether ip is from proxy
elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))  
{
  $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
}
//whether ip is from remote address
else
{
  $ip_address = $_SERVER['REMOTE_ADDR'];
}


?>
<div class="kr-loading-fnc">
  <div> <div class="sk-folding-cube sk-folding-cube-orange"> <div class="sk-cube1 sk-cube"></div> <div class="sk-cube2 sk-cube"></div> <div class="sk-cube4 sk-cube"></div> <div class="sk-cube3 sk-cube"></div> </div> </div>
</div>
<header style="width: auto;">
  <img src="<?php echo APP_URL.$App->_getLogoPath(); ?>" onclick="window.location.href='<?=APP_URL?>';" alt="" style="cursor: pointer;">
</header>
<?php

  if($App->_visitorAllowedRegister()):
?>
<section class="kr-login-act" act="<?php echo APP_URL; ?>/app/modules/kr-user/src/actions/signup.php">
  <section class="kr-login-field">
    <input type="text" name="kr_usr_name" placeholder="<?php echo $Lang->tr('Your name'); ?>" value="">
    <div class="kr-i-msg-f-kr_usr_name"><span></span></div>
    <input type="text" name="kr_usr_email" placeholder="<?php echo $Lang->tr('Your e-mail address'); ?>" value="">
    <div class="kr-i-msg-f-kr_usr_email"><span></span></div>
    <input type="password" name="kr_usr_pwd" placeholder="<?php echo $Lang->tr('Your password'); ?>" value="">
    <div class="kr-i-msg-f-kr_usr_pwd kr-login-i-last"><span></span></div>
    <input type="password" name="kr_usr_rep_pwd" placeholder="<?php echo $Lang->tr('Repeat your password'); ?>" value="">
    <div class="kr-i-msg-f-kr_usr_rep_pwd kr-login-i-last"><span></span></div>
    <div class="kr-user-f-l">
        <div style="width: 18%">
            <?php
            //echo "ankit here122";exit;
            $leadsApiObj = new LeadsApi();
            $response = $leadsApiObj->callCurl('get_country_list', []);
            //echo "<pre>";print_r($response);exit;
            if (isset($response['statuscode']) && $response['statuscode'] == '200' && isset($response['country'])) {
                $countryData = $response['country'];
                $responseIpCountry = $leadsApiObj->callCurl('get_country_from_ip', ['brand_id' => $leadsApiObj->getBusinessId(), 'ip_address' => $ip_address]);
                ?>

                <select name="countries" id="countries" style="width: 100%">
                    <?php
                    foreach ($countryData as $country){
                        $selected = ($responseIpCountry == $country['countryCode']) ? 'selected' : '';
                        ?>
                        <option value="<?=$country['countryCode']?>" <?= $selected ?> data-callingCode="<?=$country['callingCode']?>" data-imagecss="flag <?= strtolower($country['countryCode'])?>" data-title="<?=$country['countryName']?>"><?=$country['countryName']?></option>
                        <?php
                    }
                    ?>
                </select>
                <div class="kr-i-msg-f-kr_usr_country"><span></span></div>

            <?php
            }
            ?>
        </div>
        <div style="width:70%;">    
            <input type="hidden" value="" id="calling_code" name="kr_usr_country"/>
            <input type="text" name="lcp-ccode" id="lcp-ccode" placeholder="<?php echo $Lang->tr('Calling Code'); ?>" value="" style="width:70%;margin-left: 24px;">
        </div>
        <div style="width:80%;">    
            <input type="text" name="kr_usr_phone_number" placeholder="<?php echo $Lang->tr('Your phone number'); ?>" value="" style="width: 100%;float: right;">
            <div class="kr-i-msg-f-kr_usr_phone_number"><span></span></div>
        </div>
    </div>
    <input type="text" name="kr_affil_id" placeholder="<?php echo $Lang->tr('Enter Affiliate Id'); ?>" value="<?= isset($_SESSION['affil_id']) ? $_SESSION['affil_id'] : ''; ?>">
    <?php if($App->_captchaSignup()): ?>
    <div class="g-recaptcha login-captcha" data-sitekey="<?php echo $App->_getGoogleRecaptchaSiteKey(); ?>"></div>
    <?php endif; ?>
    
    <div class="kr-signup-check">
      <input type="checkbox" name="kr_usr_agree" id="kr_usr_agree">
      <label for="kr_usr_agree">I agree to the <i onclick="loadTermsPage('term_use');return false;">terms of service</i></label>
    </div>
    <footer>
      <a class="kr-gologin-view"><?php echo $Lang->tr('Back to login'); ?></a>
      <?php if($App->_captchaSignup()): ?>
      <button
        class="btn-shadow"
        data-sitekey=""
        data-size=""
        data-callback="kryptoSignup"><?php echo strtoupper($Lang->tr("Let's go !")); ?></button>
      <?php else: ?>
        <button class="btn-shadow"><?php echo strtoupper($Lang->tr("Let's go !")); ?></button>
      <?php endif; ?>
    </footer>
  </section>
</section>
<?php else:
  $LocationInfos = $App->_getVisitorLocation();
  ?>
<section class="kr-login-act-msg">
  <span>Your country is blacklisted</span>
  <?php if(strlen($App->_getSupportEmail()) > 1 || strlen($App->_getSupportPhone()) > 1): ?>
  <p>You can contact our support here</p>
  <ul>
    <?php if(strlen($App->_getSupportEmail()) > 1): ?>
      <li><?php echo $App->_getSupportEmail(); ?></li>
    <?php endif;
    if(strlen($App->_getSupportPhone()) > 1):
    ?>
      <li><?php echo $App->_getSupportPhone(); ?></li>
    <?php endif; ?>
  </ul>
  <?php endif; ?>
</section>
<?php endif; ?>

<?php if($App->_captchaSignup()): ?>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<?php endif; ?>
<script>
        $(document).ready(function() {
            try{
                $("#countries").msDropdown().data('dd');
                //$("#countries").hide();
            } catch(e){
                
            }
            
            $('#countries').on('change', function (e) {
                var option = $(this).val();
                $("#calling_code").val(option);
                
                var callingCode = $( "#countries option:selected" ).attr('data-callingCode');
                $("#lcp-ccode").val(callingCode);
            }).change();
            
            
        });
    </script>
