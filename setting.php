<?php

class ControllerSetting extends Controller {

    public $arrMethods = array('general', 'delete', 'kenosetting', 'sfgame', 'slotmachine', 'highorlow', 'baccarat', 
        'countryGameSetting', 'language', 'platformsmtp'); // for platform admin //
    public $brandArrMethod = array('delete', 'Twcall', 'payment', 'smtp', 'countrypayment', 'mailchimp', 'ip_limit', 'checkConnection',
        'ajaxDepositBonusRemove', 'postback', 'bonus', 'column', 'blocked_ip', 'call_api', 'makeApi'); /// for brand admin //

    public function __construct() {
        parent::__construct();
        $this->arrPaths = config::req('paths');

        if (isset($this->arrPaths[0]) && $this->arrPaths[0] == config::sys('admin')) {
            if (isset($this->arrPaths[2]) && !empty($this->arrPaths[2]) && in_array($this->arrPaths[2], $this->arrMethods)) {
                $method = $this->arrPaths[2];
                $this->$method();
            } else {
                $this->setting();
            }
        } else {
            if (isset($this->arrPaths[3]) && !empty($this->arrPaths[3]) && in_array($this->arrPaths[3], $this->brandArrMethod)) {
                $method = $this->arrPaths[3];
                $this->$method();
            } else {
                $this->brand_setting();
            }
        }
    }

    public function setting() {
        $message = '';
        if (isset($_POST['paypal_setting'])) {

            unset($_POST['paypal_setting']);
            $isSave = Setting::savePaypalSetting($_POST);
            if ($isSave) {
                output::redirect(config::admin_url() . 'setting/?msg=success');
            } else {
                $message = Html::site_notification('Setting not update successfully', 'fail');
            }
        }
        
        if (isset($_POST['paypal_form_setting'])) {

            unset($_POST['paypal_form_setting']);
            $isSave = Setting::savePaypalFormSetting($_POST);
            if ($isSave) {
                output::redirect(config::admin_url() . 'setting/?msg=success');
            } else {
                $message = Html::site_notification('Setting not update successfully', 'fail');
            }
        }

        if (isset($_POST['stripe_setting'])) {
            unset($_POST['stripe_setting']);
            $isSave = Setting::saveStripeSetting($_POST);
            if ($isSave) {
                output::redirect(config::admin_url() . 'setting/?msg=success');
            } else {
                $message = Html::site_notification('Setting not update successfully', 'fail');
            }
        }

        if (isset($_POST['bitcoin_setting'])) {
            unset($_POST['bitcoin_setting']);
            $isSave = Setting::saveBitcoinSetting($_POST);
            if ($isSave) {
                output::redirect(config::admin_url() . 'setting/?msg=success');
            } else {
                $message = Html::site_notification('Setting not update successfully', 'fail');
            }
        }

        if (isset($_POST['coingate_setting'])) {
            unset($_POST['coingate_setting']);
            $isSave = Setting::saveCoingateSetting($_POST);
            if ($isSave) {
                output::redirect(config::admin_url() . 'setting/?msg=success');
            } else {
                $message = Html::site_notification('Setting not update successfully', 'fail');
            }
        }

        if (isset($_POST['triplea_setting'])) {
            unset($_POST['triplea_setting']);
            $isSave = Setting::saveTripleaSetting($_POST);
            if ($isSave) {
                output::redirect(config::admin_url() . 'setting/?msg=success');
            } else {
                $message = Html::site_notification('Setting not update successfully', 'fail');
            }
        }
        if (isset($_POST['coinify_setting'])) {
            unset($_POST['coinify_setting']);
            $isSave = Setting::saveCoinifySetting($_POST);
            if ($isSave) {
                output::redirect(config::admin_url() . 'setting/?msg=success');
            } else {
                $message = Html::site_notification('Setting not update successfully', 'fail');
            }
        }
        
        if (isset($_POST['edit_setting'])) {
            $update = Setting::updatePaymentMethodSetting($_POST);
            if ($update) {
                output::redirect(config::admin_url() . 'setting/?msg=success');
            } else {
                $message = Html::site_notification('Setting not update successfully', 'fail');
            }
        }
        if (isset($_REQUEST['msg']) && $_REQUEST['msg'] == 'success') {
            $message = Html::site_notification('successfully update', 'Success');
        }
        // $page = Pagination::getpage();
        // $setLimit = Pagination::setlimit();
        // $pageLimit = Pagination::getpagelimit($setLimit);


//        $settingArr = Setting::getPaymentMethodSetting($pageLimit, $setLimit);
//        $count = Setting::getPaymentMethodSettingCount();

        $paypalSetting = Setting::getPFPaypalSetting();

        foreach ($paypalSetting as $key => $value) {
            $paypalSetting[$key] = trim($value);
        }

        $stripeSetting = Setting::getPFStripeSetting();
        $notStandradDisplay = '';
        $standradDisplay = '';
        $standradChecked = '';
        if (isset($paypalSetting['is_standrad']) && $paypalSetting['is_standrad'] == 1) {
            $notStandradDisplay = 'display:none';
            $standradChecked = 'checked';
        } else {
            $standradDisplay = 'display:none';
        }
        
        $paypalEnabledCheckbox = '';
        if (isset($paypalSetting['paypal_enable']) && $paypalSetting['paypal_enable'] == 1) {
            $paypalEnabledCheckbox = 'checked';
        }
        
        $paypal_sandbox = '';
        $paypal_live = '';
        if (!empty($paypalSetting)) {
            if ($paypalSetting['type'] == 0) {
                $paypal_sandbox = 'selected';
            }
            if ($paypalSetting['type'] == 1) {
                $paypal_live = 'selected';
            }
        }
        
        $stripeEnabledCheckbox = '';
        if (isset($stripeSetting['stripe_enable']) && $stripeSetting['stripe_enable'] == 1) {
            $stripeEnabledCheckbox = 'checked';
        }
        
        $bitcoinSetting = Setting::getPFBitcoinSetting();
        $bitcoinEnabledCheckbox = '';
        if (isset($bitcoinSetting['bitcoin_enable']) && $bitcoinSetting['bitcoin_enable'] == 1) {
            $bitcoinEnabledCheckbox = 'checked';
        }
        
        $paypalFormSetting = Setting::getPFPaypalFormSetting();

        foreach ($paypalFormSetting as $key => $value) {
            $paypalFormSetting[$key] = trim($value);
        }

        $paypalFormEnabledCheckbox = '';
        if (isset($paypalFormSetting['paypal_form_enable']) && $paypalFormSetting['paypal_form_enable'] == 1) {
            $paypalFormEnabledCheckbox = 'checked';
        }
        
        $paypal_form_sandbox = '';
        $paypal_form_live = '';
        if (!empty($paypalFormSetting)) {
            if ($paypalFormSetting['form_type'] == 0) {
                $paypal_form_sandbox = 'selected';
            }
            if ($paypalFormSetting['form_type'] == 1) {
                $paypal_form_live = 'selected';
            }
        }

         // For coingate//
         $coingateSetting = Setting::getPFCoingateSetting();

         if(!empty($coingateSetting)){
             foreach ($coingateSetting as $key => $value) {
                 $coingateSetting[$key] = trim($value);
             }
         }
 
         $coingateEnabledCheckbox = '';
         if (isset($coingateSetting['coingate_enable']) && $coingateSetting['coingate_enable'] == 1) {
             $coingateEnabledCheckbox = 'checked';
         }
         
         $coingate_sandbox = '';
         $coingate_live = '';
         if (!empty($coingateSetting)) {
             if ($coingateSetting['form_type'] == 0) {
                 $coingate_sandbox = 'selected';
             }
             if ($coingateSetting['form_type'] == 1) {
                 $coingate_live = 'selected';
             }
         }

         // For triple-a //
         $tripleaSetting = Setting::getPFTripleaSetting();
         
         if(!empty($tripleaSetting)){
             foreach ($tripleaSetting as $key => $value) {
                 $tripleaSetting[$key] = trim($value);
             }
         }
 
         $tripleaEnabledCheckbox = '';
         if (isset($tripleaSetting['triplea_enable']) && $tripleaSetting['triplea_enable'] == 1) {
             $tripleaEnabledCheckbox = 'checked';
         }
         
         $triplea_sandbox = '';
         $triplea_live = '';
         if (!empty($tripleaSetting)) {
             if ($tripleaSetting['form_type'] == 0) {
                 $triplea_sandbox = 'selected';
             }
             if ($tripleaSetting['form_type'] == 1) {
                 $triplea_live = 'selected';
             }
         }

         // For coinify //
         $coinifySetting = Setting::getPFCoinifySetting();
         
         if(!empty($coinifySetting)){
             foreach ($coinifySetting as $key => $value) {
                 $coinifySetting[$key] = trim($value);
             }
         }
 
         $coinifyEnabledCheckbox = '';
         if (isset($coinifySetting['coinify_enable']) && $coinifySetting['coinify_enable'] == 1) {
             $coinifyEnabledCheckbox = 'checked';
         }
         
         $coinify_sandbox = '';
         $coinify_live = '';
         if (!empty($coinifySetting)) {
             if ($coinifySetting['form_type'] == 0) {
                 $coinify_sandbox = 'selected';
             }
             if ($coinifySetting['form_type'] == 1) {
                 $coinify_live = 'selected';
             }
         }

//        $settingRowHtml = '';
//        if (!empty($settingArr)) {
//            foreach ($settingArr as $setting) {
//                $settingRowHtml .= '<tr>';
//                $settingRowHtml .= '<td>' . $setting['payment_type'] . '<input type="hidden" value="' . $setting['uid'] . '" name="uid[]"></td>';
//                $checked_active = ($setting['is_active'] == 0) ? 'checked="checked"' : '';
//                $settingRowHtml .= '<td><input type="checkbox" class="icheck status_check" id="' . $setting['uid'] . '" ' . $checked_active . ' name="active[' . $setting['uid'] . ']"></td>';
//                $checked = ($setting['status'] == 1) ? 'checked="checked"' : '';
//                $disabled_limit = ($setting['status'] == 1) ? 'disabled="disabled"' : '';
//                $settingRowHtml .= '<td><input type="checkbox" class="icheck method_status_check" id="' . $setting['uid'] . '" ' . $checked . ' name="status[' . $setting['uid'] . ']">Unlimited</td>';
//                $settingRowHtml .= '<td><input type="number" ' . $disabled_limit . ' id="method_limit_' . $setting['uid'] . '" class="form-control" name="limit[' . $setting['uid'] . ']" value="' . $setting['limit'] . '"></td>';
//                $settingRowHtml .= '</tr>';
//            }
//        }

        $body = make::tpl('admin/setting_payment_method')
                ->assign($paypalSetting)
                ->assign($stripeSetting)
                ->assign($bitcoinSetting)
                ->assign($paypalFormSetting)
                ->assign($coingateSetting)
                ->assign($tripleaSetting)
                ->assign($coinifySetting)
                ->assign(array(
                    'notStandradDisplay' => $notStandradDisplay,
                    'standradDisplay' => $standradDisplay,
                    'standradChecked' => $standradChecked,
        //            'settingRowHtml' => $settingRowHtml,
                    'error_message' => $message,
                    // 'Pagination' => $Pagination,
                    'paypal_live' => $paypal_live,
                    'paypal_sandbox' => $paypal_sandbox,
                    'paypalEnabledCheckbox' => $paypalEnabledCheckbox,
                    'stripeEnabledCheckbox' => $stripeEnabledCheckbox,
                    'bitcoinEnabledCheckbox' => $bitcoinEnabledCheckbox,
                    'paypal_form_live' => $paypal_form_live,
                    'paypal_form_sandbox' => $paypal_form_sandbox,
                    'paypalFormEnabledCheckbox' => $paypalFormEnabledCheckbox,                    
                    'coingate_live' => $coingate_live,
                    'coingate_sandbox' => $coingate_sandbox,
                    'coingateEnabledCheckbox' => $coingateEnabledCheckbox,
                    'triplea_live' => $triplea_live,
                    'triplea_sandbox' => $triplea_sandbox,
                    'tripleaEnabledCheckbox' => $tripleaEnabledCheckbox,
                    'coinify_live' => $coinify_live,
                    'coinify_sandbox' => $coinify_sandbox,
                    'coinifyEnabledCheckbox' => $coinifyEnabledCheckbox,
                ));

        $active = array(
            'active' => 'setting',
            'active_li' => 'set_sub_li',
        );

        $tplSkeleton = make::tpl('admin/index')->assign($active)->assign(array(
                    'body' => $body,
                    'meta_title' => 'Leads8 | Admin panel | Setting',
                    'meta_keywords' => 'Leads8',
                    'meta_description' => 'Leads8',
                ))->get_content();


        output::as_html($tplSkeleton);
    }

    // function for general setting of platform admin//
    public function general() {
        $body = '';
        $postData = array();

        if (isset($_POST['general_setting'])) {
            
            if (count($arrErr = Setting::isValidGeneralSetting($_POST)) > 0) {
                // getting error//
                $postData = $_POST;
                $error_message = '<div class="alert alert-danger">';
                $error_message .= implode("<br>", $arrErr);
                $error_message .= '</div>';
                $postData['error_message'] = $error_message;
            } else {
                // save setting //
                $isSave = Setting::setPlatformSetting($_POST);
                if ($isSave) {
                    $id = $_POST['cryptoUrl'];
                    Ecom_api::saveCryptoSiteApiUrl($id);
                    Html::set_notification('Successfully saved', 'success');
                    output::redirect(config::admin_url('setting/general'));
                    exit;
                } else {
                    $postData = $_POST;
                    Html::set_notification('Successfully not saved', 'error');
                }
            }
        }

        $error_message = Html::get_notification();
        $commissionSettingArr = Setting::getPlatformCommission();
        $timerSettingArr = Setting::getPlatformStatusTimer();
        $creditLimitSettingArr = Setting::getPlatformBussinessCreditLimit();
        $minimumCreditLimitSettingArr = Setting::getPlatformBussinessMinimumCreditLimit();
        $automatedCallServiceChargeArr = Setting::getPlatformSettingByKey('automated_call_service_charge');
        $automatedCallPaymentLimitArr = Setting::getPlatformSettingByKey('automated_call_minimum_payment');
        $cryptoUrlHtml = '';
        $cryptoUrlData = Ecom_api::getAllCryptoSiteApiUrl();
        if(!empty($cryptoUrlData)){
            foreach ($cryptoUrlData as $cryptoUrl){
                $cryptoUrlHtml.='<div class="row">';
                $cryptoUrlHtml.='<div class="col-md-3 text-center">';
                $checked = $cryptoUrl['status'] == '1' ? "checked" : "";
                $cryptoUrlHtml.='<input type="radio" name="cryptoUrl" '.$checked.' value="'.$cryptoUrl['id'].'" />';
                $cryptoUrlHtml.='</div>';
                $cryptoUrlHtml.='<div class="col-md-6">'.$cryptoUrl['api_url'].'</div>';
                $cryptoUrlHtml.='<div class="col-md-3 text-center">'.$cryptoUrl['enable_date'].'</div>';
                $cryptoUrlHtml.='</div>';
            }
        }

        $body = make::tpl('admin/general_setting')
                ->assign($postData)
                ->assign(array(
            'error_message' => $error_message,
            'commission' => (isset($commissionSettingArr['option_value'])) ? $commissionSettingArr['option_value'] : '',
            'commission_uid' => (isset($commissionSettingArr['uid'])) ? $commissionSettingArr['uid'] : '',
            'status_timer' => (isset($timerSettingArr['option_value'])) ? $timerSettingArr['option_value'] : '',
            'status_timer_uid' => (isset($timerSettingArr['uid'])) ? $timerSettingArr['uid'] : '',
            'bussiness_credit_limit_uid' => (isset($creditLimitSettingArr['uid'])) ? $creditLimitSettingArr['uid'] : '',
            'bussiness_credit_limit' => (isset($creditLimitSettingArr['option_value'])) ? $creditLimitSettingArr['option_value'] : '',
            'bussiness_minimum_credit_limit_uid' => (isset($minimumCreditLimitSettingArr['uid'])) ? $minimumCreditLimitSettingArr['uid'] : '',
            'bussiness_minimum_credit_limit' => (isset($minimumCreditLimitSettingArr['option_value'])) ? $minimumCreditLimitSettingArr['option_value'] : '',
            
            'automated_call_service_charge' => (isset($automatedCallServiceChargeArr['option_value'])) ? $automatedCallServiceChargeArr['option_value'] : '',
            'automated_call_service_charge_uid' => (isset($automatedCallServiceChargeArr['uid'])) ? $automatedCallServiceChargeArr['uid'] : '',
            'automated_call_minimum_payment' => (isset($automatedCallPaymentLimitArr['option_value'])) ? $automatedCallPaymentLimitArr['option_value'] : '',
            'automated_call_minimum_payment_uid' => (isset($automatedCallPaymentLimitArr['uid'])) ? $automatedCallPaymentLimitArr['uid'] : '',
            'cryptoUrlHtml' => $cryptoUrlHtml,
        ));

        $active = array(
            'active' => 'setting',
            'active_li' => 'set_gen_sub_li',
        );
        $tplSkeleton = make::tpl('admin/index')->assign($active)->assign(array(
                    'body' => $body,
                    'meta_title' => 'Leads8 | Admin panel | Setting',
                    'meta_keywords' => 'Leads8',
                    'meta_description' => 'Leads8',
                ))->get_content();


        output::as_html($tplSkeleton);
    }

