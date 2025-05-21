<?php

/**
 * Change user infos action
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
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/Leads8Api/LeadsApi.php";

$json = file_get_contents('php://input');
$_POST = json_decode($json, true);
//Something to write to txt log
//$log  = print_r($_POST, true);
//Save string to log, use FILE_APPEND to append.
//file_put_contents($_SERVER['DOCUMENT_ROOT'].FILE_PATH.'/logs/user_'.date("j.n.Y").'.log', $log, FILE_APPEND);

try {
    
    if(empty($_POST)) throw new Exception("Error : Missing parameters", 1);
    
    // Load app modules
    $App = new App(true);
    $User = new User();
    $App->_loadModulesControllers();
    
    $action = (isset($_POST['action']) && $_POST['action'] != '') ? $_POST['action'] : null;
    $leadsId = (isset($_POST['id']) && $_POST['id'] > 0) ? $_POST['id'] : null;
    $email = (isset($_POST['email']) && $_POST['email'] != '') ? $_POST['email'] : null;
    $name = (isset($_POST['first_name']) && $_POST['first_name'] != '') ? $_POST['first_name'].' '.$_POST['last_name'] : null;
    $phoneNumber = (isset($_POST['phone_number']) && $_POST['phone_number'] != '') ? $_POST['phone_number'] : '';
    $countryUser = (isset($_POST['country_code']) && $_POST['country_code'] != '') ? strtoupper($_POST['country_code']) : null;
    
    if($_POST['action'] == 'delete') {
        $userData = MySQL::querySqlRequest("SELECT * FROM user_krypto WHERE id_leads=:id_leads",
                                [
                                  'id_leads' => $leadsId
                                ]);
        if(!empty($userData)){
            $idUser = $userData[0]['id_user'];
            if($idUser > 0){
                $tableList = [
                    'balance_krypto' => 'id_user',
                    'banktransfert_krypto' => 'id_user',
                    'banktransfert_proof_krypto' => 'id_user',
                    'blocked_user_chat_krypto' => 'id_user',
                    'blockfolio_krypto' => 'id_user',
                    'blockonomics_address_krypto' => 'id_user',
                    'blockonomics_transactions_krypto' => 'id_user',
                    'charges_krypto' => 'id_user',
                    'converter_krypto' => 'id_user',
                    'dashboard_krypto' => 'id_user',
                    'deposit_history_krypto' => 'id_user',
                    'deposit_history_proof_krypto' => 'id_user',
                    'googletfs_krypto' => 'id_user',
                    'graph_krypto' => 'id_user',
                    'holding_krypto' => 'id_user',
                    'identity_asset_krypto' => 'id_user',
                    'identity_krypto' => 'id_user',
                    'internal_order_krypto' => 'id_user',
                    'leader_board_krypto' => 'id_user',
                    'msg_room_chat_krypto' => 'id_user',
                    'notification_center_krypto' => 'id_user',
                    'notification_krypto' => 'id_user',
                    'order_krypto' => 'id_user',
                    'referal_histo_krypto' => 'id_user',
                    'referal_krypto' => 'id_user',
                    'top_list_krypto' => 'id_user',
                    'user_intro_krypto' => 'id_user',
                    'user_login_history_krypto' => 'id_user',
                    'user_newspopup' => 'id_user',
                    'user_room_chat_krypto' => 'id_user',
                    'user_settings_krypto' => 'id_user',
                    'user_status_krypto' => 'id_user',
                    'user_thirdparty_selected_krypto' => 'id_user',
                    'user_widthdraw_krypto' => 'id_user',
                    'visits_krypto' => 'id_user',
                    'watching_krypto' => 'id_user',
                    'widthdraw_history_krypto' => 'id_user',
                    'user_krypto' => 'id_user'
                ];

                foreach ($tableList as $key => $value) {
                    $r = MySQL::execSqlRequest("DELETE FROM " . $key . " WHERE " . $value . "=:id_user", ['id_user' => $idUser]);
                    if (!$r)
                        error_log('Fail to delete user informations : table : ' . $key);
                }
                die(json_encode([
                    'error' => 0,
                    'msg' => 'User deleted'
                ]));
            }
            throw new Exception("Error : Something went wrongs.", 1);
        } else {
            throw new Exception("Error : User not found.", 1);
        }
    }
    
    // Check args given
    if (is_null($action))
        throw new Exception("Error : Please enter action", 1);
    if (is_null($leadsId))
        throw new Exception("Error : User unique id required", 1);
    if (is_null($email))
        throw new Exception("Error : User email required", 1);
    if (is_null($name))
        throw new Exception("Error : User name required", 1);
    if (is_null($phoneNumber))
        throw new Exception("Error : User phone number required", 1);
    if (is_null($countryUser))
        throw new Exception("Error : User country required", 1);
    // Check email validity
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))
        throw new Exception("Error : User create, email wrong format", 1);
    
    $oauth = 'standard';
    $picture = '';
    $pushbullet = '';
    $twostep = 0;
    $admin = 0;
    $setpwd = false;
    
    if($_POST['action'] == 'insert'){
        $userData = MySQL::querySqlRequest("SELECT * FROM user_krypto WHERE email_user=:email_user",
                                [
                                  'email_user' => $email
                                ]);
        if(isset($userData[0]) && !empty($userData[0])){
            throw new Exception("Error : User already exit.", 1);
        }
        
        if(isset($_POST['password']) && $_POST['password'] != ''){
            $password = $_POST['password'];
        } else {
            $password = '123456';
        }
        
        
        // Add user to database
        $r = MySQL::execSqlRequest("INSERT INTO user_krypto (id_leads, email_user, name_user, phone_user, country_user, password_user, picture_user,
                                                          oauth_user, pushbullet_user, twostep_user, created_date_user, admin_user, status_user) VALUES
                                                          (:id_leads ,:email_user, :name_user, :phone_user, :country_user, :password_user, :picture_user,
                                                          :oauth_user, :pushbullet_user, :twostep_user, :created_date_user, :admin_user, :status_user)", [
                    'id_leads' => $leadsId,
                    'email_user' => $email,
                    'name_user' => $name,
                    'phone_user' => $phoneNumber,
                    'country_user' => $countryUser,
                    'password_user' => ($oauth == 'standard' ? hash('sha512', $password) : ($setpwd ? $password : $oauth)),
                    'picture_user' => $picture,
                    'oauth_user' => $oauth,
                    'pushbullet_user' => $pushbullet,
                    'twostep_user' => $twostep,
                    'created_date_user' => time(),
                    'admin_user' => $admin,
                    'status_user' => ($App->_getUserActivationRequire() && $oauth == 'standard' ? '2' : '1')
        ]);

        // Check if sql add database status
        if (!$r)
            throw new Exception("Error : User fail to create account", 1);

        die(json_encode([
          'error' => 0,
          'msg' => 'User created'
        ]));
    } else if($_POST['action'] == 'update'){
        $updateDataArr = [
            'email_user' => $email,
            'name_user' => $name,
            'phone_user' => $phoneNumber,
            'country_user' => $countryUser,
            'id_leads' => $leadsId
        ];
        if(isset($_POST['password']) && $_POST['password'] != ''){
            $password = $_POST['password'];
            $updateDataArr['password_user'] = ($oauth == 'standard' ? hash('sha512', $password) : ($setpwd ? $password : $oauth));
            
            $r = MySQL::execSqlRequest("UPDATE user_krypto SET email_user=:email_user, name_user=:name_user, phone_user=:phone_user, country_user=:country_user, password_user=:password_user WHERE id_leads=:id_leads", $updateDataArr);
        } else {
            $r = MySQL::execSqlRequest("UPDATE user_krypto SET email_user=:email_user, name_user=:name_user, phone_user=:phone_user, country_user=:country_user WHERE id_leads=:id_leads", $updateDataArr);
        }
        if (!$r)
            throw new Exception("Error : Fail to change user infos.", 1);
        die(json_encode([
          'error' => 0,
          'msg' => 'User updated'
        ]));
    } else {
        die(json_encode([
            'error' => 1,
            'msg' => 'Please enter valid action'
        ]));
    }
} catch (Exception $e) {
    die(json_encode([
    'error' => 1,
    'msg' => $e->getMessage()
  ]));
}
