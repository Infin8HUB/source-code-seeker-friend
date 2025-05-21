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

?>
<div class="kr-loading-fnc">
  <div> <div class="sk-folding-cube sk-folding-cube-orange"> <div class="sk-cube1 sk-cube"></div> <div class="sk-cube2 sk-cube"></div> <div class="sk-cube4 sk-cube"></div> <div class="sk-cube3 sk-cube"></div> </div> </div>
</div>
<header style="width: auto;">
  <img src="<?php echo APP_URL.$App->_getLogoBlackPath(); ?>" onclick="window.location.href='<?=APP_URL?>';" alt="" style="cursor: pointer;">
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
    <?php
    //echo "ankit here122";exit;
    $leadsApiObj = new LeadsApi();
    $response = $leadsApiObj->callCurl('get_country_list', []);
    //echo "<pre>";print_r($response);exit;
    if (isset($response['statuscode']) && $response['statuscode'] == '200' && isset($response['country'])) {
        ?>
        <select name="kr_usr_country">
            <option value="">Your Country</option>
        <?php
        foreach ($response['country'] as $country){
            ?>
            <option value="<?=$country['countryCode']?>"><?=$country['countryName']?></option>
            <?php
        }
        ?>
        </select>    
        <div class="kr-i-msg-f-kr_usr_country"><span></span></div>
    <?php
    }
    ?>
    <input type="text" name="kr_usr_phone_number" placeholder="<?php echo $Lang->tr('Your phone number'); ?>" value="">
    <div class="kr-i-msg-f-kr_usr_phone_number"><span></span></div>
    
    <?php if($App->_captchaSignup()): ?>
    <div class="g-recaptcha login-captcha" data-sitekey="<?php echo $App->_getGoogleRecaptchaSiteKey(); ?>"></div>
    <?php endif; ?>
    
    <div class="kr-signup-check">
      <input type="checkbox" name="kr_usr_agree" id="kr_usr_agree">
      <label for="kr_usr_agree">I agree to the <i onclick="loadTermsPage('term_use');return false;">privacy policy</i></label>
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