    public function brand_setting() {

        $brand = config::req('paths');
        $brand_admin_userid = $_SESSION[$brand[0] . '_admin_userid'];
        $module = $this->arrPaths[2];
//        $section = (isset($this->arrPaths[3])) ? $this->arrPaths[3] : 'edit';
        $brand_uri = config::url() . $this->arrPaths[0] . '/';
        $brnad_slug = $this->arrPaths[0];
        $brandId = Brands::GetBranduidByBrandslug($brnad_slug);
        $error_message = '';
        $permission_message = '';

        $timeDuartion = array(
            0 => 'Any',
            1 => 'First Time',
            2 => 'Second Time',
            3 => 'Third Time',
        );
        $bonusType = array(
            0 => '%',
            1 => '$',
        );
        $permission = array(
            0 => 'Only',
            1 => 'Together with other options',
        );

        if (isset($_POST['save_setting'])) {
//           echo "<pre>";           print_r($_POST); exit;
            $_POST['brand_uid'] = $brandId;
            $saveSetting = Currency::saveBrandWiseCurrencySetting($_POST);
            if ($saveSetting) {
                Html::set_notification('Currency has been successfully saved', 'success');
            } else {
                Html::set_notification('Currency has not been successfully saved', 'error');
            }
            output::redirect(Brand::getUrl('setting'));
            exit;
        }

        if (isset($_POST['save_deposit_bonus'])) {
            $isSaveDepositSetting = Setting::saveDepositBonusSetting($brandId, $_POST);
            if ($isSaveDepositSetting) {
                Html::set_notification('Deposit Bonus has been successfully saved', 'success');
            } else {
                Html::set_notification('Deposit Bonus has not been successfully saved', 'error');
            }
            output::redirect(Brand::getUrl('setting'));
            exit;
        }

        if (isset($_POST['save_reg_bonus'])) {
            //echo "<pre>"; print_r($_POST);exit;
            $bonus_update = Setting::saveNewRegBonusSetting($brandId, $_POST);
            if ($bonus_update) {
                Html::set_notification('Register Bonus has been successfully saved', 'success');
            } else {
                Html::set_notification('Register Bonus has not been successfully saved', 'error');
            }
            output::redirect(Brand::getUrl('setting'));
            exit;
        }

        if (isset($_POST['save_mass_assign'])) {
            if ($_POST['mass_assign_limit'] <= 0 || $_POST['mass_assign_limit'] > 1000) {
                Html::set_notification('Please enter limit between 1 to 1000', 'Error');
                output::redirect(Brand::getUrl('setting'));
                exit;
            } else {
                $massAssignId = (isset($_POST['mass_assign_limit_id']) && $_POST['mass_assign_limit_id'] != '') ? $_POST['mass_assign_limit_id'] : 0;
                $isSave = Setting::saveMassAssignLimitation($_POST['mass_assign_limit'], $brandId, $massAssignId);
                if ($isSave) {
                    Html::set_notification('Mass Assign limit successfully set.', 'Success');
                } else {
                    Html::set_notification('Mass Assign limit not successfully set', 'Error');
                }
                output::redirect(Brand::getUrl('setting'));
                exit;
            }
        }
        
        if (isset($_POST['save_mass_email'])) {
            if ($_POST['mass_email_limit'] <= 0 || $_POST['mass_email_limit'] > 500) {
                Html::set_notification('Please enter limit between 1 to 500', 'Error');
                output::redirect(Brand::getUrl('setting'));
                exit;
            } else {
                $massEmailId = (isset($_POST['mass_email_limit_id']) && $_POST['mass_email_limit_id'] != '') ? $_POST['mass_email_limit_id'] : 0;
                $isSave = Setting::saveMassEmailLimitation($_POST['mass_email_limit'], $brandId, $massEmailId);
                if ($isSave) {
                    Html::set_notification('Mass Email limit successfully set.', 'Success');
                } else {
                    Html::set_notification('Mass Email limit not successfully set', 'Error');
                }
                output::redirect(Brand::getUrl('setting'));
                exit;
            }
        }
        // Automated call upload limit  //
        if (isset($_POST['save_automated_call_upload_limit'])) {
            if ($_POST['automated_call_upload_limit'] <= 0) {
                Html::set_notification('Please enter valid limit', 'Error');
                output::redirect(Brand::getUrl('setting'));
                exit;
            } else {
                $automatedUploadLimitId = (isset($_POST['automated_call_upload_limit_id']) && $_POST['automated_call_upload_limit_id'] != '') ? $_POST['automated_call_upload_limit_id'] : 0;
                $isSave = Setting::saveAutomatedCallUploadLimitation($_POST['automated_call_upload_limit'], $brandId, $automatedUploadLimitId);
                if ($isSave) {
                    Html::set_notification('Automated call upload limit successfully set.', 'Success');
                } else {
                    Html::set_notification('Automated call upload limit not successfully set', 'Error');
                }
                output::redirect(Brand::getUrl('setting'));
                exit;
            }
        }

        // save default manager assign  //
        if (isset($_POST['save_default_assgn_manager'])) {
            $isSave = Setting::saveDefaultAssingManager($brandId, $_POST);
            if ($isSave) {
                Html::set_notification('Defult Assign Manager successfully set.', 'Success');
            } else {
                Html::set_notification('Defult Assign Manager not successfully set', 'Error');
            }
            output::redirect(Brand::getUrl('setting'));
            exit;
        }

        // save duplicate email allowed setting //
        if (isset($_POST['save_duplicate_email_allow_setting'])) {
            $isSave = Setting::saveDuplicateEmailAllowedSetting($brandId, $_POST);
            if ($isSave) {
                Html::set_notification('Duplicate email allowed setting successfully set.', 'Success');
            } else {
                Html::set_notification('Duplicate email allowed setting successfully set', 'Error');
            }
            output::redirect(Brand::getUrl('setting'));
            exit;
        }
        
        // save per page records setting //
        if (isset($_POST['save_per_page_records_setting'])) {
            $isSave = Setting::savePerPageRecordsSetting($brandId, $_POST);
            if ($isSave) {
                Html::set_notification('Per Page Records setting successfully set.', 'Success');
            } else {
                Html::set_notification('Per Page Records setting successfully set', 'Error');
            }
            output::redirect(Brand::getUrl('setting'));
            exit;
        }
        
        // save per page records setting //
        if (isset($_POST['save_iframe_token_setting'])) {
            $isSave = Setting::saveIframeTokenSetting($brandId, $_POST);
            if ($isSave) {
                Html::set_notification('Token setting successfully set.', 'Success');
            } else {
                Html::set_notification('Token setting successfully set', 'Error');
            }
            output::redirect(Brand::getUrl('setting'));
            exit;
        }
        
        // save per page records setting //
        if (isset($_POST['save_affil_get_money_uid_setting'])) {
            $isSave = Setting::saveAffilGetMoneySetting($brandId, $_POST);
            if ($isSave) {
                Html::set_notification('Affiliate Get Money setting successfully set.', 'Success');
            } else {
                Html::set_notification('Affiliate Get Money setting successfully set', 'Error');
            }
            output::redirect(Brand::getUrl('setting'));
            exit;
        }

        // save default click revenue //
        if (isset($_POST['save_default_click_rev'])) {
            if ($_POST['default_banner_click_max_rev'] < $_POST['default_banner_click_min_rev']) {
                Html::set_notification('Max value must be greater than Min value', 'Error');
                output::redirect(Brand::getUrl('setting'));
                exit;
            } else {
                $isSave = Setting::saveBannerClickRevenue($brandId, $_POST);
                if ($isSave) {
                    $params = array(
                        'brand_uid' => $brandId,
                        'manager_uid' => $brand_admin_userid,
                        'min' => $_POST['default_banner_click_min_rev'],
                        'max' => $_POST['default_banner_click_max_rev']
                    );
                    Products::addRatioChangeLog($params);
                    Html::set_notification('Defult banner click revenue successfully set.', 'Success');
                } else {
                    Html::set_notification('Defult banner click revenue not successfully set', 'Error');
                }
                output::redirect(Brand::getUrl('setting'));
                exit;
            }
        }

        if(isset($_POST['save_domain_url'])){
            if(isset($_POST['domain_name']) && $_POST['domain_name'] == ''){
                Html::set_notification('Please enter domain url', 'Error');
                output::redirect(Brand::getUrl('setting'));
                exit;
            } else {                
                $isSave = Setting::saveDomainUrl($brandId, $_POST);
                if($isSave){
                    Html::set_notification('Domain url successfully saved.', 'Success');
                } else {
                    Html::set_notification('Domain url not successfully set', 'Error');
                }
                output::redirect(Brand::getUrl('setting'));
                exit;
            }
        }

        if(isset($_POST['save_timezone'])){
            $isSave = Setting::saveBrandTimeZone($brandId, $_POST);
            if($isSave){
                Html::set_notification('Timezone successfully saved.', 'Success');
            } else {
                Html::set_notification('Timezone not successfully set', 'Error');
            }
            output::redirect(Brand::getUrl('setting'));
            exit;
        }

        // save new user register main send to main manager setting //
        if (isset($_POST['save_new_user_register_mail_to_main_manager_setting'])) {
            $isSave = Setting::saveNewUserNotificationToMainManagerSetting($brandId, $_POST);
            if ($isSave) {
                Html::set_notification('New user register notification mail send to main manager setting successfully set.', 'Success');
            } else {
                Html::set_notification('New user register notification mail send to main manager setting successfully set', 'Error');
            }
            output::redirect(Brand::getUrl('setting'));
            exit;
        }

        // save per page records setting //
        if (isset($_POST['save_ip_store'])) {
            $isSave = Setting::saveIPStoreSetting($brandId, $_POST);
            if ($isSave) {
                Html::set_notification('IP setting successfully set.', 'Success');
            } else {
                Html::set_notification('IP setting successfully set', 'Error');
            }
            output::redirect(Brand::getUrl('setting'));
            exit;
        }

        $res = Manage_permission::check_permission_manager($brand_admin_userid, $module, 'general_setting');

        if (count($res) > 0) {

            // Start get deposit bonus setting data //
            $html = '';
            $depositBonusArr = Setting::getDepositBonusSetting($brandId);
            $regBonusArr = Setting::getRegBonusSetting($brandId);
            if (!empty($depositBonusArr)) {
                foreach ($depositBonusArr as $depSeting) {
                    $bonusTypeAmount = '';
                    $bonusTypePercentage = '';

                    if ($depSeting['bonus_type'] == 1) {
                        $bonusTypeAmount = $bonusType[$depSeting['bonus_type']];
                    } else {
                        $bonusTypePercentage = $bonusType[$depSeting['bonus_type']];
                    }

                    $html .= '<tr id="append_id' . $depSeting['uid'] . '">';
                    $html .= '<td>';
                    $html .= $depSeting['name'] . '<br>';
                    $html .= $permission[$depSeting['permission']];
                    $html .= '<input type="hidden" id="depid_id' . $depSeting['uid'] . '" value="' . $depSeting['uid'] . '" name="deposit_set_id[]"/>';
                    $html .= '<input type="hidden" id="name_id' . $depSeting['uid'] . '" value="' . $depSeting['name'] . '" name="setting_name[]"/>';
                    $html .= '<input type="hidden" id="permission_id' . $depSeting['uid'] . '" value="' . $depSeting['permission'] . '" name="permission[]"/>';
                    $html .= '</td>';
                    $html .= '<td>';
                    $html .= $timeDuartion[$depSeting['time']];
                    $html .= '<input type="hidden" id="time_id' . $depSeting['uid'] . '" value="' . $depSeting['time'] . '" name="time[]"/>';
                    $html .= '</td>';
                    $html .= '<td>';
                    $html .= $depSeting['min'] . '-' . $depSeting['max'];
                    $html .= '<input type="hidden" id="min_id' . $depSeting['uid'] . '" value="' . $depSeting['min'] . '" name="min_amount[]"/>';
                    $html .= '<input type="hidden" id="max_id' . $depSeting['uid'] . '" value="' . $depSeting['max'] . '" name="max_amount[]"/>';
                    $html .= '</td>';
                    $html .= '<td>';
                    $html .= $bonusTypeAmount . $depSeting['bonus_amount'] . $bonusTypePercentage . '<br>';
                    $html .= '<input type="hidden" id="btype_id' . $depSeting['uid'] . '" value="' . $depSeting['bonus_type'] . '" name="bonus_type[]"/>';
                    $html .= '<input type="hidden" id="bamount_id' . $depSeting['uid'] . '" value="' . $depSeting['bonus_amount'] . '" name="bonus_amount[]"/>';
                    $html .= '</td>';
                    $html .= '<td>';
                    $html .= '<a href="javascript::void(0);" rel="id' . $depSeting['uid'] . '" data-id="' . $depSeting['uid'] . '" class="edit-bonus">Edit</a>&nbsp;&nbsp;';
                    $html .= '<a href="javascript::void(0);" rel="id' . $depSeting['uid'] . '" data-id="' . $depSeting['uid'] . '" class="remove-bonus">Remove</a>';
                    $html .= '</td>';
                    $html .= '</tr>';
                }
            }
            // End get deposit bonus setting data //

            $currencySetting = Currency::getBrandwiseCurrencySetting($brandId);
            $selectedCurrecy = isset($currencySetting['currency_uid']) ? $currencySetting['currency_uid'] : 0;

            // get mass assign limit //
            $massAssignLimitArr = Setting::getBrandMassAssignLimitationSetting($brandId);
            $massAssignLimit = (isset($massAssignLimitArr['option_value'])) ? $massAssignLimitArr['option_value'] : '';
            $massAssignLimitUid = (isset($massAssignLimitArr['uid'])) ? $massAssignLimitArr['uid'] : '';

            $defaultManagerUid = 0;
            $defaultManagerSettingId = '';
            $defaultManagerSettingArr = Setting::getBrandDefaultAssignManagerSetting($brandId);

            if (!empty($defaultManagerSettingArr)) {
                $defaultManagerUid = $defaultManagerSettingArr['option_value'];
                $defaultManagerSettingId = $defaultManagerSettingArr['uid'];
            }

            $defaultClickRevSettingArr = Setting::getDefaultBannerClickRevenue($brandId);

            $duplicateEmailAllowedSettingArr = Setting::getDuplicateEmailAllowedSetting($brandId);
            $emailDupAllowed = '';
            if ($duplicateEmailAllowedSettingArr['duplicate_email_allowed']['value'] == 1) {
                $emailDupAllowed = "selected";
            }
            $emailExistAllowOptionHtml = '<option value="0">Not Allowed</option>';
            $emailExistAllowOptionHtml .= '<option value="1" ' . $emailDupAllowed . '>Allowed</option>';


            // new user notitfication mail send to main manager setting //
            $newUserNotificationToMainManagerSettingArr = Setting::getNewUserNotificationToMainManagerSetting($brandId);
            $sendNotificationAllowed = '';
            if ($newUserNotificationToMainManagerSettingArr['new_user_register_mail_to_main_manager']['value'] == 1) {
                $sendNotificationAllowed = "selected";
            }
            $sendNotificationAllowedOptionHtml = '<option value="0">No</option>';
            $sendNotificationAllowedOptionHtml .= '<option value="1" ' . $sendNotificationAllowed . '>Yes</option>';
            // new user notitfication mail send to main manager setting //

            $perPageRecordsSettingArr = Setting::getPerPageRecordsSetting($brandId);
            $perPageRecord = 20;
            if (isset($perPageRecordsSettingArr['per_page_records']['value']) && $perPageRecordsSettingArr['per_page_records']['value'] > 0) {
                $perPageRecord = $perPageRecordsSettingArr['per_page_records']['value'];
            }
            $perPageRecordsOptionHtml = "";
            $perPageOptionArray = array(10,20);
            foreach ($perPageOptionArray as $p){
                $perPageRecordsOptionSelected = $p==$perPageRecord ? "selected" : "";
                $perPageRecordsOptionHtml .= '<option value="'.$p.'" ' . $perPageRecordsOptionSelected . '>'.$p.'</option>';
            }
            
            $iframeTokenSettingArr = Setting::getIframeTokenSetting($brandId);
            
            $affilGetMoneySettingArr = Setting::getAffilGetMoneySetting($brandId);
            $affilGetMoney = "deposit";
            if (isset($affilGetMoneySettingArr['affil_get_money']['value']) && $affilGetMoneySettingArr['affil_get_money']['value'] != "") {
                $affilGetMoney = $affilGetMoneySettingArr['affil_get_money']['value'];
            }
            $affilDepositSelected = $affilGetMoney=="deposit" ? "selected" : "";
            $affilDepositBuySelected = $affilGetMoney=="deposit_buy" ? "selected" : "";
            $affilGetMoneyOptionHtml = '<option value="deposit" '.$affilDepositSelected.'>Deposit</option>';
            $affilGetMoneyOptionHtml .= '<option value="deposit_buy" '.$affilDepositBuySelected.'>Deposit+Buy</option>';
            
            // get mass email limit //
            $massEmailLimitArr = Setting::getBrandMassEmailLimitationSetting($brandId);
            $massEmailLimit = (isset($massEmailLimitArr['option_value'])) ? $massEmailLimitArr['option_value'] : '';
            $massEmailLimitUid = (isset($massEmailLimitArr['uid'])) ? $massEmailLimitArr['uid'] : '';

            // get automated call upload limit //
            $automatedUploadLimitLimitArr = Setting::getBrandAutomatedUploadLimitationSetting($brandId);
            $automatedUploadLimit = (isset($automatedUploadLimitLimitArr['option_value'])) ? $automatedUploadLimitLimitArr['option_value'] : '';
            $automatedUploadLimitUid = (isset($automatedUploadLimitLimitArr['uid'])) ? $automatedUploadLimitLimitArr['uid'] : '';

            $brandDomainArr = Setting::getDomainUrl($brandId);

            $brandTimeZone = Setting::getBrandTimeZone($brandId);
            $timezoneOptionHtml = Utility::selectTimeZoneOption($brandTimeZone);

            $IPStoreArr = Setting::getBrandSettingByKeyName($brandId, 'enable_ip_store');
            $IPStoreSelectedEnabled = '';
            if(isset($IPStoreArr['option_value']) && $IPStoreArr['option_value'] == 1){
                $IPStoreSelectedEnabled = 'selected';
            }
            $IPStoreOption = '<option value="0">No</option>
                                        <option value="1" '.$IPStoreSelectedEnabled.'>Yes</option>';

            $error_message = Html::get_notification();
            $body = make::tpl('admin/brand_setting')->assign(array(
                        'error_message' => $error_message,
                        'depositBonusHtml' => $html,
                        'reg_bonus_uid' => (isset($regBonusArr['uid'])) ? $regBonusArr['uid'] : '',
                        'reg_bonus_amount' => (isset($regBonusArr['bonus_value'])) ? $regBonusArr['bonus_value'] : '',
                        'currency_option' => Currency::select_options($selectedCurrecy),
                        'brand_wise_currecy_uid' => $currencySetting['uid'],
                        'mass_assign_limit' => $massAssignLimit,
                        'mass_assign_limit_id' => $massAssignLimitUid,
                        'manager_list' => Manage_permission::select_options_manager($defaultManagerUid, $brand_admin_userid),
                        'default_assign_manager_id' => $defaultManagerSettingId,
                        'default_banner_click_min_rev_uid' => isset($defaultClickRevSettingArr['default_banner_click_min_rev']['uid']) ? $defaultClickRevSettingArr['default_banner_click_min_rev']['uid'] : '',
                        'default_banner_click_max_rev_uid' => isset($defaultClickRevSettingArr['default_banner_click_max_rev']['uid']) ? $defaultClickRevSettingArr['default_banner_click_max_rev']['uid'] : '',
                        'default_banner_click_min_rev' => isset($defaultClickRevSettingArr['default_banner_click_min_rev']['value']) ? $defaultClickRevSettingArr['default_banner_click_min_rev']['value'] : '',
                        'default_banner_click_max_rev' => isset($defaultClickRevSettingArr['default_banner_click_max_rev']['value']) ? $defaultClickRevSettingArr['default_banner_click_max_rev']['value'] : '',
                        'emailExistAllowOptionHtml' => $emailExistAllowOptionHtml,
                        'duplicate_email_allowed_uid' => isset($duplicateEmailAllowedSettingArr['duplicate_email_allowed']['uid']) ? $duplicateEmailAllowedSettingArr['duplicate_email_allowed']['uid'] : '',
                        'perPageRecordsOptionHtml' => $perPageRecordsOptionHtml,
                        'per_page_records_uid' => isset($perPageRecordsSettingArr['per_page_records']['uid']) ? $perPageRecordsSettingArr['per_page_records']['uid'] : '',
                        'iframe_token_uid' => isset($iframeTokenSettingArr['iframe_token']['uid']) ? $iframeTokenSettingArr['iframe_token']['uid'] : '',
                        'iframe_token' => isset($iframeTokenSettingArr['iframe_token']['value']) ? $iframeTokenSettingArr['iframe_token']['value'] : '',
                        'affilGetMoneyOptionHtml' => $affilGetMoneyOptionHtml,
                        'affil_get_money_uid' => isset($affilGetMoneySettingArr['affil_get_money']['uid']) ? $affilGetMoneySettingArr['affil_get_money']['uid'] : '',
                        'mass_email_limit' => $massEmailLimit,
                        'mass_email_limit_id' => $massEmailLimitUid,
                        'automated_call_upload_limit' => $automatedUploadLimit,
                        'automated_call_upload_limit_id' => $automatedUploadLimitUid,
                        'domain_protocol_https_selected' => (isset($brandDomainArr['domain_protocol']) && $brandDomainArr['domain_protocol'] == 'https') ? 'selected' : '',
                        'domain_name' => isset($brandDomainArr['domain_name']) ? $brandDomainArr['domain_name'] : '',
                        'timezoneOptionHtml' => $timezoneOptionHtml,
                        'sendNotificationAllowedOptionHtml' => $sendNotificationAllowedOptionHtml,
                        'new_user_register_mail_to_main_manager_uid' => isset($newUserNotificationToMainManagerSettingArr['new_user_register_mail_to_main_manager']['uid']) ? $newUserNotificationToMainManagerSettingArr['new_user_register_mail_to_main_manager']['uid'] : '',
                        'enable_ip_store' => (isset($IPStoreArr['option_value'])) ? $IPStoreArr['option_value'] : '',
                        'enable_ip_store_uid' => (isset($IPStoreArr['uid'])) ? $IPStoreArr['uid'] : '',
                        'IPStoreOption' => $IPStoreOption,
                    ))
                    ->get_content();
        } else {
            $permission_message = Html::site_notification('You have not permission of this section ', 'Warning');
        }

        $active = array(
            'active' => 'setting',
            'active_li' => 'set_gen_sub_li',
        );

        $tplSkeleton = make::tpl('admin/index')->assign($active)->assign(array(
                    'body' => $body,
                    'permission_error_message' => $permission_message,
                    'meta_title' => 'Leads8 | Admin panel | Setting Edit',
                    'meta_keywords' => 'Leads8',
                    'meta_description' => 'Leads8',
                ))->get_content();


        output::as_html($tplSkeleton);
    }

