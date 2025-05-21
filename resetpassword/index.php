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

try{
    $requestUriData = explode("/",$_SERVER['REQUEST_URI']);
    if(isset($requestUriData[1]) && $requestUriData[1] == 'resetpassword' && isset($requestUriData[2])){
        $User = new User();
        $decodeData = base64_decode(str_replace(array('-', '_'), array('+', '/'), $requestUriData[2]));
        if($decodeData != ''){
            $explodeDecodeData = explode('#_#', $decodeData);
            $userId = $explodeDecodeData[0];
            $userEmail = $explodeDecodeData[1];
            
            $generateResetToken = $User->_generateUserResetToken($userEmail, $requestUriData[2]);
            $redirectUrl = APP_URL . '/login.php?a=pwdr&token=' . base64_encode(App::encrypt_decrypt('encrypt', $userEmail . '||--||' . $generateResetToken));
            header('Location: '. $redirectUrl);
            exit;
        }
    }
} catch (Exception $ex){
    // echo $ex->getMessage();
    header('Location: '.APP_URL . '/login.php');
}
header('Location: '.APP_URL . '/login.php');

?>