<?php

session_start();

require "../../../../config/config.settings.php";

require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/vendor/autoload.php";

require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/MySQL/MySQL.php";

require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/User/User.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/Lang/Lang.php";

require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/App/App.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/App/AppModule.php";

require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoIndicators.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoGraph.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoHisto.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoCoin.php";
require $_SERVER['DOCUMENT_ROOT'].FILE_PATH."/app/src/CryptoApi/CryptoApi.php";

$App = new App(true);
$App->_loadModulesControllers();

$User = new User();
if(!$User->_isLogged()) die('Error : User not logged');

$Lang = new Lang($User->_getLang(), $App);

try {
  // Init CryptoApi object
  //
  $Balance = new Balance($User, $App);
  $Balance = $Balance->_getCurrentBalance();

  $Manager = new Manager($App);

} catch (\Exception $e) {
  die($e->getMessage());
}

?>

<section class="kr-balance-view">
  <header>
    <h2><?php echo $Lang->tr('Transactions history'); ?></h2>
    <div>
      <div>
        <input type="text" class="kr-history-view-search" name="" value="" placeholder="<?php //echo $Lang->tr('Reference'); ?> ...">
      </div>
    </div>
  </header>
    
    <style>
        table {
            width: 100%;
        }
        .kr-marketlist-item td{
            padding: 10px;
        }
        .kr-marketlist-item .amount {
            text-align: right;
        }
        .kr-marketlist th {
            height: 38px;
            min-height: 38px;
            border-top: 1px solid #171c29;
            color: #fff; 
            cursor: pointer;
            background: #151a25;
        }
        .kr-marketlist td {
            height: 38px;
            min-height: 38px;
            /*border-top: 1px solid #171c29;*/
            color: #fff; 
            cursor: pointer;
            background: #2e3748 !important;
        }
    </style>
    
    
    
    
    <table class="kr-marketlist">
        <tr class="kr-marketlist-header">
            <th><div class="kr-marketlist-n"></div></th>
            <th><div class="kr-mono"><span><?php echo $Lang->tr('Type'); ?></span></div></th>
            <th><div class="kr-mono"><span><?php echo $Lang->tr('Date'); ?></span></div></th>
            <th><div class="kr-mono"><span><?php echo $Lang->tr('Method'); ?></span></div></th>
            <!--<div class="kr-mono"><span><?php echo $Lang->tr('Status'); ?></span></div>-->
            <th><div class="kr-mono"><span><?php echo $Lang->tr('Amount'); ?></span></div></th>
            <th><div class="kr-mono"><span><?php echo $Lang->tr('Fees'); ?></span></div></th>
            <th><div class="kr-mono"><span><?php echo $Lang->tr('Received'); ?></span></div></th>
            <?php
            if($_SESSION['is_manager_login'] == true){      
            ?>
            <th><div class="kr-mono"><span><?php echo $Lang->tr('Action'); ?></span></div></th>
            <?php
            }
            ?>
        </tr>

        <?php
        $TransactionsHistory = $Balance->_getTransactionsHistory();
        $paymentTypeArr = array('deposit', 'refund', 'bonus', 'revenue');
        foreach ($TransactionsHistory as $dataHisto) {
            //$dataHisto['type_histo'] = ($dataHisto['type_histo'] == 'bonus') ? 'deposit' : $dataHisto['type_histo'];
            
            $typeName = $dataHisto['type_histo'];
            if($dataHisto['type_histo'] == 'bonus'){
                $typeName = 'Bonus/Credit Line';
            }
            
            $currenyDecimal = 8;
            ?>
            <tr class="kr-marketlist-item kr-balanceitem-cv" kr-history-ref="<?php echo $dataHisto['ref']; ?>" kr-history-type="<?php echo $dataHisto['type_histo']; ?>">
                <td>
                    <div class="kr-marketlist-n">
                        <div class="kr-marketlist-n-nn">
                            <label class="kr-mono">
                                <?php
                                if($_SESSION['is_manager_login'] == true){
                                    $backgroundCss = ($dataHisto['is_hide'] == 1) ? 'background-color: rgb(255, 0, 0); border: 1px solid red;' : 'background-color: transparent; border: transparent;'
                                ?>
                                <span class="dot_label_<?= $dataHisto['id_histo'].'_'.$dataHisto['table_histo'] ?>" style="width: 10px; height: 10px; <?= $backgroundCss ?> margin-right: 5px; border-radius: 100%; display: table-row; margin-left: 4px;">&nbsp;</span>
                                <?php
                                }
                                ?>
                                <b><?php echo $dataHisto['ref']; ?></b>
                            </label>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="kr-mono">
                        <span style="color:<?php echo (in_array($dataHisto['type_histo'], $paymentTypeArr) ? "#29c359" : "#f14700"); ?>;"><?php echo (in_array($dataHisto['type_histo'], $paymentTypeArr) ? "&#9662;" : "&#9652") . '&nbsp;&nbsp;' . strtoupper($Lang->tr($typeName)); ?></span>
                    </div>
                </td>
                <td>
                    <div class="kr-mono">
                        <span><?php echo date('d/m/Y H:i:s', $dataHisto['date_histo']); ?></span>
                    </div>
                </td>
                <td>
                    <div class="kr-mono">
                        <span><?php echo $dataHisto['method']; ?></span>
                    </div>
                </td>
                <!--<div class="kr-mono">
                <?php
                if (in_array($dataHisto['type_histo'], $paymentTypeArr)):
                    if ($dataHisto['status'] == 0)
                        echo '<span class="kr-admin-lst-c-status kr-admin-lst-tag kr-admin-lst-tag-red">' . $Lang->tr($Manager->_getPaymentStatus($dataHisto['status'])) . '</span>';
                    if ($App->_getPaymentApproveNeeded()) {
                        if ($dataHisto['status'] == 1)
                            echo '<span class="kr-admin-lst-c-status kr-admin-lst-tag kr-admin-lst-tag-orange">' . $Lang->tr($Manager->_getPaymentStatus($dataHisto['status'])) . '</span>';
                        if ($dataHisto['status'] == 2)
                            echo '<span class="kr-admin-lst-c-status kr-admin-lst-tag kr-admin-lst-tag-green">' . $Lang->tr($Manager->_getPaymentStatus($dataHisto['status'])) . '</span>';
                    } else {
                        if ($dataHisto['status'] == 1)
                            echo '<span class="kr-admin-lst-c-status kr-admin-lst-tag kr-admin-lst-tag-green">' . $Lang->tr($Manager->_getPaymentStatus($dataHisto['status'])) . '</span>';
                    }
                else:
                    if ($dataHisto['status'] == 2):
                        ?>
                           <span class="kr-admin-lst-tag kr-admin-lst-tag-green"><?php //echo $Lang->tr('Done'); ?></span>
                
                    <?php elseif ($dataHisto['status'] == -1):
                        ?>
                             <span class="kr-admin-lst-tag kr-admin-lst-tag-grey"><?php //echo $Lang->tr('Canceled'); ?></span>
                    <?php else: ?>
                           <span class="kr-admin-lst-tag kr-admin-lst-tag-red"><?php //echo $Lang->tr('Not confirmed'); ?></span>
        <?php
        endif;

    endif;
    ?>
                </div>-->
                <td class="amount">
                    <div class="kr-mono">
                        <span><?php echo $App->_formatNumber($dataHisto['amount_histo'] + $dataHisto['fees'], $currenyDecimal) . ' ' . $dataHisto['currency']; ?></span>
                    </div>
                </td>
                <td class="amount">
                    <div class="kr-mono">
                        <span><?php echo $App->_formatNumber($dataHisto['fees'], $currenyDecimal) . ' ' . $dataHisto['currency']; ?></span>
                    </div>
                </td>
                <td class="amount">
                    <div class="kr-mono">
                        <span><b><?php echo $App->_formatNumber($dataHisto['amount_histo'], $currenyDecimal) . ' ' . $dataHisto['currency']; ?></b></span>
                    </div>
                </td>
                
                <?php
                if($_SESSION['is_manager_login'] == true){      
                    $btnName = ($dataHisto['is_hide'] == 1) ? "Show" : "Hide";
                ?>
                <td class="">
                    <div class="kr-mono">
                        <input type="button" class="btn btn-small btn-autowidth hide_show_trans_btn_<?= $dataHisto['id_histo'].'_'.$dataHisto['table_histo'] ?>" onclick="_hideShowTransaction('<?php echo $dataHisto['id_histo']; ?>', '<?php echo $dataHisto['table_histo']; ?>');" name="" value="<?= $btnName; ?>">
                    </div>
                </td>
                <?php
                }
                ?>
                
            </tr>
    <?php
}
?>
    </table>
</section>
