
const forex_socket = new WebSocket("wss:" + "//" + "finnhub.io" + "/ws");
let forexTickerInfos = [];
$(document).ready(function(){    
    forex_socket.onopen = function(t) {
        console.log("socketReady");
    }, onMessageForex()
    
});


function onMessageForex(){
    // console.log('onMessageForex:');
    forex_socket.onmessage = function(e) {
        // console.log('e.data:'+e.data);
        var data = JSON.parse(e.data),
            type = data.type,
            content = data.content;

        //    console.log("data", data);
        switch (type) {
            case 51:
                let splitArr = content.displayName.split("/");
                forexTickerInfos[content.ticker] = {
                        symbol:splitArr[0],
                        currency:splitArr[1],
                        price: 0,
                        prevClose: content.prevClose,
                        displayName: content.displayName,
                        increase: !0,
                        changePercentage: 0,
                        changeRelative: 0
                    }
                break;
            case 52:
                var a = content.ticker,
                        r = content.price,
                        i = r - forexTickerInfos[a].prevClose,
                        o = i / forexTickerInfos[a].prevClose * 100,
                        s = i > 0;
                
                        forexTickerInfos[content.ticker].price = r;
                        forexTickerInfos[content.ticker].changeRelative = i;
                        forexTickerInfos[content.ticker].changePercentage = o;
                        forexTickerInfos[content.ticker].increase = s;
                //t.props.onForexTickerUpdatePrice(r)
        }
        // console.log("forexTickerInfos", forexTickerInfos);
        onForexTickerUpdatePrice();
    }
}

function onForexTickerUpdatePrice(){
//    if(forexTickerInfos != NULL){
//    alert('test');
//    console.log("forexTickerInfos", forexTickerInfos);
    for (const [key, value] of Object.entries(forexTickerInfos)) {
        var market = 'FOREX';
        var currency = value.currency;
        var symbol = value.symbol;
//        console.log("[kr-watchinglistpair='" + market + ":" + symbol + "/" + currency + "']");
        let wItem = $("[kr-watchinglistpair='" + market + ":" + symbol + "/" + currency + "']");
        wItem.find('.kr-watchinglistpair-price').html(value.price.toFixed(4));        
        wItem.find('.kr-watchinglistpair-evolv').html(value.changePercentage.toFixed(2)+'%');
        if(value.increase){
            wItem.find('.kr-watchinglistpair-evolv').css('color', 'green');
        } else {
            wItem.find('.kr-watchinglistpair-evolv').css('color', 'red');
        }
        if($('.kr-top-graphlist-item[symbol="' + symbol + '"][currency="' + currency + '"]').length){
            $('.kr-top-graphlist-item[symbol="' + symbol + '"][currency="' + currency + '"]').find('[kr-data="CHANGE24HOURPCT"]').html(value.changePercentage.toFixed(2)+'%');
            if(value.increase){
                $('.kr-top-graphlist-item[symbol="' + symbol + '"][currency="' + currency + '"]').find('[kr-data="CHANGE24HOURPCT"]').addClass('kr-top-graphlist-item-evl-up');
                $('.kr-top-graphlist-item[symbol="' + symbol + '"][currency="' + currency + '"]').find('[kr-data="CHANGE24HOURPCT"]').removeClass('kr-top-graphlist-item-evl-down');
            } else {
                $('.kr-top-graphlist-item[symbol="' + symbol + '"][currency="' + currency + '"]').find('[kr-data="CHANGE24HOURPCT"]').removeClass('kr-top-graphlist-item-evl-up');
                $('.kr-top-graphlist-item[symbol="' + symbol + '"][currency="' + currency + '"]').find('[kr-data="CHANGE24HOURPCT"]').addClass('kr-top-graphlist-item-evl-down');
            }
        }
        if($('.left_info_'+symbol+currency).length){
            $('.left_info_'+symbol+currency).find('.kr-infoscurrencylf-price-cp_stock').html(value.price.toFixed(2));
            $('.left_info_'+symbol+currency).find('.kr-infoscurrencylf-price-evolv').html(value.changeRelative.toFixed(2) + " ("+value.changePercentage.toFixed(2)+'%'+")");
            if(value.increase){
                $('.left_info_'+symbol+currency).find('.kr-infoscurrencylf-price-evolv').css('color', 'green');
            } else {
                $('.left_info_'+symbol+currency).find('.kr-infoscurrencylf-price-evolv').css('color', 'red');
            }
        }
    }
//        forexTickerInfos.forEach(function(ticker){
//            alert(ticker.price);
//            var market = 'US_STOCK';
//            var currency = 'USDT';
//            var symbol = ticker.symbol;
//            console.log("[kr-watchinglistpair='" + market + ":" + symbol + "/" + currency + "']");
//            let wItem = $("[kr-watchinglistpair='" + market + ":" + symbol + "/" + currency + "']");
//            wItem.find('.kr-watchinglistpair-price').html(ticker.price);
//            wItem.find('.kr-watchinglistpair-evolv').html(ticker.changePercentage.toFixed(2));
//        })
//    }
}

function addForexSubscribtion(symbol){
    // Connection opened -> Subscribe
    symbol = symbol;
    forex_socket.send(JSON.stringify({
        type: 50,
        ticker: symbol
    }));
    onMessageForex();
}


////const forex_socket = new WebSocket('wss://ws.finnhub.io?token=c297cgqad3iac5lei8g0');
//$(document).ready(function(){
////    addStockSubscribtion('AAPL');
//    // Listen for messages
//    forex_socket.addEventListener('message', function (event) {
//        console.log('Message from server ', event.data);
//    });
//});
//
///**
// * Add subscription
// * @param  {String} symbol   Symbol (ex : AAPL)
// */
// function addForexSubscribtion(symbol){
//    // Connection opened -> Subscribe
//    console.log(symbol);
//    forex_socket.addEventListener('open', function (event) {
//        forex_socket.send(JSON.stringify({'type':'subscribe', 'symbol': symbol}))
// //        forex_socket.send(JSON.stringify({'type':'subscribe', 'symbol': 'BINANCE:BTCUSDT'}))
// //        forex_socket.send(JSON.stringify({'type':'subscribe', 'symbol': 'IC MARKETS:1'}))
//    });
// }
//
///**
// * Delete subscription
// * @param  {String} symbol   Symbol (ex : BTC)
// * @param  {String} currency Currency (ex : USD)
// * @param  {Number} [type=5] Type subscription
// */
//function deleteStockSubscription(symbol){
//    // Unsubscribe
//    var unsubscribe = function(symbol) {
//       forex_socket.send(JSON.stringify({'type':'unsubscribe','symbol': symbol}))
//   }
//}