<?php
/**
 * WatchingList class
 *
 * @package Krypto
 * @author Ovrley <hello@ovrley.com>
 */
class StockWatchingList extends MySQL {

  /**
   * User object
   * @var User
   */
  private $user = null;

  /**
   * CryptoApi object
   * @var CryptoApi
   */
  private $CryptoApi = null;

  /**
   * WatchingList constructor
   * @param CryptoApi $CryptoApi CryptoApi object
   * @param User $user           User object
   */
  public function __construct($CryptoApi, $user){
    $this->user = $user;
    $this->CryptoApi = $CryptoApi;
  }

  /**
   * Get user object
   * @return User User associate to the watching list
   */
  public function _getUser(){
    return $this->user;
  }

  /**
   * Get crypto api
   * @return CryptoApi CryptoApi associate to the watching list
   */
  public function _getCryptoApi(){
    return $this->CryptoApi;
  }

  /**
   * Get list coins
   * @return Array CryptoCoin Array
   */
  public function _getListsStock(){
    $resCoin = [];
    $r = parent::querySqlRequest("SELECT * FROM stock_watching_krypto WHERE id_user=:id_user", ['id_user' => $this->_getUser()->_getUserID()]);
    if(count($r) > 0){
        // Fetch list coins in database
        foreach ($r as $key => $itemWatching) {
          $resCoin[$itemWatching['symbol'].'/'.$itemWatching['currency']] = [
            'symbol' => $itemWatching['symbol'],
            'currency' => $itemWatching['currency'],
            'market' => $itemWatching['market']
          ];
        }
    } else {
        $r = parent::querySqlRequest("SELECT symbol FROM stocklist_krypto WHERE status=1 AND symbol != 'USDT' LIMIT 10");
        if(count($r) > 0){
            foreach ($r as $key => $itemWatching) {
                $resCoin[$itemWatching['symbol'].'/USDT'] = [
                  'symbol' => $itemWatching['symbol'],
                  'currency' => 'USDT',
                  'market' => 'US_STOCK'
                ];
            }
        }
    }
    return $resCoin;

  }

  /**
   * Remove watching list item
   * @param  String $symbol Symbol item (ex : BTC)
   * @return Boolean
   */
  public function _removeItem($symbol, $currency, $market = "US_STOCK"){

    // Delete item in Database
    $r = parent::execSqlRequest("DELETE FROM stock_watching_krypto WHERE symbol=:symbol AND currency=:currency AND market=:market AND id_user=:id_user", [
                                'symbol' => $symbol,
                                'currency' => $currency,
                                'id_user' => $this->_getUser()->_getUserID(),
                                'market' => $market
                              ]);

    // Check delete status
    if(!$r) throw new Exception("Error : Fail to delete watching list item (SQL Error)", 1);
    return true;
  }

  /**
   * Add watching list item
   * @param String $symbol Symbol item (ex : BTC)
   */
  public function _addItem($symbol, $currency = null, $market = "US_STOCK"){

    if(is_null($currency)) $currency = $this->_getCryptoApi()->_getCurrencyFullName();

    // Check if item is alreayd in watching list
    $r = parent::querySqlRequest("SELECT * FROM stock_watching_krypto WHERE symbol=:symbol AND currency=:currency AND market=:market AND id_user=:id_user", [
                                'symbol' => $symbol,
                                'id_user' => $this->_getUser()->_getUserID(),
                                'market' => $market,
                                'currency' => $currency
                              ]);

    // If item not exist in watching list
    if(count($r) == 0){

      // Add item in watching list in database
      $s = parent::execSqlRequest("INSERT INTO stock_watching_krypto (symbol, id_user, currency, market) VALUES (:symbol, :id_user, :currency, :market)",
                                  [
                                    'symbol' => $symbol,
                                    'id_user' => $this->_getUser()->_getUserID(),
                                    'market' => $market,
                                    'currency' => $currency
                                  ]);

      // Check insert sql status
      if(!$s) throw new Exception("Error : Fail to add to watching list (SQL Error)", 1);
     }

  }

  public function _symbolExist($symbol, $currency){
    $r = parent::querySqlRequest("SELECT * FROM stock_watching_krypto WHERE symbol=:symbol AND currency=:currency AND id_user=:id_user", [
                                'symbol' => $symbol,
                                'currency' => $currency,
                                'id_user' => $this->_getUser()->_getUserID()
                              ]);
    return count($r) > 0;
  }

}

?>