    public function Twcall() {
        $brand = config::req('paths');
        $brand_admin_userid = $_SESSION[$brand[0] . '_admin_userid'];

        $module = $this->arrPaths[2];
        $section = (isset($this->arrPaths[3])) ? $this->arrPaths[3] : 'edit';

        $brand_uri = config::url() . $this->arrPaths[0] . '/';
        $brnad_slug = $this->arrPaths[0];
        $brandId = Brands::GetBranduidByBrandslug($brnad_slug);

        // save setting for twilio configuration //
        if (isset($_POST['save_twilio'])) {
            if (!empty($_POST)) {
                if(isset($_POST['voiso_dialer_token']) && $_POST['voiso_dialer_token'] != '' && ($_POST['call_option'] == 5 || $_POST['call_option'] == 6 || $_POST['call_option'] == 7 || $_POST['call_option'] == 9)){
                    if(Setting::voisoDialerTokenExist($brandId, $brand_admin_userid, $_POST['voiso_dialer_token'], $_POST['call_option'])){
                        Html::set_notification('Dialer token already exist', 'Error');
                        output::redirect($brand_uri . 'admin/setting/Twcall');
                    }
                }
                $isSaveTwSetting = Setting::saveTwilioSetting($brandId, $brand_admin_userid, $_POST);
                if ($isSaveTwSetting) {
                    output::redirect($brand_uri . 'admin/setting/Twcall');
                } else {
                    $message = Html::site_notification('Not successfully added', 'Error');
                }
            }
        }
        
        // end save twilio configuration ///
        // get call settings of manager //
        $isAllowedChecked = '';
        $callSettingArr = Setting::getCallSettingByManager($brandId, $brand_admin_userid);
        
        if (!empty($callSettingArr)) {
            if ($callSettingArr['is_allowed'] > 0) {
                $isAllowedChecked = "checked='checked'";
            }
        }
        
//        if(empty($callSettingArr)){
//            $parentManagerId = Manager::getparentmanager($brand_admin_userid);
//            $twilioSettingArr = Setting::getCallSettingByManager($brandId, $parentManagerId);
//            
//            $callSettingArr['call_option'] = isset($twilioSettingArr['call_option']) ? $twilioSettingArr['call_option'] : 1;
//        }

        $dispay_twilio_txt_box = 'display:none';
        $twilio_text_box_required = '';

        if (isset($callSettingArr['call_option']) && $callSettingArr['call_option'] == 2) {
            $dispay_twilio_txt_box = '';
//            $twilio_text_box_required = 'required="required"';
        }
        
        $dispay_voiso_txt_box = 'display:none';
        $dispay_voiso_apikey_box = 'display:none';
        $voiso_text_box_required = '';
        if (isset($callSettingArr['call_option']) && $callSettingArr['call_option'] == 5) {
            $dispay_voiso_txt_box = '';
            $dispay_voiso_apikey_box = '';
//            $voiso_text_box_required = 'required="required"';
        }; 
        
        $dispay_coperato_api_url_box = 'display:none';
        $dispay_coperato_username_box = 'display:none';
        $dispay_coperato_password_box = 'display:none';
        if (isset($callSettingArr['call_option']) && $callSettingArr['call_option'] == 6) {
            $dispay_coperato_api_url_box = '';
            $dispay_coperato_username_box = '';
            $dispay_coperato_password_box = '';
            $dispay_voiso_txt_box = '';
//            $voiso_text_box_required = 'required="required"';
        };

        $dispay_commpeak_api_url_box = 'display:none';
        if (isset($callSettingArr['call_option']) && $callSettingArr['call_option'] == 7) {
            $dispay_commpeak_api_url_box = '';
            $dispay_voiso_txt_box = '';
        }

        $dispay_primetelecom_api_url_box = 'display:none';
        if (isset($callSettingArr['call_option']) && $callSettingArr['call_option'] == 8) {
            $dispay_primetelecom_api_url_box = '';
            $dispay_voiso_txt_box = '';
        }

        $dispay_myphone_api_url_box = 'display:none';
        if (isset($callSettingArr['call_option']) && $callSettingArr['call_option'] == 9) {
            $dispay_myphone_api_url_box = '';
            $dispay_voiso_txt_box = '';
        }

        $dispay_ringostat_authkey_box = 'display:none';
        $ringostat_text_box_required = '';
        if (isset($callSettingArr['call_option']) && $callSettingArr['call_option'] == 10) {
            $dispay_voiso_txt_box = 'display:none;';
            $dispay_ringostat_authkey_box = '';
            $ringostat_text_box_required = 'required';
        }; 
        
        
        $languageId = Languages::issetLanguageSession();
        $langTransDataArr = Languages::getLanguageTranslationContent($brand_id, $languageId, 'customers');

        $error_message = Html::get_notification();
        
        $body = make::tpl('admin/brand_call_setting')
                ->assign($langTransDataArr)
                ->assign(array(
                    'brandId' => $brandId,
                    'error_message' => $error_message,
                    'is_allowed_checked' => $isAllowedChecked,
                    'twilio_friendly_name' => isset($callSettingArr['friendly_name']) ? $callSettingArr['friendly_name'] : '',
                    'twilio_caller_number' => isset($callSettingArr['caller_number']) ? $callSettingArr['caller_number'] : '',
                    'twilio_account_sid' => isset($callSettingArr['account_sid']) ? $callSettingArr['account_sid'] : '',
                    'twilio_auth_token' => isset($callSettingArr['auth_token']) ? $callSettingArr['auth_token'] : '',
                    'twilio_app_sid' => isset($callSettingArr['app_sid']) ? $callSettingArr['app_sid'] : '',
                    'twilio_setting_uid' => isset($callSettingArr['uid']) ? $callSettingArr['uid'] : '',
                    'call_option_html' => Setting::getCallOptionHtml((isset($callSettingArr['call_option']) ? $callSettingArr['call_option'] : 1)),
                    'dispay_twilio_txt_box' => $dispay_twilio_txt_box,
                    'twilio_text_box_required' => $twilio_text_box_required,
                    'dispay_voiso_txt_box' => $dispay_voiso_txt_box,
                    'dispay_voiso_apikey_box' => $dispay_voiso_apikey_box,
                    'dispay_coperato_api_url_box' => $dispay_coperato_api_url_box,
                    'dispay_coperato_username_box' => $dispay_coperato_username_box,
                    'dispay_coperato_password_box' => $dispay_coperato_password_box,
                    'voiso_text_box_required' => $voiso_text_box_required,
                    'voiso_api_key' => isset($callSettingArr['voiso_api_key']) ? $callSettingArr['voiso_api_key'] : '',
                    'coperato_api_url' => isset($callSettingArr['coperato_api_url']) ? $callSettingArr['coperato_api_url'] : '',
                    'coperato_username' => isset($callSettingArr['coperato_username']) ? $callSettingArr['coperato_username'] : '',
                    'coperato_password' => isset($callSettingArr['coperato_password']) ? $callSettingArr['coperato_password'] : '',
                    'commpeak_api_url' => isset($callSettingArr['commpeak_api_url']) ? $callSettingArr['commpeak_api_url'] : '',
                    'voiso_dialer_token' => isset($callSettingArr['voiso_dialer_token']) ? $callSettingArr['voiso_dialer_token'] : '',
                    'prime_telecom_api_url' => isset($callSettingArr['prime_telecom_api_url']) ? $callSettingArr['prime_telecom_api_url'] : '',
                    'myphone_api_url' => isset($callSettingArr['myphone_api_url']) ? $callSettingArr['myphone_api_url'] : '',
                    'dispay_commpeak_api_url_box' => $dispay_commpeak_api_url_box,
                    'dispay_myphone_api_url_box' => $dispay_myphone_api_url_box,
                    'dispay_primetelecom_api_url_box' => $dispay_primetelecom_api_url_box,
                    'dispay_ringostat_authkey_box' => $dispay_ringostat_authkey_box,
                    'ringostat_text_box_required' => $ringostat_text_box_required,
                    'ringostat_auth_key' => isset($callSettingArr['ringostat_auth_key']) ? $callSettingArr['ringostat_auth_key'] : '',
                ))
                ->get_content();
        $active = array(
            'active' => 'setting',
            'active_li' => 'set_twc_sub_li'
        );

        $tplSkeleton = make::tpl('admin/index')->assign($active)->assign(array(
                    'body' => $body,
                    'permission_error_message' => $permission_message,
                    'meta_title' => 'Leads8 | Admin panel | Setting Edit',
                    'meta_keywords' => 'Leads8',
                    'meta_description' => 'Leads8',
                ))->get_content();


        output::as_html($tplSkeleton);
    }

