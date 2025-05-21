<?php

/**
 * Admin dashboard page
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

// Load app modules
$App = new App(true);
$App->_loadModulesControllers();

// Check loggin & permission
$User = new User();
if(!$User->_isLogged()) throw new Exception("User are not logged", 1);
if(!$User->_isAdmin()) throw new Exception("Permission denied", 1);

// Init language object
$Lang = new Lang($User->_getLang(), $App);

// Init admin object
$Admin = new Admin();

?>
<section class="kr-admin">
  <nav class="kr-admin-nav">
    <ul>
      <?php foreach ($Admin->_getListSection() as $key => $section) { // Get list admin section
        echo '<li type="module" kr-module="admin" kr-view="'.strtolower(str_replace(' ', '', $section)).'" '.($section == 'Dashboard' ? 'class="kr-admin-nav-selected"' : '').'>'.$Lang->tr($section).'</li>';
      } ?>
    </ul>
  </nav>
  <div class="kr-admin-line">
    <?php
    foreach ($Admin->_getListBlockStats() as $blockStat) { // Get list block stats dashboard
      ?>
      <div class="kr-admin-b-stats">
        <span><?php echo $Lang->tr($blockStat['title']); ?></span>
        <div>
          <span class="kr-mono"><?php echo $blockStat['value']; ?></span>
        </div>
      </div>
      <?php
    }
    ?>
  </div>
  <h3><?php echo $Lang->tr('Last users'); ?></h3>
  <div class="kr-admin-table">
    <table>
      <thead>
        <tr>
          <td><?php echo $Lang->tr('Name'); ?></td>
          <td><?php echo $Lang->tr('Email'); ?></td>
          <?php if($App->_subscriptionEnabled()): ?>
            <td><?php echo $Lang->tr('Subscription'); ?></td>
          <?php endif; ?>
          <td><?php echo $Lang->tr('Signin method'); ?></td>
          <td><?php echo $Lang->tr('Last login'); ?></td>
          <td><?php echo $Lang->tr('Notifications enabled'); ?></td>
          <td><?php echo $Lang->tr('Currency'); ?></td>
          <td></td>
        </tr>
      </thead>
      
    </table>
  </div>
</section>
