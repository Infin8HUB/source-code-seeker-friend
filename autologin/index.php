<?php
/**
 * Index app
 *
 * @package Krypto
 * @author Ovrley <hello@ovrley.com>
 */

session_start();

require "../config/config.settings.php";
require "../vendor/autoload.php";
require "../app/src/MySQL/MySQL.php";
require "../app/src/App/App.php";
require "../app/src/App/AppModule.php";
require "../app/src/User/User.php";
require "../app/src/Lang/Lang.php";
require "../app/src/Leads8Api/LeadsApi.php";
// Load modules & check domain
$App = new App(true);
$App->_checkDomain();
$App->_loadModulesControllers();

$autoLoginData = explode("/",$_SERVER['REQUEST_URI']);
if(isset($autoLoginData[1]) && $autoLoginData[1] == 'autologin' && isset($autoLoginData[2]) && isset($autoLoginData[3])){
    $User = new User();
    
    $isLogin = $User->doAutoLogin($autoLoginData[2], $autoLoginData[3]);
    
    header('Location: '.APP_URL.'/dashboard'.($App->_rewriteDashBoardName() ? '' : '.php'));
}else if(isset($autoLoginData[1]) && $autoLoginData[1] == 'autologin' && isset($autoLoginData[2])){
    $userData = explode("#_#", base64_decode($autoLoginData[2]));
    if(isset($userData[0]) && $userData[0] > 0 && isset($userData[1]) && !empty($userData[1])){
    	$leadsUserId = $userData[0]; 
    	$leadsUserEmail = $userData[1]; 
	    $User = new User();
	    $isLogin = $User->doMailUserAutoLogin($leadsUserId,$leadsUserEmail);
	    if($isLogin){	    
	    	header('Location: '.APP_URL.'/dashboard'.($App->_rewriteDashBoardName() ? '' : '.php'));
		}
	}
}
?>