    public function payment() {
        $brand = config::req('paths');
        $brand_admin_userid = $_SESSION[$brand[0] . '_admin_userid'];
        $brand_slug = $this->arrPaths[0];
        $brand_id = Brands::GetBranduidByBrandslug($brand_slug);
        $error_message = '';
        $permission_message = '';

        $res = Manage_permission::check_permission_manager($brand_admin_userid, 'setting', 'payment_setting');
        if (count($res) > 0) {
            $body = '';
            $message = '';
            $pm_active = 'active';

            $safechargeContentHtml = '';
            $algochargeContentHtml = '';
            $pbsContentHtml = '';
            $zotapayContentHtml = '';
            $paypalContentHtml = '';
            $stripeContentHtml = '';
            $worldpayContentHtml = '';
            $solidContentHtml = '';
            $squarePayContentHtml = '';
            $praxisContentHtml = '';
            $tap2payContentHtml = '';
            $bitcoinsContentHtml = '';
            $paymentMethodContentHtml = '';
            $paypalFormContentHtml = '';
            $paynetContentHtml = '';
            $gumballpayContentHtml = '';
            $neobankContentHtml = '';
            $paymentwallContentHtml = '';
            $ipasspayContentHtml = '';
            $poundPayContentHtml = '';
            $sysPgContentHtml = '';
            $octoVioContentHtml = '';
            $prmoneyContentHtml = '';
            $securepaycardContentHtml = '';
            $amlnnodeContentHtml = '';
            $paystudioContentHtml = '';
            $chargeMoneyContentHtml = '';
            $kryptovaContentHtml = '';
            $eupaymentzContentHtml = '';
            $icemarketContentHtml = '';
            $paypalExpressCheckoutContentHtml = '';
            $coinbaseCommerceCheckoutContentHtml = '';
            $tinkoffContentHtml = '';

            if (isset($_POST['edit_setting'])) {

                $auth = Setting::savePaymentMethodSetting($brand_id, $_POST);
                $auth = Setting::saveGatewaySetting($brand_id, $_POST);
                $auth = Setting::setCLientDetails($brand_id, $_POST);
                $auth = Setting::setAlgoMerchatDetails($brand_id, $_POST);
                $auth = Setting::setPBSMerchatDetails($brand_id, $_POST);
                $auth = Setting::setIFrameSafechargeDetails($brand_id, $_POST);
                $auth = Setting::setBitcoinsSetting($brand_id, $_POST);
                $auth = Setting::setZotapayMerchatDetails($brand_id, $_POST);
                $auth = Setting::setPaypalMerchatDetails($brand_id, $_POST);
                $auth = Setting::setStripeMerchatDetails($brand_id, $_POST);
                $auth = Setting::setWorldPayMerchatDetails($brand_id, $_POST);
                $auth = Setting::setSolidPaymentMerchatDetails($brand_id, $_POST);
                $auth = Setting::setSquarePaymentMerchatDetails($brand_id, $_POST);
                $auth = Setting::setPraxisDetails($brand_id, $_POST);
                $auth = Setting::settap2payDetails($brand_id, $_POST);
                $auth = Setting::setPaypalFormDetails($brand_id, $_POST);
                $auth = Setting::setPaynetMerchatDetails($brand_id, $_POST);
                $auth = Setting::setGumballpayMerchatDetails($brand_id, $_POST);
                $auth = Setting::setNeobankMerchatDetails($brand_id, $_POST);
                $auth = Setting::setPaymentwallSetting($brand_id, $_POST);
                $auth = Setting::setInternetCashbankMerchatDetails($brand_id, $_POST);
                $auth = Setting::setIpasspayMerchatDetails($brand_id, $_POST);
                $auth = Setting::setPoundPayMerchatDetails($brand_id, $_POST);
                $auth = Setting::setSysPgMerchatDetails($brand_id, $_POST);
                $auth = Setting::setOctovioMerchatDetails($brand_id, $_POST);
                $auth = Setting::setprmoneyMerchatDetails($brand_id, $_POST);
                $auth = Setting::setsecurepaycardMerchatDetails($brand_id, $_POST);
                $auth = Setting::setAmlnnodeMerchatDetails($brand_id, $_POST);
                $auth = Setting::setPaystudioMerchatDetails($brand_id, $_POST);
                $auth = Setting::setChargeMoneyMerchatDetails($brand_id, $_POST);
                $auth = Setting::setKryptovaMerchatDetails($brand_id, $_POST);
                $auth = Setting::setEupaymentzMerchatDetails($brand_id, $_POST);
                $auth = Setting::setIceMarketMerchatDetails($brand_id, $_POST);
                $auth = Setting::setPaypalExpressMerchatDetails($brand_id, $_POST);
                $auth = Setting::setCoinbaseCommerceMerchatDetails($brand_id, $_POST);
                $auth = Setting::setTinkoffMerchatDetails($brand_id, $_POST);

                Html::set_notification('Payment setting has been successfully saved', 'Success');
                output::redirect(Brand::getUrl('setting/payment'));
                exit;
            }

            $settingArr = Setting::getAllSetting($brand_id);
            // ***** //
            $paymentMethodArr = Setting::getAllPaymentMethod();

            $brandWiseMethod = Setting::getBrandWisePaymentMethodSetting($brand_id);
//        echo "<pre>";
//        print_r($brandWiseMethod);
//        exit;
            $methodHtml = '';
            if (!empty($paymentMethodArr)) {
                $brandWisePMArr = array();

                if (!empty($brandWiseMethod['setting'])) {
                    $brandWisePMArr = $brandWiseMethod['setting'];
                }

                foreach ($paymentMethodArr as $method) {

                    $paymentMethodEnable = '';
                    if (in_array($method['uid'], $brandWisePMArr)) {
                        $paymentMethodEnable = 'checked';
                    }

                    $status_checked = ($brandWiseMethod['status'][$method['uid']] == 1) ? 'checked="checked"' : '';
                    $disabled_limit = ($brandWiseMethod['status'][$method['uid']] == 1) ? 'disabled="disabled"' : '';

                    $paymentMethodContentHtml .= make::tpl('admin/payment_method_content')
                            ->assign(array(
                                'uid' => $method['uid'],
                                'paymentMethodEnable' => $paymentMethodEnable,
                                'method_name' => $method['payment_type'],
                                'status_checked' => $status_checked,
                                'disabled_limit' => $disabled_limit,
                                'method_limit' => $brandWiseMethod['limit'][$method['uid']],
                                'optional_key' => $brandWiseMethod['optional_key'][$method['uid']],
                            ))
                            ->get_content();
                }
            }
            // ***** //

            $gatewayArr = Setting::getAllGateway();
            $brandWiseGateway = Setting::getBrandWiseGatewaySetting($brand_id);
            //echo "<pre>";print_r($gatewayArr);exit;
            $gatewayHtml = '';
            if (!empty($gatewayArr)) {
                $brandWiseGWArr = array();

                if (!empty($brandWiseGateway['setting'])) {
                    $brandWiseGWArr = $brandWiseGateway['setting'];
                }
                
                foreach ($gatewayArr as $gateway) {
                    if ($gateway['gateway_name'] == 'bank transfer') {
                        continue;
                    }

                    if ($gateway['gateway_name'] == 'safe charge') {
                        $safechargeEnable = '';
                        if (in_array($gateway['uid'], $brandWiseGWArr)) {
                            $safechargeEnable = 'checked';
                        }
                        $safechargeContentHtml = make::tpl('admin/safecharge_payment_content')
                                ->assign(array(
                                    'uid' => $gateway['uid'],
                                    'gateway_name' => $gateway['gateway_name'],
                                    'safechargeEnable' => $safechargeEnable,
                                    'client_login_id' => $settingArr['safecharge_client_login_id']['option_value'],
                                    'client_login_id_uid' => $settingArr['safecharge_client_login_id']['uid'],
                                    'client_password' => $settingArr['safecharge_client_password']['option_value'],
                                    'client_password_uid' => $settingArr['safecharge_client_password']['uid'],
                                    'merchant_site_id' => $settingArr['safecharge_merchant_site_id']['option_value'],
                                    'merchant_site_id_uid' => $settingArr['safecharge_merchant_site_id']['uid'],
                                    'merchant_id' => $settingArr['safecharge_merchant_id']['option_value'],
                                    'merchant_id_uid' => $settingArr['safecharge_merchant_id']['uid'],
                                    'secret_key' => $settingArr['safecharge_secret_key']['option_value'],
                                    'secret_key_uid' => $settingArr['safecharge_secret_key']['uid'],
                                    'payment_url' => $settingArr['safecharge_payment_url']['option_value'],
                                    'payment_url_uid' => $settingArr['safecharge_payment_url']['uid'],
                                ))
                                ->get_content();
                    }

                    if ($gateway['gateway_name'] == 'algo charge') {
                        $algochargeEnable = '';
                        if (in_array($gateway['uid'], $brandWiseGWArr)) {
                            $algochargeEnable = 'checked';
                        }
                        $algochargeContentHtml = make::tpl('admin/algo_charge_payment_content')
                                ->assign(array(
                                    'uid' => $gateway['uid'],
                                    'gateway_name' => $gateway['gateway_name'],
                                    'algochargeEnable' => $algochargeEnable,
                                    'merchant_id' => $settingArr['merchant_id']['option_value'],
                                    'merchant_id_uid' => $settingArr['merchant_id']['uid'],
                                    'DCPId' => $settingArr['DCPId']['option_value'],
                                    'DCPId_uid' => $settingArr['DCPId']['uid'],
                                    'DCPPassword' => $settingArr['DCPPassword']['option_value'],
                                    'DCPPassword_uid' => $settingArr['DCPPassword']['uid'],
                                    'payment_url' => $settingArr['algo_charge_payment_url']['option_value'],
                                    'payment_url_uid' => $settingArr['algo_charge_payment_url']['uid'],
                                ))
                                ->get_content();
                    }
                    if ($gateway['gateway_name'] == 'PBS') {
                        $pbsEnable = '';
                        if (in_array($gateway['uid'], $brandWiseGWArr)) {
                            $pbsEnable = 'checked';
                        }
                        $pbsContentHtml = make::tpl('admin/pbs_payment_content')
                                ->assign(array(
                                    'uid' => $gateway['uid'],
                                    'gateway_name' => $gateway['gateway_name'],
                                    'pbsEnable' => $pbsEnable,
                                    'merchant_no' => $settingArr['merchant_no']['option_value'],
                                    'merchant_no_uid' => $settingArr['merchant_no']['uid'],
                                    'gateway_no' => $settingArr['gateway_no']['option_value'],
                                    'gateway_no_uid' => $settingArr['gateway_no']['uid'],
                                    'signkey' => $settingArr['signkey']['option_value'],
                                    'signkey_uid' => $settingArr['signkey']['uid'],
                                    'payment_url' => $settingArr['pbs_payment_url']['option_value'],
                                    'payment_url_uid' => $settingArr['pbs_payment_url']['uid'],
                                ))
                                ->get_content();
                    }
                    if ($gateway['gateway_name'] == 'zotapay') {
                        $zotapayEnable = '';
                        if (in_array($gateway['uid'], $brandWiseGWArr)) {
                            $zotapayEnable = 'checked';
                        }
                        $zotapayContentHtml = make::tpl('admin/zotapay_payment_content')
                                ->assign(array(
                                    'uid' => $gateway['uid'],
                                    'gateway_name' => $gateway['gateway_name'],
                                    'zotapayEnable' => $zotapayEnable,
                                    'zota_login' => $settingArr['zota_login']['option_value'],
                                    'zota_login_uid' => $settingArr['zota_login']['uid'],
                                    'zota_endpoint' => $settingArr['zota_endpoint']['option_value'],
                                    'zota_endpoint_uid' => $settingArr['zota_endpoint']['uid'],
                                    'zota_mer_control' => $settingArr['zota_mer_control']['option_value'],
                                    'zota_mer_control_uid' => $settingArr['zota_mer_control']['uid'],
                                    'payment_url' => $settingArr['zotapay_payment_url']['option_value'],
                                    'payment_url_uid' => $settingArr['zotapay_payment_url']['uid'],
                                    'ord_status_url' => $settingArr['zotapay_o_status_url']['option_value'],
                                    'ord_status_url_uid' => $settingArr['zotapay_o_status_url']['uid'],
                                ))
                                ->get_content();
                    }
                    if ($gateway['gateway_name'] == 'paypal') {
                        $paypalEnable = '';
                        if (in_array($gateway['uid'], $brandWiseGWArr)) {
                            $paypalEnable = 'checked';
                        }
                        $live_mode = '';
                        $sandbox_mode = '';
                        if ($settingArr['paypal_payment_mode']['option_value'] == 'live') {
                            $live_mode = 'selected';
                        } else {
                            $sandbox_mode = 'selected';
                        }
                        $paypalContentHtml = make::tpl('admin/paypal_payment_content')
                                ->assign(array(
                                    'uid' => $gateway['uid'],
                                    'gateway_name' => $gateway['gateway_name'],
                                    'paypalEnable' => $paypalEnable,
                                    'live_mode' => $live_mode,
                                    'sandbox_mode' => $sandbox_mode,
                                    'paypal_payment_mode_uid' => $settingArr['paypal_payment_mode']['uid'],
                                    'paypal_client_id' => $settingArr['paypal_client_id']['option_value'],
                                    'paypal_client_id_uid' => $settingArr['paypal_client_id']['uid'],
                                    'paypal_client_secret' => $settingArr['paypal_client_secret']['option_value'],
                                    'paypal_client_secret_uid' => $settingArr['paypal_client_secret']['uid'],
                                ))
                                ->get_content();
                    }
                    if ($gateway['gateway_name'] == 'paynet') {
                        $paynetEnable = '';
                        if (in_array($gateway['uid'], $brandWiseGWArr)) {
                            $paynetEnable = 'checked';
                        }
                        $live_mode = '';
                        $sandbox_mode = '';
                        if ($settingArr['paynet_payment_mode']['option_value'] == 'live') {
                            $live_mode = 'selected';
                        } else {
                            $sandbox_mode = 'selected';
                        }
                        $paynetContentHtml = make::tpl('admin/paynet_payment_content')
                                ->assign(array(
                                    'uid' => $gateway['uid'],
                                    'gateway_name' => $gateway['gateway_name'],
                                    'paynetEnable' => $paynetEnable,
                                    'live_mode' => $live_mode,
                                    'sandbox_mode' => $sandbox_mode,
                                    'paynet_payment_mode_uid' => $settingArr['paynet_payment_mode']['uid'],
                                    'paynet_group_id' => $settingArr['paynet_group_id']['option_value'],
                                    'paynet_group_id_uid' => $settingArr['paynet_group_id']['uid'],
                                    'paynet_merchant_control' => $settingArr['paynet_merchant_control']['option_value'],
                                    'paynet_merchant_control_uid' => $settingArr['paynet_merchant_control']['uid'],
                                ))
                                ->get_content();
                    }
                    if ($gateway['gateway_name'] == 'stripe') {
                        $stripeEnable = '';
                        if (in_array($gateway['uid'], $brandWiseGWArr)) {
                            $stripeEnable = 'checked';
                        }
                        $stripeContentHtml = make::tpl('admin/stripe_payment_content')
                                ->assign(array(
                                    'uid' => $gateway['uid'],
                                    'gateway_name' => $gateway['gateway_name'],
                                    'stripeEnable' => $stripeEnable,
                                    'sceret_key' => $settingArr['stripe_sceret_key']['option_value'],
                                    'sceret_key_uid' => $settingArr['stripe_sceret_key']['uid'],
                                    'publishable_key' => $settingArr['stripe_publishable_key']['option_value'],
                                    'publishable_key_uid' => $settingArr['stripe_publishable_key']['uid'],
                                ))
                                ->get_content();
                    }
                    if ($gateway['gateway_name'] == 'worldpay') {
                        $worldpayEnable = '';
                        if (in_array($gateway['uid'], $brandWiseGWArr)) {
                            $worldpayEnable = 'checked';
                        }
                        $worldpayContentHtml = make::tpl('admin/worldpay_payment_content')
                                ->assign(array(
                                    'uid' => $gateway['uid'],
                                    'gateway_name' => $gateway['gateway_name'],
                                    'worldpayEnable' => $worldpayEnable,
                                    'service_key' => $settingArr['worldpay_service_key']['option_value'],
                                    'service_key_uid' => $settingArr['worldpay_service_key']['uid'],
                                    'client_key' => $settingArr['worldpay_client_key']['option_value'],
                                    'client_key_uid' => $settingArr['worldpay_client_key']['uid'],
                                ))
                                ->get_content();
                    }
                    if ($gateway['gateway_name'] == 'solid') {
                        $solidEnable = '';
                        if (in_array($gateway['uid'], $brandWiseGWArr)) {
                            $solidEnable = 'checked';
                        }
                        $solidContentHtml = make::tpl('admin/solid_payment_content')
                                ->assign(array(
                                    'uid' => $gateway['uid'],
                                    'gateway_name' => $gateway['gateway_name'],
                                    'worldpayEnable' => $solidEnable,
                                    'solid_login' => $settingArr['solid_login']['option_value'],
                                    'solid_login_uid' => $settingArr['solid_login']['uid'],
                                    'solid_endpoint' => $settingArr['solid_endpoint']['option_value'],
                                    'solid_endpoint_uid' => $settingArr['solid_endpoint']['uid'],
                                    'solid_mer_control' => $settingArr['solid_mer_control']['option_value'],
                                    'solid_mer_control_uid' => $settingArr['solid_mer_control']['uid'],
                                    'payment_url' => $settingArr['solid_payment_url']['option_value'],
                                    'payment_url_uid' => $settingArr['solid_payment_url']['uid'],
                                    'ord_status_url' => $settingArr['solid_o_status_url']['option_value'],
                                    'ord_status_url_uid' => $settingArr['solid_o_status_url']['uid'],
                                ))
                                ->get_content();
                    }
                    if ($gateway['gateway_name'] == 'squarePay') {
                        $squarePayEnable = '';
                        if (in_array($gateway['uid'], $brandWiseGWArr)) {
                            $squarePayEnable = 'checked';
                        }
                        $squarePayContentHtml = make::tpl('admin/squarePay_payment_content')
                                ->assign(array(
                                    'uid' => $gateway['uid'],
                                    'gateway_name' => $gateway['gateway_name'],
                                    'squarePayEnable' => $squarePayEnable,
                                    'square_app_id' => $settingArr['square_app_id']['option_value'],
                                    'square_app_id_uid' => $settingArr['square_app_id']['uid'],
                                    'square_access_token' => $settingArr['square_access_token']['option_value'],
                                    'square_access_token_uid' => $settingArr['square_access_token']['uid'],
                                ))
                                ->get_content();
                    }

                    // For praxis payment method //
                    if ($gateway['gateway_name'] == 'praxis') {
                        $praxisEnable = '';
                        if (in_array($gateway['uid'], $brandWiseGWArr)) {
                            $praxisEnable = 'checked';
                        }

                        $praxisContentHtml = make::tpl('admin/praxis_payment_content')
                                ->assign(array(
                                    'uid' => $gateway['uid'],
                                    'gateway_name' => $gateway['gateway_name'],
                                    'praxisEnable' => $praxisEnable,
                                    'praxis_form_act_url' => $settingArr['praxis_form_act_url']['option_value'],
                                    'praxis_form_act_url_uid' => $settingArr['praxis_form_act_url']['uid'],
                                    'praxis_ws_password' => $settingArr['praxis_ws_password']['option_value'],
                                    'praxis_ws_password_uid' => $settingArr['praxis_ws_password']['uid'],
                                    'praxis_forntend_key' => $settingArr['praxis_forntend_key']['option_value'],
                                    'praxis_forntend_key_uid' => $settingArr['praxis_forntend_key']['uid'],
                                ))
                                ->get_content();
                    }

                    // For tap2pay payment method //
                    if ($gateway['gateway_name'] == 'tap2pay') {
                        $tap2payEnable = '';
                        if (in_array($gateway['uid'], $brandWiseGWArr)) {
                            $tap2payEnable = 'checked';
                        }

                        $tap2payContentHtml = make::tpl('admin/tap2pay_payment_content')
                                ->assign(array(
                                    'uid' => $gateway['uid'],
                                    'gateway_name' => $gateway['gateway_name'],
                                    'tap2payEnable' => $tap2payEnable,
                                    'tap2pay_merchant_id' => $settingArr['tap2pay_merchant_id']['option_value'],
                                    'tap2pay_merchant_id_uid' => $settingArr['tap2pay_merchant_id']['uid'],
                                    'tap2pay_api_token' => $settingArr['tap2pay_api_token']['option_value'],
                                    'tap2pay_api_token_uid' => $settingArr['tap2pay_api_token']['uid'],
                                ))
                                ->get_content();
                    }

                    if ($gateway['gateway_name'] == 'Bitcoins') {
                        $bitcoinsEnable = '';
                        if (in_array($gateway['uid'], $brandWiseGWArr)) {
                            $bitcoinsEnable = 'checked';
                        }
                        $bitcoinsContentHtml = make::tpl('admin/bitcoin_payment_content')
                                ->assign(array(
                                    'uid' => $gateway['uid'],
                                    'gateway_name' => $gateway['gateway_name'],
                                    'bitcoinsEnable' => $bitcoinsEnable,
                                    'api_key' => $settingArr['bitcoins_api_key']['option_value'],
                                    'api_key_uid' => $settingArr['bitcoins_api_key']['uid'],
                                    'api_secret' => $settingArr['bitcoins_api_secret']['option_value'],
                                    'api_secret_uid' => $settingArr['bitcoins_api_secret']['uid'],
                                ))
                                ->get_content();
                    }
                    if ($gateway['gateway_name'] == 'paypal_form') {
                        $paypalFormEnable = '';
                        if (in_array($gateway['uid'], $brandWiseGWArr)) {
                            $paypalFormEnable = 'checked';
                        }
                        $live_mode = '';
                        $sandbox_mode = '';
                        if ($settingArr['paypal_form_payment_mode']['option_value'] == 'live') {
                            $live_mode = 'selected';
                        } else {
                            $sandbox_mode = 'selected';
                        }
                        $paypalFormContentHtml = make::tpl('admin/paypal_form_payment_content')
                                ->assign(array(
                                    'uid' => $gateway['uid'],
                                    'gateway_name' => $gateway['gateway_name'],
                                    'paypalFormEnable' => $paypalFormEnable,
                                    'live_mode' => $live_mode,
                                    'sandbox_mode' => $sandbox_mode,
                                    'paypal_form_payment_mode_uid' => $settingArr['paypal_form_payment_mode']['uid'],
                                    'paypal_form_email' => $settingArr['paypal_form_email']['option_value'],
                                    'paypal_form_email_uid' => $settingArr['paypal_form_email']['uid'],
                                ))
                                ->get_content();
                    }
                    if ($gateway['gateway_name'] == 'gumballpay') {
                        $gumballpayEnable = '';
                        if (in_array($gateway['uid'], $brandWiseGWArr)) {
                            $gumballpayEnable = 'checked';
                        }
                        $live_mode = '';
                        $sandbox_mode = '';
                        if ($settingArr['gumballpay_payment_mode']['option_value'] == 'live') {
                            $live_mode = 'selected';
                        } else {
                            $sandbox_mode = 'selected';
                        }
                        $gumballpayContentHtml = make::tpl('admin/gumballpay_payment_content')
                                ->assign(array(
                                    'uid' => $gateway['uid'],
                                    'gateway_name' => $gateway['gateway_name'],
                                    'gumballpayEnable' => $gumballpayEnable,
                                    'live_mode' => $live_mode,
                                    'sandbox_mode' => $sandbox_mode,
                                    'gumballpay_payment_mode_uid' => $settingArr['gumballpay_payment_mode']['uid'],
                                    'gumballpay_group_id' => $settingArr['gumballpay_group_id']['option_value'],
                                    'gumballpay_group_id_uid' => $settingArr['gumballpay_group_id']['uid'],
                                    'gumballpay_merchant_control' => $settingArr['gumballpay_merchant_control']['option_value'],
                                    'gumballpay_merchant_control_uid' => $settingArr['gumballpay_merchant_control']['uid'],
                                ))
                                ->get_content();
                    }
                    if ($gateway['gateway_name'] == 'neobank') {
                        $neobankEnable = '';
                        if (in_array($gateway['uid'], $brandWiseGWArr)) {
                            $neobankEnable = 'checked';
                        }
                        $live_mode = '';
                        $sandbox_mode = '';
                        if ($settingArr['neobank_payment_mode']['option_value'] == 'live') {
                            $live_mode = 'selected';
                        } else {
                            $sandbox_mode = 'selected';
                        }
                        $neobankContentHtml = make::tpl('admin/neobank_payment_content')
                                ->assign(array(
                                    'uid' => $gateway['uid'],
                                    'gateway_name' => $gateway['gateway_name'],
                                    'neobankEnable' => $neobankEnable,
                                    'live_mode' => $live_mode,
                                    'sandbox_mode' => $sandbox_mode,
                                    'neobank_payment_mode_uid' => $settingArr['neobank_payment_mode']['uid'],
                                    'neobank_api_key' => $settingArr['neobank_api_key']['option_value'],
                                    'neobank_api_key_uid' => $settingArr['neobank_api_key']['uid'],
                                ))
                                ->get_content();
                    }
                    
                    if ($gateway['gateway_name'] == 'paymentwall') {
                        $paymentwallEnable = '';
                        if (in_array($gateway['uid'], $brandWiseGWArr)) {
                            $paymentwallEnable = 'checked';
                        }
                        $paymentwallContentHtml = make::tpl('admin/paymentwall_payment_content')
                                ->assign(array(
                                    'uid' => $gateway['uid'],
                                    'gateway_name' => $gateway['gateway_name'],
                                    'paymentwallEnable' => $paymentwallEnable,
                                    'api_key' => $settingArr['paymentwall_api_key']['option_value'],
                                    'api_key_uid' => $settingArr['paymentwall_api_key']['uid'],
                                    'api_secret' => $settingArr['paymentwall_api_secret']['option_value'],
                                    'api_secret_uid' => $settingArr['paymentwall_api_secret']['uid'],
                                ))
                                ->get_content();
                    }

                    if ($gateway['gateway_name'] == 'internet_cashbank') {
                        $internetCashbankEnable = '';
                        if (in_array($gateway['uid'], $brandWiseGWArr)) {
                            $internetCashbankEnable = 'checked';
                        }
                        $live_mode = '';
                        $sandbox_mode = '';
                        if ($settingArr['internet_cashbank_payment_mode']['option_value'] == 'live') {
                            $live_mode = 'selected';
                        } else {
                            $sandbox_mode = 'selected';
                        }
                        $internetCashbankContentHtml = make::tpl('admin/internet_cashbank_payment_content')
                                ->assign(array(
                                    'uid' => $gateway['uid'],
                                    'gateway_name' => $gateway['gateway_name'],
                                    'internetCashbankEnable' => $internetCashbankEnable,
                                    'live_mode' => $live_mode,
                                    'sandbox_mode' => $sandbox_mode,
                                    'internet_cashbank_payment_mode_uid' => $settingArr['internet_cashbank_payment_mode']['uid'],
                                    'internet_cashbank_merchant_key' => $settingArr['internet_cashbank_merchant_key']['option_value'],
                                    'internet_cashbank_merchant_key_uid' => $settingArr['internet_cashbank_merchant_key']['uid'],
                                    'internet_cashbank_merchant_sign' => $settingArr['internet_cashbank_merchant_sign']['option_value'],
                                    'internet_cashbank_merchant_sign_uid' => $settingArr['internet_cashbank_merchant_sign']['uid'],
                                ))
                                ->get_content();
                    }

                    if ($gateway['gateway_name'] == 'ipasspay') {
                        $ipasspayEnable = '';
                        if (in_array($gateway['uid'], $brandWiseGWArr)) {
                            $ipasspayEnable = 'checked';
                        }
                        $live_mode = '';
                        $sandbox_mode = '';
                        if ($settingArr['ipasspay_payment_mode']['option_value'] == 'live') {
                            $live_mode = 'selected';
                        } else {
                            $sandbox_mode = 'selected';
                        }
                        $ipasspayContentHtml = make::tpl('admin/payment/ipasspay_payment_content')
                                ->assign(array(
                                    'uid' => $gateway['uid'],
                                    'gateway_name' => $gateway['gateway_name'],
                                    'ipasspayEnable' => $ipasspayEnable,
                                    'live_mode' => $live_mode,
                                    'sandbox_mode' => $sandbox_mode,
                                    'ipasspay_payment_mode_uid' => $settingArr['ipasspay_payment_mode']['uid'],
                                    'ipasspay_merchant_key' => $settingArr['ipasspay_merchant_key']['option_value'],
                                    'ipasspay_merchant_key_uid' => $settingArr['ipasspay_merchant_key']['uid'],
                                    'ipasspay_app_id' => $settingArr['ipasspay_app_id']['option_value'],
                                    'ipasspay_app_id_uid' => $settingArr['ipasspay_app_id']['uid'],
                                    'ipasspay_api_secret' => $settingArr['ipasspay_api_secret']['option_value'],
                                    'ipasspay_api_secret_uid' => $settingArr['ipasspay_api_secret']['uid'],
                                ))
                                ->get_content();
                    }

                    if ($gateway['gateway_name'] == 'pound_pay') {
                        $poundPayEnable = '';
                        if (in_array($gateway['uid'], $brandWiseGWArr)) {
                            $poundPayEnable = 'checked';
                        }
                        $live_mode = '';
                        $sandbox_mode = '';
                        if ($settingArr['pound_pay_payment_mode']['option_value'] == 'live') {
                            $live_mode = 'selected';
                        } else {
                            $sandbox_mode = 'selected';
                        }
                        $poundPayContentHtml = make::tpl('admin/pound_pay_payment_content')
                                ->assign(array(
                                    'uid' => $gateway['uid'],
                                    'gateway_name' => $gateway['gateway_name'],
                                    'poundPayEnable' => $poundPayEnable,
                                    'live_mode' => $live_mode,
                                    'sandbox_mode' => $sandbox_mode,
                                    'pound_pay_payment_mode_uid' => $settingArr['pound_pay_payment_mode']['uid'],
                                    'pound_pay_api_key' => $settingArr['pound_pay_api_key']['option_value'],
                                    'pound_pay_api_key_uid' => $settingArr['pound_pay_api_key']['uid'],
                                ))
                                ->get_content();
                    }

                    if ($gateway['gateway_name'] == 'syspg') {
                        $ipasspayEnable = '';
                        if (in_array($gateway['uid'], $brandWiseGWArr)) {
                            $ipasspayEnable = 'checked';
                        }
                        $live_mode = '';
                        $sandbox_mode = '';
                        if ($settingArr['syspg_payment_mode']['option_value'] == 'live') {
                            $live_mode = 'selected';
                        } else {
                            $sandbox_mode = 'selected';
                        }
                        $sysPgContentHtml = make::tpl('admin/payment/syspg_payment_content')
                                ->assign(array(
                                    'uid' => $gateway['uid'],
                                    'gateway_name' => $gateway['gateway_name'],
                                    'ipasspayEnable' => $ipasspayEnable,
                                    'live_mode' => $live_mode,
                                    'sandbox_mode' => $sandbox_mode,
                                    'syspg_payment_mode_uid' => $settingArr['syspg_payment_mode']['uid'],
                                    'syspg_app_token' => $settingArr['syspg_app_token']['option_value'],
                                    'syspg_app_token_uid' => $settingArr['syspg_app_token']['uid'],
                                ))
                                ->get_content();
                    }

                    if ($gateway['gateway_name'] == 'octovio') {
                        $octovioEnable = '';
                        if (in_array($gateway['uid'], $brandWiseGWArr)) {
                            $octovioEnable = 'checked';
                        }
                        $live_mode = '';
                        $sandbox_mode = '';
                        if ($settingArr['octovio_payment_mode']['option_value'] == 'live') {
                            $live_mode = 'selected';
                        } else {
                            $sandbox_mode = 'selected';
                        }
                        $octoVioContentHtml = make::tpl('admin/payment/octovio_payment_content')
                                ->assign(array(
                                    'uid' => $gateway['uid'],
                                    'gateway_name' => $gateway['gateway_name'],
                                    'octovioEnable' => $octovioEnable,
                                    'live_mode' => $live_mode,
                                    'sandbox_mode' => $sandbox_mode,
                                    'octovio_payment_mode_uid' => $settingArr['octovio_payment_mode']['uid'],
                                    'octovio_merchant_key' => $settingArr['octovio_merchant_key']['option_value'],
                                    'octovio_merchant_key_uid' => $settingArr['octovio_merchant_key']['uid'],
                                    'octovio_hash_key' => $settingArr['octovio_hash_key']['option_value'],
                                    'octovio_hash_key_uid' => $settingArr['octovio_hash_key']['uid'],
                                ))
                                ->get_content();
                    }

                    if ($gateway['gateway_name'] == 'prmoney') {
                        $prmoneyEnable = '';
                        if (in_array($gateway['uid'], $brandWiseGWArr)) {
                            $prmoneyEnable = 'checked';
                        }
                        $live_mode = '';
                        $sandbox_mode = '';
                        if ($settingArr['prmoney_payment_mode']['option_value'] == 'live') {
                            $live_mode = 'selected';
                        } else {
                            $sandbox_mode = 'selected';
                        }
                        $prmoneyContentHtml = make::tpl('admin/payment/prmoney_payment_content')
                                ->assign(array(
                                    'uid' => $gateway['uid'],
                                    'gateway_name' => $gateway['gateway_name'],
                                    'prmoneyEnable' => $prmoneyEnable,
                                    'live_mode' => $live_mode,
                                    'sandbox_mode' => $sandbox_mode,
                                    'prmoney_payment_mode_uid' => $settingArr['prmoney_payment_mode']['uid'],
                                    'prmoney_client_id' => $settingArr['prmoney_client_id']['option_value'],
                                    'prmoney_client_id_uid' => $settingArr['prmoney_client_id']['uid'],
                                    'prmoney_client_secret' => $settingArr['prmoney_client_secret']['option_value'],
                                    'prmoney_client_secret_uid' => $settingArr['prmoney_client_secret']['uid'],
                                ))
                                ->get_content();
                    }
                    if ($gateway['gateway_name'] == 'securepaycard') {
                        $securepaycardEnable = '';
                        if (in_array($gateway['uid'], $brandWiseGWArr)) {
                            $securepaycardEnable = 'checked';
                        }
                        $live_mode = '';
                        $sandbox_mode = '';
                        if ($settingArr['securepaycard_payment_mode']['option_value'] == 'live') {
                            $live_mode = 'selected';
                        } else {
                            $sandbox_mode = 'selected';
                        }
                        $securepaycardContentHtml = make::tpl('admin/payment/securepaycard_payment_content')
                                ->assign(array(
                                    'uid' => $gateway['uid'],
                                    'gateway_name' => $gateway['gateway_name'],
                                    'securepaycardEnable' => $securepaycardEnable,
                                    'live_mode' => $live_mode,
                                    'sandbox_mode' => $sandbox_mode,
                                    'securepaycard_payment_mode_uid' => $settingArr['securepaycard_payment_mode']['uid'],
                                    'securepaycard_client_id' => $settingArr['securepaycard_client_id']['option_value'],
                                    'securepaycard_client_id_uid' => $settingArr['securepaycard_client_id']['uid'],
                                    'securepaycard_client_secret' => $settingArr['securepaycard_client_secret']['option_value'],
                                    'securepaycard_client_secret_uid' => $settingArr['securepaycard_client_secret']['uid'],
                                ))
                                ->get_content();
                    }

                    if ($gateway['gateway_name'] == 'amlnnode') {
                        $amlnnodeEnable = '';
                        if (in_array($gateway['uid'], $brandWiseGWArr)) {
                            $amlnnodeEnable = 'checked';
                        }
                        $amlnnodeContentHtml = make::tpl('admin/payment/amlnnode_payment_content')
                                ->assign(array(
                                    'uid' => $gateway['uid'],
                                    'gateway_name' => $gateway['gateway_name'],
                                    'amlnnodeEnable' => $amlnnodeEnable,
                                    'amlnnode_api_key' => $settingArr['amlnnode_api_key']['option_value'],
                                    'amlnnode_api_key_uid' => $settingArr['amlnnode_api_key']['uid'],
                                    'amlnnode_secret_key' => $settingArr['amlnnode_secret_key']['option_value'],
                                    'amlnnode_secret_key_uid' => $settingArr['amlnnode_secret_key']['uid'],
                                ))
                                ->get_content();
                    }

                    if ($gateway['gateway_name'] == 'paystudio') {
                        $paystudioEnable = '';
                        if (in_array($gateway['uid'], $brandWiseGWArr)) {
                            $paystudioEnable = 'checked';
                        }
                        $paystudioContentHtml = make::tpl('admin/payment/paystudio_payment_content')
                                ->assign(array(
                                    'uid' => $gateway['uid'],
                                    'gateway_name' => $gateway['gateway_name'],
                                    'paystudioEnable' => $paystudioEnable,
                                    'paystudio_api_key' => $settingArr['paystudio_api_key']['option_value'],
                                    'paystudio_api_key_uid' => $settingArr['paystudio_api_key']['uid'],
                                ))
                                ->get_content();
                    }

                    if ($gateway['gateway_name'] == 'chargemoney') {
                        $chargemoneyEnable = '';
                        if (in_array($gateway['uid'], $brandWiseGWArr)) {
                            $chargemoneyEnable = 'checked';
                        }
                        $chargeMoneyContentHtml = make::tpl('admin/payment/chargemoney_payment_content')
                                ->assign(array(
                                    'uid' => $gateway['uid'],
                                    'gateway_name' => $gateway['gateway_name'],
                                    'chargemoneyEnable' => $chargemoneyEnable,
                                    'chargemoney_api_key' => $settingArr['chargemoney_api_key']['option_value'],
                                    'chargemoney_api_key_uid' => $settingArr['chargemoney_api_key']['uid'],
                                ))
                                ->get_content();
                    }

                    if ($gateway['gateway_name'] == 'kryptova') {
                        $kryptovaEnable = '';
                        if (in_array($gateway['uid'], $brandWiseGWArr)) {
                            $kryptovaEnable = 'checked';
                        }
                        $kryptovaContentHtml = make::tpl('admin/payment/kryptova_payment_content')
                                ->assign(array(
                                    'uid' => $gateway['uid'],
                                    'gateway_name' => $gateway['gateway_name'],
                                    'kryptovaEnable' => $kryptovaEnable,
                                    'kryptova_api_key' => $settingArr['kryptova_api_key']['option_value'],
                                    'kryptova_api_key_uid' => $settingArr['kryptova_api_key']['uid'],
                                ))
                                ->get_content();
                    }

                    if ($gateway['gateway_name'] == 'eupaymentz') {
                        $eupaymentzEnable = '';
                        if (in_array($gateway['uid'], $brandWiseGWArr)) {
                            $eupaymentzEnable = 'checked';
                        }
                        $eupaymentzContentHtml = make::tpl('admin/payment/eupaymentz_payment_content')
                                ->assign(array(
                                    'uid' => $gateway['uid'],
                                    'gateway_name' => $gateway['gateway_name'],
                                    'eupaymentzEnable' => $eupaymentzEnable,
                                    'eupaymentz_account_id' => $settingArr['eupaymentz_account_id']['option_value'],
                                    'eupaymentz_account_id_uid' => $settingArr['eupaymentz_account_id']['uid'],
                                    'eupaymentz_account_password' => $settingArr['eupaymentz_account_password']['option_value'],
                                    'eupaymentz_account_password_uid' => $settingArr['eupaymentz_account_password']['uid'],
                                    'eupaymentz_account_passphrase' => $settingArr['eupaymentz_account_passphrase']['option_value'],
                                    'eupaymentz_account_passphrase_uid' => $settingArr['eupaymentz_account_passphrase']['uid'],
                                ))
                                ->get_content();
                    }

                    if ($gateway['gateway_name'] == 'icemarket') {
                        $icemarketEnable = '';
                        if (in_array($gateway['uid'], $brandWiseGWArr)) {
                            $icemarketEnable = 'checked';
                        }
                        $icemarketContentHtml = make::tpl('admin/payment/icemarket_payment_content')
                                ->assign(array(
                                    'uid' => $gateway['uid'],
                                    'gateway_name' => $gateway['gateway_name'],
                                    'icemarketEnable' => $icemarketEnable,
                                    'icemarket_account_id' => $settingArr['icemarket_account_id']['option_value'],
                                    'icemarket_account_id_uid' => $settingArr['icemarket_account_id']['uid']
                                ))
                                ->get_content();
                    }

                    if ($gateway['gateway_name'] == 'paypal_express') {
                        $paypalExpressEnable = '';
                        if (in_array($gateway['uid'], $brandWiseGWArr)) {
                            $paypalExpressEnable = 'checked';
                        }
                        $live_mode = '';
                        $sandbox_mode = '';
                        if ($settingArr['paypal_express_payment_mode']['option_value'] == 'live') {
                            $live_mode = 'selected';
                        } else {
                            $sandbox_mode = 'selected';
                        }
                        $paypalExpressCheckoutContentHtml = make::tpl('admin/paypal_express_payment_content')
                                ->assign(array(
                                    'uid' => $gateway['uid'],
                                    'gateway_name' => $gateway['gateway_name'],
                                    'paypalExpressEnable' => $paypalExpressEnable,
                                    'live_mode' => $live_mode,
                                    'sandbox_mode' => $sandbox_mode,
                                    'paypal_express_payment_mode_uid' => $settingArr['paypal_express_payment_mode']['uid'],
                                    'paypal_express_username' => $settingArr['paypal_express_username']['option_value'],
                                    'paypal_express_username_uid' => $settingArr['paypal_express_username']['uid'],
                                    'paypal_express_password' => $settingArr['paypal_express_password']['option_value'],
                                    'paypal_express_password_uid' => $settingArr['paypal_express_password']['uid'],
                                    'paypal_express_signature' => $settingArr['paypal_express_signature']['option_value'],
                                    'paypal_express_signature_uid' => $settingArr['paypal_express_signature']['uid'],
                                ))
                                ->get_content();
                    }

                    if ($gateway['gateway_name'] == 'coinbase_commerce') {
                        $coinbaseCommerceEnable = '';
                        if (in_array($gateway['uid'], $brandWiseGWArr)) {
                            $coinbaseCommerceEnable = 'checked';
                        }
                        $coinbaseCommerceCheckoutContentHtml = make::tpl('admin/payment/coinbase_commerce_payment_content')
                                ->assign(array(
                                    'uid' => $gateway['uid'],
                                    'gateway_name' => $gateway['gateway_name'],
                                    'coinbaseCommerceEnable' => $coinbaseCommerceEnable,
                                    'coinbase_commerce_api_key' => $settingArr['coinbase_commerce_api_key']['option_value'],
                                    'coinbase_commerce_api_key_uid' => $settingArr['coinbase_commerce_api_key']['uid'],
                                ))
                                ->get_content();
                    }

                    if ($gateway['gateway_name'] == 'tinkoff') {
                        $tinkoffEnable = '';
                        if (in_array($gateway['uid'], $brandWiseGWArr)) {
                            $tinkoffEnable = 'checked';
                        }
                        $live_mode = '';
                        $sandbox_mode = '';
                        if ($settingArr['tinkoff_payment_mode']['option_value'] == 'live') {
                            $live_mode = 'selected';
                        } else {
                            $sandbox_mode = 'selected';
                        }
                        $tinkoffContentHtml = make::tpl('admin/payment/tinkoff_payment_content')
                                ->assign(array(
                                    'uid' => $gateway['uid'],
                                    'gateway_name' => $gateway['gateway_name'],
                                    'tinkoffEnable' => $tinkoffEnable,
                                    'live_mode' => $live_mode,
                                    'sandbox_mode' => $sandbox_mode,
                                    'tinkoff_payment_mode_uid' => $settingArr['tinkoff_payment_mode']['uid'],
                                    'tinkoff_terminal_key' => $settingArr['tinkoff_terminal_key']['option_value'],
                                    'tinkoff_terminal_key_uid' => $settingArr['tinkoff_terminal_key']['uid'],
                                    'tinkoff_password' => $settingArr['tinkoff_password']['option_value'],
                                    'tinkoff_password_uid' => $settingArr['octovio_password']['uid'],
                                ))
                                ->get_content();
                    }
                }
            }

            $error_message = Html::get_notification();

            $body = make::tpl('admin/brand_wise_payment_method_setting')
                    ->assign(array(
                        'error_message' => $error_message,
                        'safechargeContentHtml' => $safechargeContentHtml,
                        'algochargeContentHtml' => $algochargeContentHtml,
                        'pbsContentHtml' => $pbsContentHtml,
                        'zotapayContentHtml' => $zotapayContentHtml,
                        'paypalContentHtml' => $paypalContentHtml,
                        'stripeContentHtml' => $stripeContentHtml,
                        'worldpayContentHtml' => $worldpayContentHtml,
                        'solidContentHtml' => $solidContentHtml,
                        'squarePayContentHtml' => $squarePayContentHtml,
                        'bitcoinsContentHtml' => $bitcoinsContentHtml,
                        'paymentMethodContentHtml' => $paymentMethodContentHtml,
                        'praxisContentHtml' => $praxisContentHtml,
                        'tap2payContentHtml' => $tap2payContentHtml,
                        'paypalFormContentHtml' => $paypalFormContentHtml,
                        'paynetContentHtml' => $paynetContentHtml,
                        'gumballpayContentHtml' => $gumballpayContentHtml,
                        'neobankContentHtml' => $neobankContentHtml,
                        'paymentwallContentHtml' => $paymentwallContentHtml,
                        'internetCashbankContentHtml' => $internetCashbankContentHtml,
                        'ipasspayContentHtml' => $ipasspayContentHtml,
                        'poundPayContentHtml' => $poundPayContentHtml,
                        'sysPgContentHtml' => $sysPgContentHtml,
                        'octoVioContentHtml' => $octoVioContentHtml,
                        'prmoneyContentHtml' => $prmoneyContentHtml,
                        'securepaycardContentHtml' => $securepaycardContentHtml,
                        'amlnnodeContentHtml' => $amlnnodeContentHtml,
                        'paystudioContentHtml' => $paystudioContentHtml,
                        'chargeMoneyContentHtml' => $chargeMoneyContentHtml,
                        'kryptovaContentHtml' => $kryptovaContentHtml,
                        'eupaymentzContentHtml' => $eupaymentzContentHtml,
                        'icemarketContentHtml' => $icemarketContentHtml,
                        'paypalExpressCheckoutContentHtml' => $paypalExpressCheckoutContentHtml,
                        'coinbaseCommerceCheckoutContentHtml' => $coinbaseCommerceCheckoutContentHtml,
                        'tinkoffContentHtml' => $tinkoffContentHtml
                    ))
                    ->get_content();
        } else {
            $permission_message = Html::site_notification('You have not permission of this section ', 'Warning');
        }

        $active = array(
            'active' => 'setting',
            'active_li' => 'set_pymnt_sub_li'
        );

        $tplSkeleton = make::tpl('admin/index')->assign($active)->assign(array(
                    'body' => $body,
                    'permission_error_message' => $permission_message,
                    'meta_title' => 'Leads8 | Admin panel | payment-method-setting',
                    'meta_keywords' => 'Leads8',
                    'meta_description' => 'Leads8',
                ))->get_content();


        output::as_html($tplSkeleton);
    }

