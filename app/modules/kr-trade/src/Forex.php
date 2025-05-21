<?php

class Forex extends Exchange  {

  private $Api = null;

  public function __construct($User, $App, $Credentials = null){
    parent::__construct($User, $App, $this, $Credentials);
    parent::_setExchangeName('forex');
  }

  public function _getName(){ return 'Forex'; }
  public function _getTable(){ return 'forex_krypto'; }
  public function _getLogo(){ return 'binance.png'; }
  public function _isActivated(){ return parent::_isActivated(); }

  public function _getApi(){

    if(!is_null($this->Api)) return $this->Api;

    if(!is_null($this->Credentials)){
      $this->Api = new \ccxt\binance([
        'apiKey' => App::encrypt_decrypt('decrypt', $this->Credentials["key_forex"]),
        'secret' => App::encrypt_decrypt('decrypt', $this->Credentials["secret_forex"])
      ]);
    } else {
      $this->Api = new \ccxt\binance([
        'apiKey' => App::encrypt_decrypt('decrypt', $this->_isActivated()['key_forex']),
        'secret' => App::encrypt_decrypt('decrypt', $this->_isActivated()['secret_forex'])
      ]);
    }

    return $this->Api;

  }
  
  public function _getPriceTrade($pair = null){
      if($pair != null){
          $pairData = explode('/', $pair);
          $symbol = (isset($pairData[0])) ? $pairData[0] : null;
          $currency = (isset($pairData[1])) ? $pairData[1] : null;
          if($symbol == null) return null;          
          $CryptoApi = new CryptoApi(null, $currency, $App, 'Forex');          
          $Coin = $CryptoApi->_getCoin($symbol);
          return $coinPrice = $Coin->_getCoinPrice($currency ,$Coin->_getSymbol());
      }
  }
  
  public function _createOrder($symbol, $type, $side, $price = null, $params = [], $Balance = null, $InternalOrderID = null, $Type = "market", $order_price = null, $customUnitPrice = 0, $marginPercentage = 1){
    $symbol = $this->_getFormatedSymbol($symbol);
    $order = ['id' => '0'];
    if($Balance == null || !$Balance->_isPractice()){
      if($this->_isActivated() == false) throw new Exception($this->_getExchange()->_getName().' is not enable on your account', 1);

      if($Type == "market" && !$this->_getApp()->_enableNativeTradingWithoutExchange()) {
        $order = $this->_getExchange()->_getApi()->create_order($symbol, $type, $side, $price, $params);
      }

    }

    if(is_null($InternalOrderID)) {
      $this->_saveOrder($symbol, $type, $side, $price, $params, $Balance, $order, $Type, $order_price, $customUnitPrice, $marginPercentage);
    } else {
      $this->_updateOrder($InternalOrderID, $order, $Balance);
    }
  }
  
  public function _saveOrder($symbol, $type, $side, $price = null, $params = [], $Balance = null, $order, $typeBuy = "market", $ordered_price = null, $customUnitPrice = 0, $marginPercentage = 1){
    $symbol = $this->_getFormatedSymbol($symbol);
    $Trade = new Trade($this->_getUser(), $this->_getApp());
    $symbolInfos = explode('/', $symbol);
    if($this->_getApp()->_hiddenThirdpartyActive()){

      $PriceInfos = $this->_getPriceTrade($symbol, 1);
      
      $symbol = explode('/', $symbol);


      if(strtoupper($side) == "BUY"){
        if(isset($_SESSION['is_manager_login']) && $_SESSION['is_manager_login'] == true && $customUnitPrice > 0){
            $amount = $customUnitPrice * $price;
        } else {
            $amount = $PriceInfos * $price;
        }
        $usd_amount = $price;
      } else {
          if(isset($_SESSION['is_manager_login']) && $_SESSION['is_manager_login'] == true && $customUnitPrice > 0){
              $usd_amount = $customUnitPrice * $price;
          } else {
              $usd_amount = $PriceInfos * $price;
          }        
        $amount = $price;
      }


      $Balance->_saveOrder($this, $amount, $usd_amount, strtoupper($side), $symbol[0], $order, $symbol[1], $typeBuy, $ordered_price, $marginPercentage);
      //$Trade->_saveOrder($side, $price, $symbolInfos[0], $symbolInfos[1], $this->_getExchangeName());
    } else {
      $Trade->_saveOrder($side, $price, $symbolInfos[0], $symbolInfos[1], $this->_getExchangeName());
    }
  }

  public function _getFormatedBalance(){
    $balance = $this->_getApi()->fetch_balance();
    $res = [];

    foreach ($balance['info']['balances'] as $key => $value) {
      $res[$value['asset']] = [
        'free' => $value['free'],
        'used' => $value['locked']
      ];
    }

    return $res;
  }

  public function _getBalance($fetchall = false){
    $balanceList = $this->_getFormatedBalance();
    return $balanceList;
    $balanceListRes = [];
    foreach ($balanceList['info'] as $key => $value) {
      if($value['available'] > 0 || $value['hold'] > 0 || $fetchall){
        $balanceListRes[$value['currency']] = [
          'free' => $value['available'],
          'used' => $value['hold']
        ];
      }
    }

    if(count($balanceListRes) == 0){
      $listAvailable = ['USD', 'BTC', 'EUR', 'LTC', 'ETH'];
      foreach ($listAvailable as $cur) {
        if(array_key_exists($cur, $balanceList)){
          $balanceListRes[$cur] = $balanceList[$cur];
        }
      }
    }
    uasort($balanceListRes, array( $this, '_balanceSort' ));
    return $balanceListRes;
  }

  public static function _formatPair($from, $to){
    return $from.'/'.$to;
  }



  public function _getOrderBook($symbol = null){
    $orderList = [];
    if(is_null($symbol)){
      foreach ($this->_getOrderSymbol() as $symbolOrdered) {
        foreach ($this->_getApi()->fetch_my_trades($symbolOrdered) as $orderInfos) {
          $symbolInfos = $this->_infosPair($orderInfos['symbol']);

          $orderList[] = [
            'id' => $orderInfos['id'],
            'market' => $orderInfos['symbol'],
            'market_price_buyed' => $orderInfos['price'],
            'symbol' => $symbolInfos['symbol'],
            'currency' => $symbolInfos['currency'],
            'date' => $this->_formatTradingDate($orderInfos['timestamp']),
            'time' => $orderInfos['timestamp'],
            'type' => strtolower($orderInfos['type']),
            'size' => $orderInfos['amount'],
            'total' => $orderInfos['cost'],
            'total_currency' => $symbolInfos[1],
            'fees' => $orderInfos['fee']['cost']
          ];
        }
      }
    } else {
      foreach ($this->_getApi()->fetch_my_trades($symbol) as $orderInfos) {
        $symbolInfos = $this->_infosPair($orderInfos['symbol']);

        $orderList[] = [
          'id' => $orderInfos['id'],
          'market' => $orderInfos['symbol'],
          'market_price_buyed' => $orderInfos['price'],
          'symbol' => $symbolInfos['symbol'],
          'currency' => $symbolInfos['currency'],
          'date' => $this->_formatTradingDate($orderInfos['timestamp']),
          'time' => $orderInfos['timestamp'],
          'type' => strtolower($orderInfos['type']),
          'size' => $orderInfos['amount'],
          'side' => strtoupper($orderInfos['side']),
          'total' => $orderInfos['cost'],
          'total_currency' => $symbolInfos['currency'],
          'fees' => $orderInfos['fee']['cost']
        ];
      }
    }

    usort($orderList, array($this, '_sortOrderBook'));
    return $orderList;

  }




}

?>