    public function ip_limit() {
        $brand = config::req('paths');
        $brand_admin_userid = $_SESSION[$brand[0] . '_admin_userid'];

        $brand_uri = config::url() . $this->arrPaths[0] . '/';
        $brand_slug = $this->arrPaths[0];
        $brand_id = Brands::GetBranduidByBrandslug($brand_slug);

        $body = '';
        $message = '';
        $ipAddress = '';
        $permission_message = '';

        $res = Manage_permission::check_permission_manager($brand_admin_userid, 'setting', 'ip_limit_setting');
        if (count($res) > 0) {
            if (isset($_POST['save_ip'])) {
                $saveIp = Setting::saveBrandIpAddress($_POST, $brand_id, 0);
                if ($saveIp) {
                    Html::set_notification('Successfully created', 'success');
                    output::redirect($brand_uri . 'admin/setting/ip_limit/');
                } else {
                    $ipAddress = $_POST['ip'];
                    Html::set_notification('Not successfully update', 'error');
                }
            }

            if (isset($_POST['save_acive'])) {
                if (isset($_POST['ip_limitation_active'])) {
                    $ipLimitation = 1; /// 1 for limitation active
                } else {
                    $ipLimitation = 0; // 0 for limitation deactive //
                }
                $seve_Set = Setting::saveIpLimitation($ipLimitation, $brand_id, $_POST['ip_active_id']);
                if ($seve_Set) {
                    Html::set_notification('Successfully update', 'success');
                    output::redirect($brand_uri . 'admin/setting/ip_limit/');
                } else {
                    $ipAddress = $_POST['ip'];
                    Html::set_notification('Not successfully update', 'error');
                }
            }
            if(isset($_POST['delete_ip'])){
                if(!empty($_POST['delete_ip_checkbox'])){
                    $isDelete = Setting::deleteIpLimitation($brand_id, $_POST['delete_ip_checkbox']);
                    if($isDelete){
                        Html::set_notification('Successfully deleted', 'success');
                        output::redirect($brand_uri . 'admin/setting/ip_limit/');
                    } else {
                        Html::set_notification('Not successfully deleted', 'error');
                        output::redirect($brand_uri . 'admin/setting/ip_limit/');
                    }
                } else {
                    Html::set_notification('Please select IP', 'error');
                    output::redirect($brand_uri . 'admin/setting/ip_limit/');
                }
            }

            $message = Html::get_notification();

            $ip_address_checked = '';
            $ip_limitation_id = 0;
            $brandIpAddrHtml = '';
            $brandIpAdrArr = Setting::getAllIpAddressByBrand($brand_id, 0);

            if (!empty($brandIpAdrArr)) {
                foreach ($brandIpAdrArr as $bIpaddr) {
                    $brandIpAddrHtml .= "<tr>";
                    $brandIpAddrHtml .= "<td><input type='checkbox' name='delete_ip_checkbox[]' value='".$bIpaddr['uid']."' class='ip_check' id='".$bIpaddr['uid']."'/></td>";
                    $brandIpAddrHtml .= "<td>" . $bIpaddr['uid'] . "</td>";
                    $brandIpAddrHtml .= "<td>" . $bIpaddr['ip_address'] . "</td>";
                    $brandIpAddrHtml .= "<td>" . $bIpaddr['note'] . "</td>";
                    $brandIpAddrHtml .= '<td><a data-toggle="modal" href="#myAlert' . $bIpaddr['uid'] . '" onclick="return confirm("Are you sure?")" class="">Delete</a>';
                    $brandIpAddrHtml .= '</td>';
                    $brandIpAddrHtml .= "</tr>";
                    $brandIpAddrHtml .= '<div id="myAlert' . $bIpaddr['uid'] . '" class="modal">';
                    $brandIpAddrHtml .= '<div class="modal-header">';
                    $brandIpAddrHtml .= '<button data-dismiss="modal" class="close" type="button">&times;</button>';
                    $brandIpAddrHtml .= '<h3>Delete "' . $bIpaddr['ip_address'] . '"</h3>';
                    $brandIpAddrHtml .= '</div>';
                    $brandIpAddrHtml .= '<div class="modal-body">';
                    $brandIpAddrHtml .= '<p>Are you sure? You want to delete "' . $bIpaddr['ip_address'] . '" ?</p>';
                    $brandIpAddrHtml .= '</div>';
                    $brandIpAddrHtml .= '<div class="modal-footer">';
                    $brandIpAddrHtml .= '<a class="btn btn-primary" href="' . $brand_uri . 'admin/setting/delete/?del=ip&id=' . base64_encode($bIpaddr['uid']) . '">Confirm</a>';
                    $brandIpAddrHtml .= '<a data-dismiss="modal" class="btn btn-primary" href="#">Cancel</a>';
                    $brandIpAddrHtml .= '</div>';
                    $brandIpAddrHtml .= '</div>';
                }
            }

            $ipLimitationActive = Setting::getBrandIpLimitationSetting($brand_id);
            if (!empty($ipLimitationActive)) {
                if ($ipLimitationActive['option_value'] == 1) {
                    $ip_address_checked = "checked=checked";
                }
                $ip_limitation_id = $ipLimitationActive['uid'];
            }

            $brandOptionHtml = Brands::select_options($brand_id);
            $body = make::tpl('admin/brand_wise_ip_setting')
                    ->assign(array(
                        'error_message' => $message,
                        'brand_id' => $_SESSION['brand_id'],
                        'brand_list' => $brandOptionHtml,
                        'brandIpAddrHtml' => $brandIpAddrHtml,
                        'ip_limitation_id' => $ip_limitation_id,
                        'ip_address_checked' => $ip_address_checked,
                    ))
                    ->get_content();
        } else {
            $permission_message = Html::site_notification('You have not permission of this section ', 'Warning');
        }

        $active = array(
            'active' => 'setting',
            'active_li' => 'set_iplmt_sub_li'
        );

        $tplSkeleton = make::tpl('admin/index')->assign($active)->assign(array(
                    'body' => $body,
                    'permission_error_message' => $permission_message,
                    'meta_title' => 'Leads8 | Admin panel | payment-method-setting',
                    'meta_keywords' => 'Leads8',
                    'meta_description' => 'Leads8',
                ))->get_content();


        output::as_html($tplSkeleton);
    }

    public function smtp() {
        $brand = config::req('paths');
        $brand_admin_userid = $_SESSION[$brand[0] . '_admin_userid'];

        $brand_uri = config::url() . $this->arrPaths[0] . '/';
        $brand_slug = $this->arrPaths[0];
        $brand_id = Brands::GetBranduidByBrandslug($brand_slug);

        $body = '';
        $message = '';
        $permission_message = '';

        $res = Manage_permission::check_permission_manager($brand_admin_userid, 'setting', 'smtp_setting');
        if (count($res) > 0) {
            if (isset($_POST['smtp_setting']) && !empty($_POST['smtp_setting'])) {
                $_POST['brand_id'] = $brand_id;
                Setting::setSMTPSetting($_POST);
                Setting::setSettingSendViaEmail($_POST['brand_id'], $_POST['send_email_via'], $_POST['send_email_via_uid']);
            }

            $checkedWinnerWonPrize = '';
            $sendScheduledEmail = '';
            
            $smtp_selected = '';
            $brandsmtp_required = '';
            $mailchimp_selected = '';
            $mailchimp_required = '';
            $activetrail_selected = '';
            $activetrail_required = '';
            $customerio_selected = '';
            $customerio_required = '';

            $settingArr = Setting::getAllSetting($brand_id);

            if (isset($settingArr['winner_won_prize_email']['option_value']) && $settingArr['winner_won_prize_email']['option_value'] == 1) {
                $checkedWinnerWonPrize = 'checked="checked"';
            }
            if (isset($settingArr['send_scheduled_email']['option_value']) && $settingArr['send_scheduled_email']['option_value'] == 1) {
                $sendScheduledEmail = 'checked="checked"';
            }
            if (isset($settingArr['send_email_via']['option_value']) && $settingArr['send_email_via']['option_value'] == 'smtp') {
                $smtp_selected = 'selected';
                $brandsmtp_required = 'required';
            }
            if (isset($settingArr['send_email_via']['option_value']) && $settingArr['send_email_via']['option_value'] == 'mailchimp') {
                $mailchimp_selected = 'selected';
                $mailchimp_required = 'required';
            }
            if (isset($settingArr['send_email_via']['option_value']) && $settingArr['send_email_via']['option_value'] == 'activetrail') {
                $activetrail_selected = 'selected';
                $activetrail_required = 'required';
            }
            if (isset($settingArr['send_email_via']['option_value']) && $settingArr['send_email_via']['option_value'] == 'customerio') {
                $customerio_selected = 'selected';
                $customerio_required = 'required';
            }
            if (isset($settingArr['smtp_secure']['option_value']) && $settingArr['smtp_secure']['option_value'] == 'tls') {
                $mailchimp_smtp_secure_selected = 'selected';
            }
            if (isset($settingArr['brand_smtp_secure']['option_value']) && $settingArr['brand_smtp_secure']['option_value'] == 'tls') {
                $brand_smtp_secure_selected = 'selected';
            }
            if (isset($settingArr['customerio_smtp_secure']['option_value']) && $settingArr['customerio_smtp_secure']['option_value'] == 'tls') {
                $customerio_smtp_secure_selected = 'selected';
            }

            $brandOptionHtml = Brands::select_options($brand_id);
            $body = make::tpl('admin/brand_wise_smtp_setting')
                    ->assign(array(
                        'error_message' => $message,
                        'brand_id' => $_SESSION['brand_id'],
                        'brand_list' => $brandOptionHtml,
                        'smtp_host_id' => $settingArr['smtp_host']['uid'],
                        'smtp_host' => $settingArr['smtp_host']['option_value'],
                        'smtp_port_id' => $settingArr['smtp_port']['uid'],
                        'smtp_port' => $settingArr['smtp_port']['option_value'],
                        'smtp_username_id' => $settingArr['smtp_username']['uid'],
                        'smtp_username' => $settingArr['smtp_username']['option_value'],
                        'smtp_password_id' => $settingArr['smtp_password']['uid'],
                        'smtp_password' => $settingArr['smtp_password']['option_value'],
                        'mail_chimp_api_key_id' => $settingArr['mail_chimp_api_key']['uid'],
                        'mail_chimp_api_key' => $settingArr['mail_chimp_api_key']['option_value'],
                        'mail_chimp_list_id' => $settingArr['mail_chimp_list_id']['uid'],
                        'mail_chimp_list' => $settingArr['mail_chimp_list_id']['option_value'],
                        'activetrail_api_url_id'   => $settingArr['activetrail_api_url']['uid'],
                        'activetrail_api_url'   => $settingArr['activetrail_api_url']['option_value'],
                        'activetrail_private_key_id'   => $settingArr['activetrail_private_key']['uid'],
                        'activetrail_private_key'   => $settingArr['activetrail_private_key']['option_value'],
                        'brand_smtp_host_id' => $settingArr['brand_smtp_host']['uid'],
                        'brand_smtp_host' => $settingArr['brand_smtp_host']['option_value'],
                        'brand_smtp_port_id' => $settingArr['brand_smtp_port']['uid'],
                        'brand_smtp_port' => $settingArr['brand_smtp_port']['option_value'],
                        'brand_smtp_username_id' => $settingArr['brand_smtp_username']['uid'],
                        'brand_smtp_username' => $settingArr['brand_smtp_username']['option_value'],
                        'brand_smtp_password_id' => $settingArr['brand_smtp_password']['uid'],
                        'brand_smtp_password' => $settingArr['brand_smtp_password']['option_value'],
                        'mailchimpSelected' => $mailchimp_selected,
                        'activetrailSelected' => $activetrail_selected,
                        'customerioSelected' => $customerio_selected,
                        'smtpSelected' => $smtp_selected,
                        'send_email_via_uid' => $settingArr['send_email_via']['uid'],
                        'brandsmtp_required' => $brandsmtp_required,
                        'mailchimp_required' => $mailchimp_required,
                        'customerio_required' => $customerio_required,
                        'mailchimp_smtp_secure_selected' => $mailchimp_smtp_secure_selected,
                        'brand_smtp_secure_selected' => $brand_smtp_secure_selected,
                        'customerio_smtp_secure_selected' => $customerio_smtp_secure_selected,
                        'customerio_site_id_id'   => $settingArr['customerio_site_id']['uid'],
                        'customerio_site_id'   => $settingArr['customerio_site_id']['option_value'],
                        'customerio_api_key_id'   => $settingArr['customerio_api_key']['uid'],
                        'customerio_api_key'   => $settingArr['customerio_api_key']['option_value'],
                        'customerio_smtp_host_id' => $settingArr['customerio_smtp_host']['uid'],
                        'customerio_smtp_host' => $settingArr['customerio_smtp_host']['option_value'],
                        'customerio_smtp_port_id' => $settingArr['customerio_smtp_port']['uid'],
                        'customerio_smtp_port' => $settingArr['customerio_smtp_port']['option_value'],
                        'customerio_smtp_username_id' => $settingArr['customerio_smtp_username']['uid'],
                        'customerio_smtp_username' => $settingArr['customerio_smtp_username']['option_value'],
                        'customerio_smtp_password_id' => $settingArr['customerio_smtp_password']['uid'],
                        'customerio_smtp_password' => $settingArr['customerio_smtp_password']['option_value'],
                    ))
                    ->get_content();
        } else {
            $permission_message = Html::site_notification('You have not permission of this section ', 'Warning');
        }

        $active = array(
            'active' => 'setting',
            'active_li' => 'set_smtp_sub_li'
        );

        $tplSkeleton = make::tpl('admin/index')->assign($active)->assign(array(
                    'body' => $body,
                    'permission_error_message' => $permission_message,
                    'meta_title' => 'Leads8 | Admin panel | payment-method-setting',
                    'meta_keywords' => 'Leads8',
                    'meta_description' => 'Leads8',
                ))->get_content();


        output::as_html($tplSkeleton);
    }

//    public function currency() {
//        $brand_uri = config::url() . $this->arrPaths[0] . '/';
//        $brand_slug = $this->arrPaths[0];
//        $brand_id = Brands::GetBranduidByBrandslug($brand_slug);
//
//        $body = '';
//        $brandRowHtml = '';
//        //echo "Test";
//        if (isset($_POST['save_setting'])) {
////           echo "<pre>";           print_r($_POST); exit;
//            $saveSetting = Currency::saveBrandWiseCurrencySetting($_POST);
//            if ($saveSetting) {
//                Html::set_notification('Successfully update', 'success');
//                output::redirect($brand_uri . 'admin/setting/currency');
//            } else {
//                Html::set_notification('Not successfully update', 'error');
//            }
//        }
//        $message = Html::get_notification();
//
//        $currencySetting = Currency::getBrandwiseCurrencySetting($brand_id);
//        $brandName = Brands::GetBrandNameByBranduid($brand_id);
//        $selectedCurrecy = isset($currencySetting['currency_uid']) ? $currencySetting['currency_uid'] : 0;
//        $brandRowHtml .= make::tpl('admin/brandwise_currency_setting.row')
//                ->assign(array(
//                    'brand_uid' => $brand_id,
//                    'brand_name' => $brandName,
//                    'currency_option' => Currency::select_options($selectedCurrecy),
//                    'brand_wise_currecy_uid' => $currencySetting['uid']
//                ))
//                ->get_content();
//
//        $body = make::tpl('admin/brandwise_currency_setting')
//                ->assign(array(
//                    'currencySettingRow' => $brandRowHtml,
//                    'error_message' => $message
//                ))
//                ->get_content();
//        $active = array(
//            'active' => 'setting',
//            'active_li' => 'set_crncy_sub_li'
//        );
//
//        $tplSkeleton = make::tpl('admin/index')->assign($active)->assign(array(
//                    'body' => $body,
//                    'meta_title' => 'Leads8 | Admin panel | currency-setting',
//                    'meta_keywords' => 'Leads8',
//                    'meta_description' => 'Leads8',
//                ))->get_content();
//
//
//        output::as_html($tplSkeleton);
//    }

    public function countrypayment() {
        $brand = config::req('paths');
        $brand_admin_userid = $_SESSION[$brand[0] . '_admin_userid'];

        $brand_uri = config::url() . $this->arrPaths[0] . '/';
        $brand_slug = $this->arrPaths[0];
        $brand_id = Brands::GetBranduidByBrandslug($brand_slug);

        $body = '';
        $message = '';
        $settingRowHtml = '';
        $colspan = 1;
        $permission_message = '';
        
        $page = Pagination::getpage();
        $setLimit = Pagination::setlimit();
        $pageLimit = Pagination::getpagelimit($setLimit);

        $res = Manage_permission::check_permission_manager($brand_admin_userid, 'setting', 'country_payment_setting');
        if (count($res) > 0) {
            if (isset($_POST['save_country_setting'])) {
                $saveCountrySetting = Setting::saveCountryPaymentMethodSetting($_POST, $brand_id);
                if ($saveCountrySetting) {
                    Html::set_notification('Successfully update', 'success');
                    output::redirect($brand_uri . 'admin/setting/countrypayment/?page='.$page);
                } else {
                    Html::set_notification('Not successfully update', 'success');
                }
            }
            $message = Html::get_notification();

            $tableTHDataHtml = '';
            $gatewayArr = Setting::getAllGateway();
            $tableTHDataHtml .= "<th>Country Name</th>";
            if (!empty($gatewayArr)) {
                foreach ($gatewayArr as $gateway) {
                    $tableTHDataHtml .= "<input type='hidden' name='" . str_replace(" ", "_", $gateway['gateway_name']) . "_uid' value='" . $gateway['uid'] . "'>";
                    $tableTHDataHtml .= "<th width='100'>" . $gateway['gateway_name'] . "</th>";
                    $colspan++;
                }
            }
            $notSuppGatewayCountryArr = Setting::getNotSuppGatewayCountry($brand_id);
            $countryArr = Country::getCountryList($setLimit, $pageLimit);
            if (!empty($countryArr)) {
                foreach ($countryArr as $country) {
                    $settingRowHtml .= '<tr>';
                    $settingRowHtml .= "<td>" . $country['countryName'] . "</td>";
                    foreach ($gatewayArr as $gateway) {
                        $nSupCountryArr = (isset($notSuppGatewayCountryArr[$gateway['uid']])) ? $notSuppGatewayCountryArr[$gateway['uid']] : array();
                        if (in_array($country['id'], $nSupCountryArr)) {
                            $settingRowHtml .= "<td width='100'><input type='checkbox' checked='checked' class='form-control' name='" . str_replace(" ", "_", $gateway['gateway_name']) . "[]' value='" . $country['id'] . "'></th>";
                        } else {
                            $settingRowHtml .= "<td width='100'><input type='checkbox' class='form-control' name='" . str_replace(" ", "_", $gateway['gateway_name']) . "[]' value='" . $country['id'] . "'></th>";
                        }
                    }
                    $settingRowHtml .= '</tr>';
                }
            }
            
            $count = Country::getCountCountryList();
            $url = $brand_uri . 'admin/setting/countrypayment/';
            $Pagination = Pagination::displayPaginationBelow($setLimit, $page, $url, $count);

            $body = make::tpl('admin/country_wise_payment_method')->assign(array(
                'tableTHDataHtml' => $tableTHDataHtml,
                'settingRowHtml' => $settingRowHtml,
                'colspan' => $colspan,
                'error_message' => $message,
                'Pagination' => $Pagination,
            ));
        } else {
            $permission_message = Html::site_notification('You have not permission of this section ', 'Warning');
        }

        $active = array(
            'active' => 'setting',
            'active_li' => 'set_cntry_sub_li'
        );

        $tplSkeleton = make::tpl('admin/index')->assign($active)->assign(array(
                    'body' => $body,
                    'permission_error_message' => $permission_message,
                    'meta_title' => 'Leads8 | Admin panel | Setting',
                    'meta_keywords' => 'Leads8',
                    'meta_description' => 'Leads8',
                ))->get_content();


        output::as_html($tplSkeleton);
    }

    // function for setting of platform admin language //
    public function language() {
        $body = '';
        $message = '';
        $settingRowHtml = '';
        $settingUid = '';
        $platLangArr = array();

        if (isset($_POST['save_language_setting'])) {
            $isSave = Setting::savePlatLangSetting($_POST);
            if ($isSave) {
                output::redirect(config::admin_url() . 'setting/language/?msg=success');
                exit;
            } else {
                $message = Html::site_notification('Setting not update successfully', 'fail');
            }
        }

        if (isset($_REQUEST['msg']) && $_REQUEST['msg'] == 'success') {
            $message = Html::site_notification('successfully update', 'Success');
        }

        $plaformLangSetting = Setting::getPlatformLanguageSetting();

        if (!empty($plaformLangSetting)) {
            $settingUid = $plaformLangSetting['uid'];
            $platLangArr = explode(',', $plaformLangSetting['option_value']);
        }

        // get all language //
        $languageArr = Languages::getAllLanguages();
        if (!empty($languageArr)) {
            foreach ($languageArr as $language) {
                $settingRowHtml .= '<tr>';
                $settingRowHtml .= '<td>';
                if (in_array($language['slug'], $platLangArr)) {
                    $settingRowHtml .= "<input type='checkbox'class='form-control' checked='checked' name='language[]' value='" . $language['slug'] . "' />";
                } else {
                    $settingRowHtml .= "<input type='checkbox'class='form-control' name='language[]' value='" . $language['slug'] . "' />";
                }
                $settingRowHtml .= '</td>';
                $settingRowHtml .= '<td>';
                $settingRowHtml .= $language['slug'];
                $settingRowHtml .= '</td>';
                $settingRowHtml .= '<td>';
                $settingRowHtml .= $language['name'];
                $settingRowHtml .= '</td>';
                $settingRowHtml .= '</tr>';
            }
        }

        $body = make::tpl('admin/platform_language_setting')->assign(array(
            'settingRowHtml' => $settingRowHtml,
            'error_message' => $message,
            'settingUid' => $settingUid,
        ));

        $active = array(
            'active' => 'setting',
            'active_li' => 'set_lng_sub_li',
        );
        $tplSkeleton = make::tpl('admin/index')->assign($active)->assign(array(
                    'body' => $body,
                    'meta_title' => 'Leads8 | Admin panel | Setting',
                    'meta_keywords' => 'Leads8',
                    'meta_description' => 'Leads8',
                ))->get_content();


        output::as_html($tplSkeleton);
    }

    // function for setting of game by country wise //
    public function countryGameSetting() {
        $body = '';
        $message = '';
        $settingRowHtml = '';
        $settingId = '';

        $brand_id = (isset($_SESSION['brand_id'])) ? $_SESSION['brand_id'] : 0;

        if (isset($_POST['brand'])) {
            $brand_id = (!empty($_POST['brand'])) ? $_POST['brand'] : 0;
            $_SESSION['brand_id'] = $brand_id;
        }

        if (isset($_POST['save_country_setting'])) {
            if ($_POST['brand_id'] > 0 && $_POST['brand_id'] != '') {
                $isSavecountrySetting = Setting::setCountryGameSetting($_POST);
                if ($isSavecountrySetting) {
                    output::redirect(config::admin_url() . 'setting/countryGameSetting/?msg=success');
                } else {
                    $message = Html::site_notification('Setting not update successfully', 'fail');
                }
            } else {
                $message = Html::site_notification('please select brand', 'Error');
            }
        }

        if (isset($_REQUEST['msg']) && $_REQUEST['msg'] == 'success') {
            $message = Html::site_notification('successfully update', 'Success');
        }

        $countrySettingArr = array();
        $countrySetting = Setting::getCountryGameSetting($brand_id);
        if (!empty($countrySetting)) {
            $countrySettingArr = explode(',', $countrySetting['option_value']);
            $settingId = $countrySetting['uid'];
        }
        $brandOptionHtml = Brands::select_options($brand_id);
        $countryArr = Country::getCountryList();
        if (!empty($countryArr)) {
            foreach ($countryArr as $country) {
                $settingRowHtml .= '<tr>';
                $settingRowHtml .= "<td>" . $country['countryName'] . "</td>";

                if (in_array($country['id'], $countrySettingArr)) {
                    $settingRowHtml .= "<td width='100'><input type='checkbox' checked='checked' class='form-control' name='games[]' value='" . $country['id'] . "'></th>";
                } else {
                    $settingRowHtml .= "<td width='100'><input type='checkbox' class='form-control' name='games[]' value='" . $country['id'] . "'></th>";
                }

                $settingRowHtml .= '</tr>';
            }
        }

        $body = make::tpl('admin/country_wise_game_setting')->assign(array(
            'brand_list' => $brandOptionHtml,
            'brand_uid' => $brand_id,
            'settingRowHtml' => $settingRowHtml,
            'settingId' => $settingId,
            'error_message' => $message,
                //'Pagination' => $Pagination,
        ));

        $active = array(
            'dashboard' => '',
            'draws' => 'class="active"',
            'active' => 'game_setting'
        );
        $tplSkeleton = make::tpl('admin/index')->assign($active)->assign(array(
                    'body' => $body,
                    'meta_title' => 'Leads8 | Admin panel | Setting',
                    'meta_keywords' => 'Leads8',
                    'meta_description' => 'Leads8',
                ))->get_content();


        output::as_html($tplSkeleton);
    }

    public function mailchimp() {
        $brand = config::req('paths');
        $brand_admin_userid = $_SESSION[$brand[0] . '_admin_userid'];

        $brand_uri = config::url() . $this->arrPaths[0] . '/';
        $brand_slug = $this->arrPaths[0];
        $brand_id = Brands::GetBranduidByBrandslug($brand_slug);

        $body = '';
        $message = '';
        $permission_message = '';

        $res = Manage_permission::check_permission_manager($brand_admin_userid, 'setting', 'mailchimp_setting');
        if (count($res) > 0) {
            if (isset($_POST['save_mailchimp_setting'])) {
                $_POST['brand_uid'] = $brand_id;
                //echo "<pre>"; print_r($_POST); exit;
                $saveApiLey = Setting::saveMailchimpApiKey($_POST);
                $isSave = Setting::saveMailchimpListSetting($_POST);
                if ($isSave || $saveApiLey) {
                    Html::set_notification('Successfully update', 'success');
                    output::redirect($brand_uri . 'admin/setting/mailchimp/');
                } else {
                    Html::set_notification('Not successfully update', 'Error');
                }
            }

            $message = Html::get_notification();

            $brandLangListHtmlRow = '';

            $settingArr = Setting::getAllSetting($brand_id);
            $mailchimpListSetting = Setting::getMailchimpListSettingByBrand($brand_id);

            $brandLanguageArr = Languages::getBrandLanguage($brand_id);
            if (!empty($brandLanguageArr)) {
                foreach ($brandLanguageArr as $brandLang) {
                    $listId = isset($mailchimpListSetting[$brandLang['uid']]) ? $mailchimpListSetting[$brandLang['uid']] : '';
                    $brandLangListHtmlRow .= '<tr>';
                    $brandLangListHtmlRow .= '<td>';
                    $brandLangListHtmlRow .= '<input type="hidden" value="' . $brandLang['uid'] . '" name="lang_uid[]">';
                    $brandLangListHtmlRow .= $brandLang['name'];
                    $brandLangListHtmlRow .= '</td>';
                    $brandLangListHtmlRow .= '<td>';
                    $brandLangListHtmlRow .= $brandLang['slug'];
                    $brandLangListHtmlRow .= '</td>';
                    $brandLangListHtmlRow .= '<td>';
                    $brandLangListHtmlRow .= '<input type="text" class="form-control" value="' . $listId . '" name=list_id[' . $brandLang['uid'] . ']>';
                    $brandLangListHtmlRow .= '</td>';
                    $brandLangListHtmlRow .= '</tr>';
                }
            }


            $brandOptionHtml = Brands::select_options($brand_id);
            $body = make::tpl('admin/brand_wise_mailchimp_setting')
                    ->assign(array(
                        'error_message' => $message,
                        'brand_list' => $brandOptionHtml,
                        'brand_uid' => $brand_id,
                        'brandLangListHtmlRow' => $brandLangListHtmlRow,
                        'mail_chimp_api_key_id' => $settingArr['mail_chimp_api_key']['uid'],
                        'mail_chimp_api_key' => $settingArr['mail_chimp_api_key']['option_value'],
                    ))
                    ->get_content();
        } else {
            $permission_message = Html::site_notification('You have not permission of this section ', 'Warning');
        }

        $active = array(
            'active' => 'setting',
            'active_li' => 'set_mchimp_sub_li'
        );

        $tplSkeleton = make::tpl('admin/index')->assign($active)->assign(array(
                    'body' => $body,
                    'permission_error_message' => $permission_message,
                    'meta_title' => 'Leads8 | Admin panel | payment-method-setting',
                    'meta_keywords' => 'Leads8',
                    'meta_description' => 'Leads8',
                ))->get_content();


        output::as_html($tplSkeleton);
    }

    public function dSetting() {
        $body = '';
        $message = '';
        /// get all brand select option //
        $brand_id = 0;

        if (isset($_POST['brand'])) {
            $brand_id = (!empty($_POST['brand'])) ? $_POST['brand'] : 0;
            $_SESSION['brand_id'] = $brand_id;
        }

        if (isset($_POST['l_mail_setting']) && !empty($_POST['l_mail_setting'])) {
            if ($_POST['brand_id'] > 0 && $_POST['brand_id'] != '') {
                $saveLSetting = Setting::saveLotteryMailSetting($_POST);
                if ($saveLSetting) {
                    output::redirect(config::admin_url() . 'setting/dSetting/?msg=success');
                } else {
                    $message = Html::site_notification('Setting successfully not saved', 'Error');
                }
            } else {
                $message = Html::site_notification('please select brand', 'Error');
            }
        }

        if (isset($_REQUEST['msg']) && $_REQUEST['msg'] == 'success') {
            $message = Html::site_notification('Setting successfully saved', 'Success');
        }

        $lotterySettingHtml = '';
        if (isset($_SESSION['brand_id']) && !empty($_SESSION['brand_id'])) {
            $brand_id = $_SESSION['brand_id'];
            $lotteryArr = Lottery_management::getAllData();

            $drawSetting = Setting::getBrandLotteryMailSetting($brand_id);
            $drawSettingArr = explode(',', $drawSetting['option_value']);

            if (!empty($lotteryArr)) {
                $lotterySettingHtml .= '<input type="hidden" name="l_mail_uid" value="' . $drawSetting['uid'] . '" />';
                foreach ($lotteryArr as $lottery) {
                    $lotterySettingHtml .= '<tr>';
                    $lotterySettingHtml .= '<td>';
                    if (in_array($lottery['uid'], $drawSettingArr)) {
                        $lotterySettingHtml .= '<input type="checkbox" checked="checked" name="l_m_setting[]" value="' . $lottery['uid'] . '" class="form-control" >';
                    } else {
                        $lotterySettingHtml .= '<input type="checkbox" name="l_m_setting[]" value="' . $lottery['uid'] . '" class="form-control" >';
                    }

                    $lotterySettingHtml .= '</td>';
                    $lotterySettingHtml .= '<td>';
                    $lotterySettingHtml .= $lottery['name'];
                    $lotterySettingHtml .= '</td>';
                    $lotterySettingHtml .= '</tr>';
                }
                $lotterySettingHtml .= '<tr>';
                $lotterySettingHtml .= '<td colspan="3" style="text-align: center;">';
                $lotterySettingHtml .= ' <input type="submit" name="l_mail_setting" value="Save" class="btn red">';
                $lotterySettingHtml .= '<a href="{{ uri_admin }}setting/dSetting" class="btn btn-default">Reset</a>';
                $lotterySettingHtml .= '</td>';
                $lotterySettingHtml .= '</tr>';
            }
        }

        $brandOptionHtml = Brands::select_options($brand_id);
        $body = make::tpl('admin/draw_mail_setting')
                ->assign(array(
                    'error_message' => $message,
                    'brand_id' => $_SESSION['brand_id'],
                    'brand_list' => $brandOptionHtml,
                    'lottery_list_html' => $lotterySettingHtml,
                ))
                ->get_content();
        $active = array(
            'dashboard' => '',
            'setting' => 'class="active"',
            'active' => 'setting'
        );

        $tplSkeleton = make::tpl('admin/index')->assign($active)->assign(array(
                    'body' => $body,
                    'meta_title' => 'Leads8 | Admin panel | payment-method-setting',
                    'meta_keywords' => 'Leads8',
                    'meta_description' => 'Leads8',
                ))->get_content();


        output::as_html($tplSkeleton);
    }

    // function for delete //
    public function delete() {
        if (isset($_REQUEST['del']) && $_REQUEST['del'] != '' && $_REQUEST['del'] == 'ip') {
            if (isset($_REQUEST['id']) && $_REQUEST['id'] != '') {
                $id = base64_decode($_REQUEST['id']);
                $deleteIp = Setting::deleteIpaddressById($id);
                if ($deleteIp) {
                    $brand_uri = config::url() . $this->arrPaths[0] . '/';
                    Html::set_notification('Successfully deleted', 'success');
                    output::redirect($brand_uri . 'admin/setting/ip_limit/');
                } else {
                    Html::set_notification('Not successfully delete', 'Error');
                    output::redirect($brand_uri . 'admin/setting/ip_limit/');
                }
            } else {
                Html::set_notification('Not successfully delete', 'Error');
                output::redirect($brand_uri . 'admin/setting/ip_limit/');
            }
        } else if (isset($_REQUEST['del']) && $_REQUEST['del'] != '' && $_REQUEST['del'] == 'blocked_ip') {
            if (isset($_REQUEST['id']) && $_REQUEST['id'] != '') {
                $id = base64_decode($_REQUEST['id']);
                $deleteIp = Setting::deleteBlockedIpaddressById($id);
                if ($deleteIp) {
                    $brand_uri = config::url() . $this->arrPaths[0] . '/';
                    Html::set_notification('Successfully deleted', 'success');
                    output::redirect($brand_uri . 'admin/setting/blocked_ip/');
                } else {
                    Html::set_notification('Not successfully delete', 'Error');
                    output::redirect($brand_uri . 'admin/setting/blocked_ip/');
                }
            } else {
                Html::set_notification('Not successfully delete', 'Error');
                output::redirect($brand_uri . 'admin/setting/blocked_ip/');
            }
        } else {
            output::redirect($brand_uri . 'admin/setting/ip_limit/');
        }
    }

    /*
     * Function for check smtp connetion properly or not
     */

    public function checkConnection() {
        $response = array();
        if (isset($_POST) && !empty($_POST)) {
            $smtp['host'] = $_POST['host'];
            $smtp['port'] = $_POST['port'];
            $smtp['username'] = $_POST['username'];
            $smtp['password'] = $_POST['password'];
            $smtp['secure'] = $_POST['secure'];

            $connection = Output::smtpConnection($smtp);
            $response['message'] = $connection;
        } else {
            $response['message'] = 'Something went wrong';
        }
        echo json_encode($response);
        exit;
    }

    /*
     * Function for remove deposit bonus setting by id 
     */

    public function ajaxDepositBonusRemove() {
        if (!empty($_POST)) {
            $settingId = $_POST['id'];

            $isDelete = Setting::removeDepositBonusSetting($settingId);
            if ($isDelete) {
                $response['status'] = true;
            } else {
                $response['status'] = false;
            }
        } else {
            $response['status'] = false;
        }
        echo json_encode($response);
        exit;
    }

    /*
     * Function for setting of postback//
     */

    public function postback() {
        $brand = config::req('paths');
        $brand_admin_userid = $_SESSION[$brand[0] . '_admin_userid'];
//        $module = $this->arrPaths[2];
//        $section = (isset($this->arrPaths[3])) ? $this->arrPaths[3] : 'edit';
        $brand_uri = config::url() . $this->arrPaths[0] . '/';
        $brnad_slug = $this->arrPaths[0];
        $brandId = Brands::GetBranduidByBrandslug($brnad_slug);
        $error_message = '';
        $body = '';
        $permission_message = '';
        $postData = array();

        $res = Manage_permission::check_permission_manager($brand_admin_userid, 'setting', 'postback_setting');
        if (count($res) > 0) {
            if (isset($_POST['save_setting'])) {
                if (count($arrErr = Setting::isValidPostbackSetting($_POST)) > 0) {
                    // getting error//
                    $postData = $_POST;
                    $error_message = '<div class="alert alert-danger">';
                    $error_message .= implode("<br>", $arrErr);
                    $error_message .= '</div>';
                    $postData['error_message'] = $error_message;
                } else {
                    // save setting //
                    $isSave = Setting::setPostbackDetails($brandId, $_POST);
                    if ($isSave) {
                        Html::set_notification('Successfully saved', 'success');
                        output::redirect(Brand::getUrl('setting/postback'));
                        exit;
                    } else {
                        $postData = $_POST;
                        Html::set_notification('Successfully not saved', 'error');
                    }
                }
            }

            $error_message = Html::get_notification();

            $postbackUrlData = Setting::getPostbackUrlData($brandId);
            $postbackEnableData = Setting::getPostbackEnableData($brandId);

            $postEnableChecked = '';
            if (isset($postbackEnableData['option_value']) && $postbackEnableData['option_value'] == 1) {
                $postEnableChecked = 'checked';
            }

            $body = make::tpl('admin/postback_setting')
                    ->assign($postData)
                    ->assign(array(
                'error_message' => $error_message,
                'postback_url_uid' => $postbackUrlData['uid'],
                'postback_url' => $postbackUrlData['option_value'],
                'postback_enable_uid' => $postbackEnableData['uid'],
                'postback_enable_checked' => $postEnableChecked,
            ));
        } else {
            $permission_message = Html::site_notification('You have not permission of this section ', 'Warning');
        }

        $active = array(
            'active' => 'setting',
            'active_li' => 'set_postback_sub_li',
        );
        $tplSkeleton = make::tpl('admin/index')->assign($active)->assign(array(
                    'body' => $body,
                    'permission_error_message' => $permission_message,
                    'meta_title' => 'Leads8 | Admin panel | Setting',
                    'meta_keywords' => 'Leads8',
                    'meta_description' => 'Leads8',
                ))->get_content();


        output::as_html($tplSkeleton);
    }

    public function bonus() {
        $brand = config::req('paths');
        $brand_admin_userid = $_SESSION[$brand[0] . '_admin_userid'];
//        $module = $this->arrPaths[2];
//        $section = (isset($this->arrPaths[3])) ? $this->arrPaths[3] : 'edit';
        $brand_uri = config::url() . $this->arrPaths[0] . '/';
        $brnad_slug = $this->arrPaths[0];
        $brand_id = Brands::GetBranduidByBrandslug($brnad_slug);
        $error_message = '';
        $body = '';
        $bonusSettingArr = array();
        $is_enable_setting = '';

        $res = Manage_permission::check_permission_manager($brand_admin_userid, 'setting', 'bonus_setting');
        if (count($res) > 0) {
            if (isset($_POST['bonus_setting'])) {
                if (isset($brand_id) && $brand_id > 0) {
                    $_POST['brand_id'] = $brand_id;
//                echo "<pre>";
//                print_r($_POST);
//                exit;
                    $isSave = Setting::setBrandBonusSetting($_POST);
                    if ($isSave) {
                        Html::set_notification('Bonus setting successfully saved', 'success');
                        output::redirect(Brand::getUrl('setting/bonus'));
                        exit;
                    } else {
                        Html::set_notification('Bonus setting not successfully saved', 'error');
                    }
                } else {
                    Html::set_notification('Something went wrong', 'error');
                }
            }
            
            $languageId = Languages::issetLanguageSession();
            $langTransDataArr = Languages::getLanguageTranslationContent($brand_id, $languageId, 'customers');

            $levelTplArr = array();
            $deposit_selected = '';
            $deposit_buy_selected = 'selected';
            $levelBonusAmountTypeSelected = '';
            if (isset($brand_id) && !empty($brand_id)) {
                $bonusSettingArr = Setting::getBrandBonusSetting($brand_id);
                if (!empty($bonusSettingArr)) {
                    if ($bonusSettingArr['is_enable'] == 1) {
                        $is_enable_setting = 'checked';
                    }
                    $levelPercentageArr = explode(',', $bonusSettingArr['level_percentage']);
                    for ($i = 0; $i < count($levelPercentageArr); $i++) {
                        $index = 'percentage_level' . ($i + 1);
                        $levelTplArr[$index] = $levelPercentageArr[$i];
                    }
                    if(isset($bonusSettingArr['type']) && $bonusSettingArr['type'] == 'deposit'){
                        $deposit_selected = 'selected';
                        $deposit_buy_selected = '';
                    }
                    
                    if($bonusSettingArr['level_bonus_type'] == 'A'){
                        $levelBonusAmountTypeSelected = 'selected';
                    }
                }
            }

            $error_message = Html::get_notification();

            $brandOptionHtml = Brands::select_options($brand_id);
            $body = make::tpl('admin/brand_wise_bonus_setting')
                    ->assign($bonusSettingArr)
                    ->assign($levelTplArr)
                    ->assign($langTransDataArr)
                    ->assign(array(
                        'error_message' => $error_message,
                        'brand_id' => $brand_id,
                        'brand_list' => $brandOptionHtml,
                        'is_enable_setting' => $is_enable_setting,
                        'deposit_selected' => $deposit_selected,
                        'deposit_buy_selected'  => $deposit_buy_selected,
                        'levelBonusAmountTypeSelected' => $levelBonusAmountTypeSelected,
                    ))
                    ->get_content();
        } else {
            $permission_message = Html::site_notification('You have not permission of this section ', 'Warning');
        }
        
        $active = array(
            'dashboard' => '',
            'setting' => 'class="active"',
            'active' => 'setting'
        );

        $tplSkeleton = make::tpl('admin/index')->assign($active)->assign(array(
                    'body' => $body,
                    'permission_error_message' => $permission_message,
                    'meta_title' => 'Lotto | Admin panel | Bonus-Setting',
                    'meta_keywords' => 'Lotto',
                    'meta_description' => 'Lotto',
                ))->get_content();


        output::as_html($tplSkeleton);
    }

    /*
     * setting for column display
     */

    public function column() {
        $brand = config::req('paths');
        $brand_admin_userid = $_SESSION[$brand[0] . '_admin_userid'];
//        $module = $this->arrPaths[2];
//        $section = (isset($this->arrPaths[3])) ? $this->arrPaths[3] : 'edit';
        $brand_uri = config::url() . $this->arrPaths[0] . '/';
        $brnad_slug = $this->arrPaths[0];
        $brand_id = Brands::GetBranduidByBrandslug($brnad_slug);
        $error_message = '';
        $body = '';
        $permission_message = '';

        $res = Manage_permission::check_permission_manager($brand_admin_userid, 'setting', 'column_setting');
        if (count($res) > 0) {
            // For save customers column setting //
            if (isset($_POST['cust_column_save'])) {
                $customer_column_shown_uid = isset($_POST['customer_column_shown_uid']) ? $_POST['customer_column_shown_uid'] : 0;
                $_POST['cust_column'] = isset($_POST['cust_column']) ? $_POST['cust_column'] : array();
                $isSave = Setting::setCustomerColumnSetting($brand_id, $_POST['cust_column'], $customer_column_shown_uid);
                if ($isSave) {
                    Customers::deleteCustomerTableOrder($brand_id);
                    Html::set_notification('Customer column saved successfully', 'success');
                    output::redirect(Brand::getUrl('setting/column'));
                    exit;
                } else {
                    Html::set_notification('Customer column not saved successfully', 'error');
                }
            }
            
            // For save leads column setting //
            if (isset($_POST['lead_column_save'])) {
                $lead_column_shown_uid = isset($_POST['lead_column_shown_uid']) ? $_POST['lead_column_shown_uid'] : 0;
                $_POST['lead_column'] = isset($_POST['lead_column']) ? $_POST['lead_column'] : array();
                $isSave = Setting::setLeadColumnSetting($brand_id, $_POST['lead_column'], $lead_column_shown_uid);
                if ($isSave) {
                    Html::set_notification('Lead column saved successfully', 'success');
                    output::redirect(Brand::getUrl('setting/column'));
                    exit;
                } else {
                    Html::set_notification('Lead column not saved successfully', 'error');
                }
            }
            
            // For customers column manage //
            $customerColumnSettingArr = array();
            $customerColumnSettingId = 0;
            $customerSettingColumnData = Setting::getCustomerColumnSetting($brand_id);
            
            if (!empty($customerSettingColumnData)) {
                $customerColumnSettingArr = json_decode($customerSettingColumnData['option_value'], true);
                $customerColumnSettingId = $customerSettingColumnData['uid'];
            }

            $languageId = Languages::issetLanguageSession();
            $langTransDataArr = Languages::getLanguageTranslationContent($brand_id, $languageId, 'customers');
            
            $customerColumnData = Customers::getColumnData();
            $customerColumnHtml = '';
            if (!empty($customerColumnData)) {
                foreach ($customerColumnData as $column) {
                    $customerColchecked = (in_array($column['slug'], $customerColumnSettingArr) || empty($customerColumnSettingArr)) ? 'checked' : '';

                    $customerColumnHtml .= '<div class="col-md-6">';
                    $customerColumnHtml .= '<div class="form-group">';
                    $customerColumnHtml .= '<input type="checkbox" class="cust_col_check" name="cust_column[]" id="" value="' . $column['slug'] . '" ' . $customerColchecked . '/>' . (($column['translate_keyword'] != '') ? $langTransDataArr[$column['translate_keyword']] : $column['name']);
                    $customerColumnHtml .= '</div>';
                    $customerColumnHtml .= '</div>';
                }
            }
            
            // Customer custom fields /
            $customerCustomColumnHtml = '';
            $customFieldsArr    = Formbuild::getBrandCustomerFormData($brand_id, 1);
            if(!empty($customFieldsArr)){
                foreach($customFieldsArr as $customField){
                    $columnName = 'custom_'.$customField['name'].'_th';
                    $customerColchecked = (in_array($columnName, $customerColumnSettingArr) || empty($customerColumnSettingArr)) ? 'checked' : '';
                    
                    $customerCustomColumnHtml .= '<div class="col-md-6">';
                    $customerCustomColumnHtml .= '<div class="form-group">';
                    $customerCustomColumnHtml .= '<input type="checkbox" class="cust_col_check" name="cust_column[]" id="" value="' . $columnName . '" ' . $customerColchecked . '/>' . $customField['name'];
                    $customerCustomColumnHtml .= '</div>';
                    $customerCustomColumnHtml .= '</div>';
                }
            }
            
            // For leads column manage //
            $leadColumnSettingArr = array();
            $leadColumnSettingId = 0;
            
            $leadSettingColumnData = Setting::getLeadColumnSetting($brand_id);
            if (!empty($leadSettingColumnData)) {
                $leadColumnSettingArr = json_decode($leadSettingColumnData['option_value'], true);
                $leadColumnSettingId = $leadSettingColumnData['uid'];
            }
            
            $langLeadsTransDataArr = Languages::getLanguageTranslationContent($brand_id, $languageId, 'leads');
            $leadColumnData = Leads::getColumnData();
            $leadColumnHtml = '';
            if (!empty($leadColumnData)) {
                foreach ($leadColumnData as $lcolumn) {
                    $leadColchecked = (in_array($lcolumn['slug'], $leadColumnSettingArr) || empty($leadColumnSettingArr)) ? 'checked' : '';
                    
                    $leadColumnHtml .= '<div class="col-md-6">';
                    $leadColumnHtml .= '<div class="form-group">';
                    $leadColumnHtml .= '<input type="checkbox" class="lead_col_check" name="lead_column[]" id="" value="' . $lcolumn['slug'] . '" ' . $leadColchecked . '/>' . (($lcolumn['translate_keyword'] != '') ? $langLeadsTransDataArr[$lcolumn['translate_keyword']] : $lcolumn['name']);
                    $leadColumnHtml .= '</div>';
                    $leadColumnHtml .= '</div>';
                }
            }

            $error_message = Html::get_notification();
            $body = make::tpl('admin/brand_wise_column_setting')
                    ->assign($levelTplArr)
                    ->assign(array(
                        'customerColumnHtml' => $customerColumnHtml,
                        'customerCustomColumnHtml' => $customerCustomColumnHtml,
                        'customerColumnSettingId' => $customerColumnSettingId,
                        'leadColumnHtml' => $leadColumnHtml,
                        'leadColumnSettingId' => $leadColumnSettingId,
                        'error_message' => $error_message
                    ))
                    ->get_content();
        } else {
            $permission_message = Html::site_notification('You have not permission of this section ', 'Warning');
        }

        $active = array(
            'dashboard' => '',
            'setting' => 'class="active"',
            'active' => 'setting',
            'active_li' => 'set_column_sub_li',
        );
        $tplSkeleton = make::tpl('admin/index')->assign($active)->assign(array(
                    'body' => $body,
                    'meta_title' => 'Lotto | Admin panel | Column-Setting',
                    'permission_error_message' => $permission_message,
                    'meta_keywords' => 'Lotto',
                    'meta_description' => 'Lotto',
                ))->get_content();


        output::as_html($tplSkeleton);
    }
    
    public function blocked_ip() {
        $brand = config::req('paths');
        $brand_admin_userid = $_SESSION[$brand[0] . '_admin_userid'];

        $brand_uri = config::url() . $this->arrPaths[0] . '/';
        $brand_slug = $this->arrPaths[0];
        $brand_id = Brands::GetBranduidByBrandslug($brand_slug);

        $body = '';
        $message = '';
        $ipAddress = '';
        $permission_message = '';

        $res = Manage_permission::check_permission_manager($brand_admin_userid, 'setting', 'affil_ip_limit_setting');
        if (count($res) > 0) {
            if (isset($_POST['save_ip'])) {
                $saveIp = Setting::saveBlockedIpAddress($_POST, $brand_id);
                if ($saveIp) {
                    Html::set_notification('Successfully created', 'success');
                    output::redirect($brand_uri . 'admin/setting/blocked_ip/');
                } else {
                    $ipAddress = $_POST['ip'];
                    Html::set_notification('Not successfully update', 'error');
                }
            }
            
            if(isset($_POST['delete_ip'])){
                if(!empty($_POST['delete_ip_checkbox'])){
                    $isDelete = Setting::deleteBlockedIp($brand_id, $_POST['delete_ip_checkbox']);
                    if($isDelete){
                        Html::set_notification('Successfully deleted', 'success');
                        output::redirect($brand_uri . 'admin/setting/blocked_ip/');
                    } else {
                        Html::set_notification('Not successfully deleted', 'error');
                        output::redirect($brand_uri . 'admin/setting/blocked_ip/');
                    }
                } else {
                    Html::set_notification('Please select IP', 'error');
                    output::redirect($brand_uri . 'admin/setting/blocked_ip/');
                }
            }
            
            $message = Html::get_notification();

            $ip_address_checked = '';
            $ip_limitation_id = 0;
            $brandIpAddrHtml = '';
            $brandIpAdrArr = Setting::getAllBlockedIpAddressByBrand($brand_id);

            if (!empty($brandIpAdrArr)) {
                foreach ($brandIpAdrArr as $bIpaddr) {
                    $brandIpAddrHtml .= "<tr>";
                    $brandIpAddrHtml .= "<td><input type='checkbox' name='delete_ip_checkbox[]' value='".$bIpaddr['uid']."' class='ip_check' id='".$bIpaddr['uid']."'/></td>";
                    $brandIpAddrHtml .= "<td>" . $bIpaddr['uid'] . "</td>";
                    $brandIpAddrHtml .= "<td>" . $bIpaddr['ip_address'] . "</td>";
                    $brandIpAddrHtml .= "<td>" . $bIpaddr['note'] . "</td>";
                    $brandIpAddrHtml .= '<td><a data-toggle="modal" href="#myAlert' . $bIpaddr['uid'] . '" onclick="return confirm("Are you sure?")" class="">Delete</a>';
                    $brandIpAddrHtml .= '</td>';
                    $brandIpAddrHtml .= "</tr>";
                    $brandIpAddrHtml .= '<div id="myAlert' . $bIpaddr['uid'] . '" class="modal">';
                    $brandIpAddrHtml .= '<div class="modal-header">';
                    $brandIpAddrHtml .= '<button data-dismiss="modal" class="close" type="button">&times;</button>';
                    $brandIpAddrHtml .= '<h3>Delete "' . $bIpaddr['ip_address'] . '"</h3>';
                    $brandIpAddrHtml .= '</div>';
                    $brandIpAddrHtml .= '<div class="modal-body">';
                    $brandIpAddrHtml .= '<p>Are you sure? You want to delete "' . $bIpaddr['ip_address'] . '" ?</p>';
                    $brandIpAddrHtml .= '</div>';
                    $brandIpAddrHtml .= '<div class="modal-footer">';
                    $brandIpAddrHtml .= '<a class="btn btn-primary" href="' . $brand_uri . 'admin/setting/delete/?del=blocked_ip&id=' . base64_encode($bIpaddr['uid']) . '">Confirm</a>';
                    $brandIpAddrHtml .= '<a data-dismiss="modal" class="btn btn-primary" href="#">Cancel</a>';
                    $brandIpAddrHtml .= '</div>';
                    $brandIpAddrHtml .= '</div>';
                }
            }

            $brandOptionHtml = Brands::select_options($brand_id);
            $body = make::tpl('admin/brand_wise_blocked_ip_setting')
                    ->assign(array(
                        'error_message' => $message,
                        'brand_id' => $_SESSION['brand_id'],
                        'brand_list' => $brandOptionHtml,
                        'brandIpAddrHtml' => $brandIpAddrHtml,
                        'ip_address_checked' => $ip_address_checked,
                    ))
                    ->get_content();
        } else {
            $permission_message = Html::site_notification('You have not permission of this section ', 'Warning');
        }

        $active = array(
            'active' => 'setting',
            'active_li' => 'set_affl_iplmt_sub_li'
        );

        $tplSkeleton = make::tpl('admin/index')->assign($active)->assign(array(
                    'body' => $body,
                    'permission_error_message' => $permission_message,
                    'meta_title' => 'Leads8 | Admin panel | blocked-ip-setting',
                    'meta_keywords' => 'Leads8',
                    'meta_description' => 'Leads8',
                ))->get_content();


        output::as_html($tplSkeleton);
    }
    
    public function platformsmtp() {
        $body = '';
        if (isset($_POST['platformsmtp_setting'])) {
            $isSave = Setting::setPlatformSMTPSetting($_POST);
            if ($isSave) {
                Html::set_notification('Successfully saved', 'success');
                output::redirect(config::admin_url('setting/platformsmtp'));
                exit;
            } else {
                Html::set_notification('Successfully not saved', 'error');
                output::redirect(config::admin_url('setting/platformsmtp'));
                exit;
            }
        }
        
        $platformSmtpData = Setting::getPlatformSMTPSettingData();
        $smtpValue = array();
        if(!empty($platformSmtpData)){
            if(isset($platformSmtpData['option_value']) && $platformSmtpData['option_value'] != ''){
                $smtpValue = json_decode($platformSmtpData['option_value'], true);
            }
        }

        $error_message = Html::get_notification();
        $body = make::tpl('admin/platform_smtp_setting')
                ->assign($postData)
                ->assign(array(
                    'error_message' => $error_message,
                    'platform_smtp_uid' => (isset($platformSmtpData['uid'])) ? $platformSmtpData['uid'] : '',
                    'platform_smtp_host' => $smtpValue['host'],
                    'platform_smtp_port' => $smtpValue['port'],
                    'platform_smtp_username' => $smtpValue['username'],
                    'platform_smtp_password' => $smtpValue['password'],
                    'ssl_selected' => (isset($smtpValue['secure']) && $smtpValue['secure'] == 'ssl') ? 'selected' : '',
        ));

        $active = array(
            'active' => 'setting',
            'active_li' => 'set_pl_smtp_sub_li',
        );
        $tplSkeleton = make::tpl('admin/index')->assign($active)->assign(array(
                    'body' => $body,
                    'meta_title' => 'Leads8 | Admin panel | Setting',
                    'meta_keywords' => 'Leads8',
                    'meta_description' => 'Leads8',
                ))->get_content();


        output::as_html($tplSkeleton);
    }
    
    public function call_api() {
        $brand = config::req('paths');
        $brand_admin_userid = $_SESSION[$brand[0] . '_admin_userid'];

        $brand_uri = config::url() . $this->arrPaths[0] . '/';
        $brand_slug = $this->arrPaths[0];
        $brand_id = Brands::GetBranduidByBrandslug($brand_slug);

        $body = '';
        $message = '';
        $permission_message = '';

//        $res = Manage_permission::check_permission_manager($brand_admin_userid, 'setting', 'call_api_setting');
//        if (count($res) > 0) {
            if (isset($_POST['voiso_setting'])) {
                if(isset($_POST['voiso_api_key']) && $_POST['voiso_api_key'] != '' && isset($_POST['voiso_extension']) && $_POST['voiso_extension'] != ''){
                    if(!Setting::callApiTokenExist($brand_id, $brand_admin_userid, $_POST['voiso_api_key'])){
                        $saveCallApi = Setting::saveVoisoCallApiSetting($brand_id, $brand_admin_userid, $_POST);
                        if ($saveCallApi) {
                            Html::set_notification('Successfully saved', 'success');
                            output::redirect($brand_uri . 'admin/setting/call_api/');
                        } else {
                            Html::set_notification('Not successfully saved', 'error');
                        }                
                    } else {
                        Html::set_notification('Token already exist', 'error');
                    }
                } else {
                    Html::set_notification('Please fill up required fields.', 'error');
                }
            }
            
            $callSettingData = Setting::getAllCallApiSetting($brand_id);
            
            $message = Html::get_notification();

            
            $body = make::tpl('admin/call_api_setting')
                    ->assign(array(
                        'error_message' => $message,
                        'voiso_uid' => isset($callSettingData['voiso']['uid']) ? $callSettingData['voiso']['uid'] : '',
                        'voiso_api_key' => isset($callSettingData['voiso']['api_token']) ? $callSettingData['voiso']['api_token'] : '',
                        'voiso_extension' => isset($callSettingData['voiso']['extension_id']) ? $callSettingData['voiso']['extension_id'] : '',
                        'voiso_enabled_checked' => (isset($callSettingData['voiso']['is_enabled']) && $callSettingData['voiso']['is_enabled'] == 1) ? 'checked' : ''
                    ))
                    ->get_content();
//        } else {
//            $permission_message = Html::site_notification('You have not permission of this section ', 'Warning');
//        }

        $active = array(
            'active' => 'setting',
            'active_li' => 'call_api_sub_li'
        );

        $tplSkeleton = make::tpl('admin/index')->assign($active)->assign(array(
                    'body' => $body,
                    'permission_error_message' => $permission_message,
                    'meta_title' => 'Leads8 | Admin panel | blocked-ip-setting',
                    'meta_keywords' => 'Leads8',
                    'meta_description' => 'Leads8',
                ))->get_content();


        output::as_html($tplSkeleton);
    }

    public function makeApi(){
        $brand = config::req('paths');
        $brand_admin_userid = $_SESSION[$brand[0] . '_admin_userid'];

        $brand_uri = config::url() . $this->arrPaths[0] . '/';
        $brand_slug = $this->arrPaths[0];
        $brandId = Brands::GetBranduidByBrandslug($brand_slug);
        $accesstoken = Brands::getAccessTokenByBrandId($brandId);
        $md5AccessToken = md5($accesstoken);

        $body = '';
        $message = '';
        $permission_message = '';

       $res = Manage_permission::check_permission_manager($brand_admin_userid, 'setting', 'make_api_setting');
       if (count($res) > 0) {           
            
            $message = Html::get_notification();
            
            $body = make::tpl('admin/make_api_setting')
                    ->assign(array(
                        'error_message' => $message,
                        'brandId' => $brandId,
                        'api_token' => $md5AccessToken
                    ))
                    ->get_content();
       } else {
           $permission_message = Html::site_notification('You have not permission of this section ', 'Warning');
       }

        $active = array(
            'active' => 'setting',
            'active_li' => 'call_api_sub_li'
        );

        $tplSkeleton = make::tpl('admin/index')->assign($active)->assign(array(
                    'body' => $body,
                    'permission_error_message' => $permission_message,
                    'meta_title' => 'Leads8 | Admin panel | make-ip-setting',
                    'meta_keywords' => 'Leads8',
                    'meta_description' => 'Leads8',
                ))->get_content();


        output::as_html($tplSkeleton);
    }

}
