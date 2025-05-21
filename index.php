<?php
session_start();
require "config/config.settings.php";
require "vendor/autoload.php";
require "app/src/MySQL/MySQL.php";
require "app/src/App/App.php";
require "app/src/App/AppModule.php";
require "app/src/User/User.php";
require "app/src/Lang/Lang.php";

// Load modules & check domain
$App = new App(true);
$App->_checkDomain();
$App->_loadModulesControllers();

try {
    
    // Check if user is already logged
    $User = new User();
    if ($User->_isLogged())
        header('Location: ' . APP_URL . '/dashboard' . ($App->_rewriteDashBoardName() ? '' : '.php'));
    
    if(isset($_REQUEST['refer_id']) && $_REQUEST['refer_id'] > 0){
        $_SESSION['refer_id'] = $_REQUEST['refer_id'];
    }
    if(isset($_REQUEST['aid']) && $_REQUEST['aid'] > 0){
        $_SESSION['affil_id'] = $_REQUEST['aid'];
    }
if(isset($_REQUEST['mid']) && $_REQUEST['mid'] > 0){
        $_SESSION['mid'] = $_REQUEST['mid'];
    }
    if(isset($_REQUEST['code']) && $_REQUEST['code'] != ''){
        $_SESSION['code'] = $_REQUEST['code'];
    }

    // Init lang object
    $Lang = new Lang(null, $App);

    if (!empty($_GET) && isset($_GET['lng']) && !empty($_GET['lng'])) {
        $Lang->setLangCookie($_GET['lng']);
    }

    if ($App->_enableGooglOauth()) {
        $GoogleOauth = new GoogleOauth($User);
    }

    $App->_checkReferalSource();
} catch (Exception $e) {
    define('ERROR_SOFTWARE', $e->getMessage());
}

$ch =  curl_init("https://min-api.cryptocompare.com/data/v2/news/?lang=EN");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
curl_setopt($ch, CURLOPT_ENCODING,  '');
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
$cryptoData = json_decode(curl_exec($ch), true);
$cryptoNewsData = (isset($cryptoData['Data']) && !empty($cryptoData['Data'])) ? $cryptoData['Data'] : array();
curl_close($ch);
?>

<!doctype html>
<html lang="en">

<head>
    <title>infin8fx - Crypto Currency</title>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Favicon -->
    <!-- <link rel="apple-touch-icon" sizes="152x152" href="assets/img/icons/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="assets/img/icons/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/img/icons/favicon/favicon-16x16.png"> -->
    <link rel="icon" type="image/png" sizes="16x16" href="assets/img/icons/favicon/favicon.ico">
    <link rel="manifest" href="assets/img/icons/favicon/site.webmanifest">
    <link rel="mask-icon" href="assets/img/icons/favicon/safari-pinned-tab.svg" color="#5bbad5">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=PT+Sans&Ubuntu:400,500,700" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
    <!-- Magnific Popup CSS -->
    <link rel="stylesheet" type="text/css" href="css/magnific-popup/magnific-popup.css" />
    <!-- owl-carousel CSS -->
    <link rel="stylesheet" type="text/css" href="css/owl-carousel/owl.carousel.css" />
    <!-- Animate CSS -->
    <link rel="stylesheet" type="text/css" href="css/animate.css" />
    <!-- Font Awesome -->
    <link rel="stylesheet" type="text/css" href="css/font-awesome.css" />
    <!-- Ionicons CSS -->
    <link rel="stylesheet" type="text/css" href="css/ionicons.min.css">
    <!-- Flaticon CSS -->
    <link rel="stylesheet" type="text/css" href="css/flaticon.css">
    <!-- Shop CSS -->
    <link rel="stylesheet" type="text/css" href="css/shop.css">
    <!-- REVOLUTION STYLE SHEETS -->
    <link rel="stylesheet" type="text/css" href="revslider/css/settings.css">
    <!-- style CSS -->
    <link rel="stylesheet" type="text/css" href="css/style.css?v=1.0">
    <!-- Responsive CSS -->
    <link rel="stylesheet" type="text/css" href="css/responsive.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" type="text/css" href="css/custom.css">
     

</head>

<body>
    <!-- loading -->
    <div id="loading">
        <div id="loading-center">
            <!-- <img src="images/loader.gif" alt="loder"> -->
            <img src="images/logo.png" alt="loder">
        </div>
    </div>
    <!-- loading End -->
    <!-- Header -->
    <header class="simpal-yellow">
        <div id="home" class="topbar">
            <div class="container">
                <div class="row">
                    
                </div>
            </div>
        </div>
        <div class="iq-header">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="logo">
                            <a href="#home"><img id="logo_dark" class="img-fluid" src="images/logo.png" alt="logo"></a>
                        </div>
                        <nav> <a id="resp-menu" class="responsive-menu" href="javascript:void(0)"><i class="fa fa-reorder"></i> Menu</a>
                            <ul class="menu text-right">
                                <li><a href="#home">Home</a>
								</li>
								<li><a href="#about">About</a>
								</li>
								<li><a href="#steps">Steps</a>
								</li>
								<!--
								<li><a class="active" href="steps">How it works</a>
								</li>
								-->
								<li><a href="#payments">Payments</a>
								</li>
                                <!-- <li><a href="#recovery">Recovery</a>
                                </li> -->
								<li><a href="#news">News</a>
								</li>
								<li><a href="#contact">Contact</a>
								</li>
								<li><a href="login.php"><b><font color=#505cab>Login</font></b></a>
								</li>
								<li>
                                <div class="multilanguageDiv" style="width:58px;">
                                    <div class="flagBtnDiv">
                                        <button href="#" onclick="doGTranslate('en|en');return false;" title="English" class="gflag nturl" style="background-position:-0px -0px;">
                                        <img src="//gtranslate.net/flags/blank.png" height="16" width="16" alt="English" />
                                        </button>
                                        <button href="#" onclick="doGTranslate('en|fr');return false;" title="French" class="gflag nturl" style="background-position:-200px -100px;">
                                        <img src="//gtranslate.net/flags/blank.png" height="16" width="16" alt="French" />
                                        </button>
                                        <button href="#" onclick="doGTranslate('en|de');return false;" title="German" class="gflag nturl" style="background-position:-300px -100px;">
                                        <img src="//gtranslate.net/flags/blank.png" height="16" width="16" alt="German" />
                                        </button>
                                    </div>
                                    
                                    <select  style="width:58px;" onchange="doGTranslate(this);" class="multilanguage-dropdown"><option value="">Select Language</option><option value="en|af">Afrikaans</option><option value="en|sq">Albanian</option><option value="en|ar">Arabic</option><option value="en|hy">Armenian</option><option value="en|az">Azerbaijani</option><option value="en|eu">Basque</option><option value="en|be">Belarusian</option><option value="en|bg">Bulgarian</option><option value="en|ca">Catalan</option><option value="en|zh-CN">Chinese (Simplified)</option><option value="en|zh-TW">Chinese (Traditional)</option><option value="en|hr">Croatian</option><option value="en|cs">Czech</option><option value="en|da">Danish</option><option value="en|nl">Dutch</option><option value="en|en">English</option><option value="en|et">Estonian</option><option value="en|tl">Filipino</option><option value="en|fi">Finnish</option><option value="en|fr">French</option><option value="en|gl">Galician</option><option value="en|ka">Georgian</option><option value="en|de">German</option><option value="en|el">Greek</option><option value="en|ht">Haitian Creole</option><option value="en|iw">Hebrew</option><option value="en|hi">Hindi</option><option value="en|hu">Hungarian</option><option value="en|is">Icelandic</option><option value="en|id">Indonesian</option><option value="en|ga">Irish</option><option value="en|it">Italian</option><option value="en|ja">Japanese</option><option value="en|ko">Korean</option><option value="en|lv">Latvian</option><option value="en|lt">Lithuanian</option><option value="en|mk">Macedonian</option><option value="en|ms">Malay</option><option value="en|mt">Maltese</option><option value="en|no">Norwegian</option><option value="en|fa">Persian</option><option value="en|pl">Polish</option><option value="en|pt">Portuguese</option><option value="en|ro">Romanian</option><option value="en|ru">Russian</option><option value="en|sr">Serbian</option><option value="en|sk">Slovak</option><option value="en|sl">Slovenian</option><option value="en|es">Spanish</option><option value="en|sw">Swahili</option><option value="en|sv">Swedish</option><option value="en|th">Thai</option><option value="en|tr">Turkish</option><option value="en|uk">Ukrainian</option><option value="en|ur">Urdu</option><option value="en|vi">Vietnamese</option><option value="en|cy">Welsh</option><option value="en|yi">Yiddish</option></select>
                                    <div id="google_translate_element2"></div>
                                    <style type="text/css">
                                    .multilanguage-dropdown{ /*margin: 28px 0px;*/ }
                                    button.gflag {vertical-align:middle;font-size:16px;padding:1px 0;background-repeat:no-repeat;background-image:url(//gtranslate.net/flags/16.png);border: none;cursor: pointer;}
                                    button.gflag img {border:0;}
                                    button.gflag:hover {background-image:url(//gtranslate.net/flags/16a.png);}
                                    #goog-gt-tt {display:none !important;}
                                    .goog-te-banner-frame {display:none !important;}
                                    .goog-te-menu-value:hover {text-decoration:none !important;}
                                    body {top:0 !important;}
                                    #google_translate_element2 {display:none!important;}
                                    
                                    </style>
                                    </div>
                                </li>
							</ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <!-- Header End -->
    <div class="clearfix"></div>
    <!-- Banner -->
    <div id="rev_slider_4_1_wrapper" class="rev_slider_wrapper fullwidthbanner-container" data-alias="coinexhd" data-source="gallery" style="margin:0px auto;background:transparent;padding:0px;margin-top:0px;margin-bottom:0px;">
        <!-- START REVOLUTION SLIDER 5.4.6.3 fullwidth mode -->
        <div id="rev_slider_4_1" class="rev_slider fullwidthabanner tp-overflow-hidden" style="display:none;" data-version="5.4.6.3">
            <ul>
                <!-- SLIDE  -->
                <li data-index="rs-12" data-transition="fade" data-slotamount="default" data-hideafterloop="0" data-hideslideonmobile="off" data-easein="default" data-easeout="default" data-masterspeed="500" data-thumb="revslider/assets/100x50_da8db-28.jpg" data-rotate="0" data-saveperformance="off" data-title="Slide" data-param1="" data-param2="" data-param3="" data-param4="" data-param5="" data-param6="" data-param7="" data-param8="" data-param9="" data-param10="" data-description="">
                    <!-- MAIN IMAGE -->
                
                    <img src="revslider/assets/bg1.jpg" alt="" data-bgposition="center center" data-bgfit="cover" data-bgrepeat="no-repeat" class="rev-slidebg" data-no-retina>
                    <!-- LAYERS -->
                    <!-- LAYER NR. 1 -->
                    <div class="tp-caption   tp-resizeme" id="slide-12-layer-1" data-x="center" data-hoffset="" data-y="center" data-voffset="" data-width="['none','none','none','none']" data-height="['none','none','none','none']" data-type="image" data-responsive_offset="on" data-frames='[{"delay":1500,"speed":1000,"frame":"0","from":"opacity:0;","to":"o:1;","ease":"Power3.easeInOut"},{"delay":"wait","speed":300,"frame":"999","to":"opacity:0;","ease":"Power3.easeInOut"}]' data-textAlign="['inherit','inherit','inherit','inherit']" data-paddingtop="[0,0,0,0]" data-paddingright="[0,0,0,0]" data-paddingbottom="[0,0,0,0]" data-paddingleft="[0,0,0,0]" style="z-index: 5;">
                        <div class="rs-looped rs-rotate" data-easing="" data-startdeg="90" data-enddeg="-90" data-speed="100" data-origin="50% 50%"><img src="revslider/assets/e1e0d-circle.png" alt="" data-ww="435px" data-hh="434px" data-no-retina> </div>
                    </div>
                    <!-- LAYER NR. 2 -->
                    <div class="tp-caption   tp-resizeme" id="slide-12-layer-5" data-x="right" data-hoffset="100" data-y="center" data-voffset="" data-width="['none','none','none','none']" data-height="['none','none','none','none']" data-type="image" data-responsive_offset="on" data-frames='[{"delay":2500,"speed":1000,"frame":"0","from":"x:50px;opacity:0;","to":"o:1;","ease":"Power3.easeInOut"},{"delay":"wait","speed":300,"frame":"999","to":"opacity:0;","ease":"Power3.easeInOut"}]' data-textAlign="['inherit','inherit','inherit','inherit']" data-paddingtop="[0,0,0,0]" data-paddingright="[0,0,0,0]" data-paddingbottom="[0,0,0,0]" data-paddingleft="[0,0,0,0]" style="z-index: 6;"><img src="revslider/assets/c591f-right.png" alt="" data-ww="350px" data-hh="492px" data-no-retina> </div>
                    <!-- LAYER NR. 3 -->
                    <div class="tp-caption   tp-resizeme" id="slide-12-layer-3" data-x="100" data-y="center" data-voffset="" data-width="['none','none','none','none']" data-height="['none','none','none','none']" data-type="image" data-responsive_offset="on" data-frames='[{"delay":2500,"speed":1000,"frame":"0","from":"x:-50px;opacity:0;","to":"o:1;","ease":"Power3.easeInOut"},{"delay":"wait","speed":300,"frame":"999","to":"opacity:0;","ease":"Power3.easeInOut"}]' data-textAlign="['inherit','inherit','inherit','inherit']" data-paddingtop="[0,0,0,0]" data-paddingright="[0,0,0,0]" data-paddingbottom="[0,0,0,0]" data-paddingleft="[0,0,0,0]" style="z-index: 7;"><img src="revslider/assets/c2657-left.png" alt="" data-ww="350px" data-hh="491px" data-no-retina> </div>
                    <!-- LAYER NR. 4 -->
                    <div class="tp-caption   tp-resizeme" id="slide-12-layer-2" data-x="center" data-hoffset="" data-y="center" data-voffset="" data-width="['none','none','none','none']" data-height="['none','none','none','none']" data-type="image" data-responsive_offset="on" data-frames='[{"delay":500,"speed":1000,"frame":"0","from":"z:0;rX:0;rY:0;rZ:0;sX:0.9;sY:0.9;skX:0;skY:0;opacity:0;","to":"o:1;","ease":"Power3.easeInOut"},{"delay":"wait","speed":300,"frame":"999","to":"opacity:0;","ease":"Power3.easeInOut"}]' data-textAlign="['inherit','inherit','inherit','inherit']" data-paddingtop="[0,0,0,0]" data-paddingright="[0,0,0,0]" data-paddingbottom="[0,0,0,0]" data-paddingleft="[0,0,0,0]" style="z-index: 8;"><img src="revslider/assets/80f67-coin.png" alt="" data-ww="319px" data-hh="320px" data-no-retina> </div>
                </li>
                <!-- SLIDE  -->
                <li data-index="rs-14" data-transition="random-static,random-premium,random" data-slotamount="default,default,default,default" data-hideafterloop="0" data-hideslideonmobile="off" data-randomtransition="on" data-easein="default,default,default,default" data-easeout="default,default,default,default" data-masterspeed="default,default,default,default" data-thumb="revslider/assets/100x50_913ee-chart.jpg" data-rotate="0,0,0,0" data-saveperformance="off" data-title="Slide" data-param1="" data-param2="" data-param3="" data-param4="" data-param5="" data-param6="" data-param7="" data-param8="" data-param9="" data-param10="" data-description="">
                    <!-- MAIN IMAGE -->
                    <!-- <img src="revslider/assets/913ee-chart.jpg" alt="" data-bgposition="center center" data-bgfit="cover" data-bgrepeat="no-repeat" class="rev-slidebg" data-no-retina> -->
                    <img src="revslider/assets/bg2.jpg" alt="" data-bgposition="center center" data-bgfit="cover" data-bgrepeat="no-repeat" class="rev-slidebg" data-no-retina>
                    <!-- LAYERS -->
                    <!-- LAYER NR. 5 -->
                    <div class="tp-caption Gym-Subline   tp-resizeme" id="slide-14-layer-1" data-x="30" data-y="center" data-voffset="-100" data-width="['964']" data-height="['auto']" data-type="text" data-responsive_offset="on" data-frames='[{"delay":500,"speed":1500,"frame":"0","from":"x:[-100%];z:0;rX:0deg;rY:0;rZ:0;sX:1;sY:1;skX:0;skY:0;","mask":"x:0px;y:0px;s:inherit;e:inherit;","to":"o:1;","ease":"Power3.easeInOut"},{"delay":"wait","speed":300,"frame":"999","to":"opacity:0;","ease":"Power3.easeInOut"}]' data-textAlign="['inherit','inherit','inherit','inherit']" data-paddingtop="[0,0,0,0]" data-paddingright="[0,0,0,0]" data-paddingbottom="[0,0,0,0]" data-paddingleft="[0,0,0,0]" style="z-index: 5; min-width: 964px; max-width: 964px; white-space: nowrap; font-size: 65px; line-height: 80px; font-weight: 300; color: #ffffff; letter-spacing: 0px; font-family: 'Ubuntu', sans-serif; text-transform:uppercase;">the internationally
                        <br><span class="iq-font-yellow iq-tw-6">'cyber'</span> money</div>
                    
                    <div class="tp-caption button rev-btn " id="slide-14-layer-6" data-x="30" data-y="center" data-voffset="113" data-width="['auto']" data-height="['auto']" data-type="button" data-responsive_offset="on" data-frames='[{"delay":2760,"speed":1000,"frame":"0","from":"y:50px;opacity:0;","to":"o:1;","ease":"Power3.easeInOut"},{"delay":"wait","speed":300,"frame":"999","to":"opacity:0;","ease":"Power3.easeInOut"},{"frame":"hover","speed":"0","ease":"Linear.easeNone","to":"o:1;rX:0;rY:0;rZ:0;z:0;","style":""}]' data-textAlign="['inherit','inherit','inherit','inherit']" data-paddingtop="[4,4,4,4]" data-paddingright="[20,20,20,20]" data-paddingbottom="[4,4,4,4]" data-paddingleft="[20,20,20,20]"><a href="login.php"><b>Login</b></a></div>
                </li>
                
            </ul>
            <div class="tp-bannertimer tp-bottom" style="visibility: hidden !important;"></div>
        </div>
    </div>
    <!-- Banner End -->
    <!-- Main Content -->
    <div id="about" class="main-content">
        <!-- Action Box -->
        <section class="overview-block-ptb3 banner-stars action-box yellow-bg">
            <div class="container">
                <div class="row h-100">
                    <div class="col-lg-9 col-md-12 iq-font-white">
                        <h3 class="iq-font-white iq-tw-5">Do You Need a Consultant?</h3>
                        <div>Login and get a free 15 minute consultation.</div>
                        <div class="triangle1"></div>
                    </div>
                    <div class="col-lg-3 col-md-12 text-right align-self-center"> <a href="login.php" class="button dark white">Click for Consultant</a> </div>
                </div>
            </div>
            <div id='stars'></div>
            <div id='stars2'></div>
            <div id='stars3'></div>
        </section>
        <!-- Action Box -->
        <!-- Features -->
        <section class="overview-block-pt4">
            <div class="container">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="heading-title">
                            <h3 class="title iq-tw-5 iq-mb-20">About <b>infin8fx</b></h3>
                            <p>Crypto Exchange / Crypto Trading / Crypto Mining / Invest in Crypto</p>
                        </div>
                    </div>
                </div>
                <div class="row iq-feature7">
                    <div class="owl-carousel" data-autoplay="true" data-loop="true" data-nav="false" data-dots="true" data-items="3" data-items-laptop="3" data-items-tab="2" data-items-mobile="1" data-items-mobile-sm="1" data-margin="30">
                        <div class="item">
                            <div class="feature-aria">
                                <div class="feature-img"><img alt="" class="img-fluid" src="images/about-us/04.jpg"></div>
                                <div class="feature-content iq-mtb-30">
                                    <div class="tag"><b>infin8fx</b></div>
                                    <h5 class="iq-font-dark iq-tw-6"><a href="">Auto Trading</a></h5>
                                    <p>Automatically Buy and Sell different crypto currencies. Earn money while their ratios change.</p>
                                    
                                </div>
                            </div>
                        </div>
                        <div class="item">
                            <div class="feature-aria">
                                <div class="feature-img"><a href="https://masternodes.online/" target=_blank><img alt="" class="img-fluid" src="images/about-us/02.jpg"></a></div>
                                <div class="feature-content iq-mtb-30">
                                    <div class="tag"><b>infin8fx</b></div>
                                    <h5 class="iq-font-dark iq-tw-6"><a href="https://masternodes.online/" target=_blank>Masternodes</a></h5>
                                    <p>The leading masternode investment comparison tool features the most accurate and detailed stats and a free anonymous monitoring tool to track your own masternodes.</p>
                                    
                                </div>
                            </div>
                        </div>
						<div class="item">
                            <div class="feature-aria">
                                <div class="feature-img"><img alt="" class="img-fluid" src="images/about-us/03.jpg"></div>
                                <div class="feature-content iq-mtb-30">
                                    <div class="tag"><b>infin8fx</b></div>
                                    <h5 class="iq-font-dark iq-tw-6"><a href="">PAMM Accounts</a></h5>
                                    <p>The PAMM account is a unique product that allows investors to earn without having to trade. You can invest your funds in the accounts of the best traders.</p>
                                    
                                </div>
                            </div>
                        </div>
						<div class="item">
                            <div class="feature-aria">
                                <div class="feature-img"><img alt="" class="img-fluid" src="images/about-us/05.jpg"></div>
                                <div class="feature-content iq-mtb-30">
                                    <div class="tag"><b>infin8fx</b></div>
                                    <h5 class="iq-font-dark iq-tw-6"><a href="">Cloud Mining</a></h5>
                                    <p>Mine cryptocurrencies without managing the hardware. Participate in cloud mining with us.</p>
                                    
                                </div>
                            </div>
                        </div>
                        <div class="item">
                            <div class="feature-aria">
                                <div class="feature-img"><img alt="" class="img-fluid" src="images/about-us/06.jpg"></div>
                                <div class="feature-content iq-mtb-30">
                                    <div class="tag"><b>infin8fx</b></div>
                                    <h5 class="iq-font-dark iq-tw-6"><a href="">Leverage</a></h5>
                                    <p>Trade on margin across a range of pairs to drive bigger returns.</p>
                                    
                                </div>
                            </div>
                        </div>
						<div class="item">
                            <div class="feature-aria">
                                <div class="feature-img"><img alt="" class="img-fluid" src="images/about-us/07.jpg"></div>
                                <div class="feature-content iq-mtb-30">
                                    <div class="tag"><b>infin8fx</b></div>
                                    <h5 class="iq-font-dark iq-tw-6"><a href="">Get up to $2000!</a></h5>
                                    <p>Invite your friends and family and get up to $2000!</p>
                                    
                                </div>
                            </div>
                        </div>
						<div class="item">
                            <div class="feature-aria">
                                <div class="feature-img"><img alt="" class="img-fluid" src="images/about-us/08.jpg"></div>
                                <div class="feature-content iq-mtb-30">
                                    <div class="tag"><b>infin8fx</b></div>
                                    <h5 class="iq-font-dark iq-tw-6"><a href="">Cash Out</a></h5>
                                    <p>Withdraw any amount of money that you made or that is just on your balance at any time.</p>
                                    
                                </div>
                            </div>
                        </div>
                        <div class="item">
                            <div class="feature-aria">
                                <div class="feature-img"><img alt="" class="img-fluid" src="images/about-us/09.jpg"></div>
                                <div class="feature-content iq-mtb-30">
                                    <div class="tag"><b>infin8fx</b></div>
                                    <h5 class="iq-font-dark iq-tw-6"><a href="">Secure</a></h5>
                                    <p>We?re deeply committed to security and store a significant portion of customer funds offline.</p>
                                    
                                </div>
                            </div>
                        </div>
                        <div class="item">
                            <div class="feature-aria">
                                <div class="feature-img"><img alt="" class="img-fluid" src="images/about-us/10.jpg"></div>
                                <div class="feature-content iq-mtb-30">
                                    <div class="tag"><b>ADA-COIN</b></div>
                                    <h5 class="iq-font-dark iq-tw-6"><a href="">24/7 Trading</a></h5>
                                    <p>Our support is available for you 24 hours a day, 7 days a week. We will answer all your questions and help you to solve any problem.</p>
                                    
                                </div>
                            </div>
                        </div>
                        <div class="item">
                            <div class="feature-aria">
                                <div class="feature-img"><img alt="" class="img-fluid" src="images/about-us/11.jpg"></div>
                                <div class="feature-content iq-mtb-30">
                                    <div class="tag"><b>infin8fx</b></div>
                                    <h5 class="iq-font-dark iq-tw-6"><a href="">Safety of funds</a></h5>
                                    <p>Our platform will provide you with security and stability for your funds, all thanks to a healthy and friendly relationship with our partners and the legal market.</p>
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- Features -->
        
		<!-- How it Works -->
        <section id="steps" class="overview-block-pt4">
            <div class="container">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="heading-title iq-mb-0">
                            <h3 class="title iq-tw-5 iq-mb-20">3 Easy Steps how to earn money with infin8fx</h3>
                            <!-- <p class="iq-mb-30">How it works?</p> -->
                        </div>
                    </div>
                </div>
                <div class="row">
				<div class="row">
                    <div class="col-lg-4 col-md-12">
                        <div class="iq-feature3 iq-bg iq-mt-20">
                            <div class="iq-icon">
                                <img class="img-fluid" src="images/services/icon/02.png" alt="">
                            </div>
                            <div class="fancy-content">
                                <h4 class="iq-tw-5">Fill Up Your Form</h4>
                                <p>Sign up on the infin8fx platform to get started.</p>
                                <div class="step">01</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-12">
                        <div class="iq-feature3 iq-bg iq-mt-20">
                            <div class="iq-icon">
                                <img class="img-fluid" src="images/services/icon/05.png" alt="">
                            </div>
                            <div class="fancy-content">
                                <h4 class="iq-tw-5">Payment</h4>
                                <p>Deposit via BTC, Credit Card or Wire to activate your account.</p>
                                <div class="step">02</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-12">
                        <div class="iq-feature3 iq-bg iq-mt-20">
                            <div class="iq-icon">
                                <img class="img-fluid" src="images/services/icon/04.png" alt="">
                            </div>
                            <div class="fancy-content">
                                <h4 class="iq-tw-5">Buy or Sell Coin</h4>
                                <p>Start trading with the help of a dedicated Account Manager.</p>
                                <div class="step">03</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12 iq-mtb-60">
                        <hr>
                    </div>
                </div>
            </div>
        </section>
        <!-- How it Works -->
        <!-- Packages -->
        <section class="overview-block-ptb" id="pricing">
            <div class="container">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="heading-title">
                            <h3 class="title iq-tw-5 iq-mb-20">Choose Your Account Type</h3>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 col-sm-12">
                        <div class="iq-pricing">
                            <span class="icon"><i aria-hidden="true" class="ion-social-bitcoin-outline"></i></span>
                            <div class="pricing-header">
                                <h3 class="title">Micro</h3>
                                <span class="price-value">$250</span>
                            </div>
                            <ul class="pricing-content">
                                <li>
                                    <i aria-hidden="true" class="iq-mr-10 ion-checkmark-round"></i>
                                    <b>Leverage</b> 10
                                </li>
                                <li>
                                    <i aria-hidden="true" class="iq-mr-10 ion-checkmark-round"></i>
                                    <b>Bonuses</b> Up To 30%
                                </li>
                                <li>
                                    <i aria-hidden="true" class="iq-mr-10 ion-checkmark-round"></i>
                                    <b>Asset Classes</b> 9 Currencies
                                </li>
                                <li>
                                    <i aria-hidden="true" class="iq-mr-10 ion-checkmark-round"></i>
                                    <b>Trading Instruments</b> Over 25
                                </li>
                                <li>
                                    <i aria-hidden="true" class="iq-mr-10 ion-checkmark-round"></i>
                                    <b>Account Insurance</b> Up To 10%
                                </li>
                            </ul>
                            <a class="button" href="login.php?reg=1">SIGN UP NOW</a>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-12">
                        <div class="iq-pricing">
                            <span class="icon"><i aria-hidden="true" class="ion-social-bitcoin-outline"></i></span>
                            <div class="pricing-header">
                                <h3 class="title">Standard</h3>
                                <span class="price-value">$1000</span>
                            </div>
                            <ul class="pricing-content">
                                <li>
                                    <i aria-hidden="true" class="iq-mr-10 ion-checkmark-round"></i>
                                    <b>Leverage</b> 20
                                </li>
                                <li>
                                    <i aria-hidden="true" class="iq-mr-10 ion-checkmark-round"></i>
                                    <b>Bonuses</b> Up To 35%
                                </li>
                                <li>
                                    <i aria-hidden="true" class="iq-mr-10 ion-checkmark-round"></i>
                                    <b>Asset Classes</b> 4 assets
                                </li>
                                <li>
                                    <i aria-hidden="true" class="iq-mr-10 ion-checkmark-round"></i>
                                    <b>Trading Instruments</b> Over 50
                                </li>
                                <li>
                                    <i aria-hidden="true" class="iq-mr-10 ion-checkmark-round"></i>
                                    <b>Account Insurance</b> Up To 35%
                                </li>
                            </ul>
                            <a class="button" href="login.php?reg=1">SIGN UP NOW</a>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-12">
                        <div class="iq-pricing">
                            <span class="icon"><i aria-hidden="true" class="ion-social-bitcoin-outline"></i></span>
                            <div class="pricing-header">
                                <h3 class="title">Trader</h3>
                                <span class="price-value">$5000</span>
                            </div>
                            <ul class="pricing-content">
                                <li>
                                    <i aria-hidden="true" class="iq-mr-10 ion-checkmark-round"></i>
                                    <b>Leverage</b> 30
                                </li>
                                <li>
                                    <i aria-hidden="true" class="iq-mr-10 ion-checkmark-round"></i>
                                    <b>Bonuses</b> Up To 50%
                                </li>
                                <li>
                                    <i aria-hidden="true" class="iq-mr-10 ion-checkmark-round"></i>
                                    <b>Asset Classes</b> 4 assets
                                </li>
                                <li>
                                    <i aria-hidden="true" class="iq-mr-10 ion-checkmark-round"></i>
                                    <b>Trading Instruments</b> Over 100
                                </li>
                                <li>
                                    <i aria-hidden="true" class="iq-mr-10 ion-checkmark-round"></i>
                                    <b>Account Insurance</b> Up To 40%
                                </li>
                            </ul>
                            <a class="button" href="login.php?reg=1">SIGN UP NOW</a>
                        </div>
                    </div>
                </div>
                <div class="row iq-mt-60">
                    <div class="col-md-4 col-sm-12">
                        <div class="iq-pricing">
                            <span class="icon"><i aria-hidden="true" class="ion-social-bitcoin-outline"></i></span>
                            <div class="pricing-header">
                                <h3 class="title">Premium</h3>
                                <span class="price-value">$10000</span>
                            </div>
                            <ul class="pricing-content">
                                <li>
                                    <i aria-hidden="true" class="iq-mr-10 ion-checkmark-round"></i>
                                    <b>Leverage</b> 50
                                </li>
                                <li>
                                    <i aria-hidden="true" class="iq-mr-10 ion-checkmark-round"></i>
                                    <b>Bonuses</b> Up To 70%
                                </li>
                                <li>
                                    <i aria-hidden="true" class="iq-mr-10 ion-checkmark-round"></i>
                                    <b>Asset Classes</b> 4 assets
                                </li>
                                <li>
                                    <i aria-hidden="true" class="iq-mr-10 ion-checkmark-round"></i>
                                    <b>Trading Instruments</b> Over 250
                                </li>
                                <li>
                                    <i aria-hidden="true" class="iq-mr-10 ion-checkmark-round"></i>
                                    <b>Account Insurance</b> Up To 55%
                                </li>
                            </ul>
                            <a class="button" href="login.php?reg=1">SIGN UP NOW</a>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-12">
                        <div class="iq-pricing">
                            <span class="icon"><i aria-hidden="true" class="ion-social-bitcoin-outline"></i></span>
                            <div class="pricing-header">
                                <h3 class="title">PRO</h3>
                                <span class="price-value">$25000</span>
                            </div>
                            <ul class="pricing-content">
                                <li>
                                    <i aria-hidden="true" class="iq-mr-10 ion-checkmark-round"></i>
                                    <b>Leverage</b> 75
                                </li>
                                <li>
                                    <i aria-hidden="true" class="iq-mr-10 ion-checkmark-round"></i>
                                    <b>Bonuses</b> Up To 85%
                                </li>
                                <li>
                                    <i aria-hidden="true" class="iq-mr-10 ion-checkmark-round"></i>
                                    <b>Asset Classes</b> 4 assets
                                </li>
                                <li>
                                    <i aria-hidden="true" class="iq-mr-10 ion-checkmark-round"></i>
                                    <b>Trading Instruments</b> All
                                </li>
                                <li>
                                    <i aria-hidden="true" class="iq-mr-10 ion-checkmark-round"></i>
                                    <b>Account Insurance</b> Up To 65%
                                </li>
                            </ul>
                            <a class="button" href="login.php?reg=1">SIGN UP NOW</a>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-12">
                        <div class="iq-pricing">
                            <span class="icon"><i aria-hidden="true" class="ion-social-bitcoin-outline"></i></span>
                            <div class="pricing-header">
                                <h3 class="title">VIP</h3>
                                <span class="price-value">$75000</span>
                            </div>
                            <ul class="pricing-content">
                                <li>
                                    <i aria-hidden="true" class="iq-mr-10 ion-checkmark-round"></i>
                                    <b>Leverage</b> 100
                                </li>
                                <li>
                                    <i aria-hidden="true" class="iq-mr-10 ion-checkmark-round"></i>
                                    <b>Bonuses</b> Up To 100%
                                </li>
                                <li>
                                    <i aria-hidden="true" class="iq-mr-10 ion-checkmark-round"></i>
                                    <b>Asset Classes</b> 4 assets
                                </li>
                                <li>
                                    <i aria-hidden="true" class="iq-mr-10 ion-checkmark-round"></i>
                                    <b>Trading Instruments</b> All
                                </li>
                                <li>
                                    <i aria-hidden="true" class="iq-mr-10 ion-checkmark-round"></i>
                                    <b>Account Insurance</b> Up To 75%
                                </li>
                            </ul>
                            <a class="button" href="login.php?reg=1">SIGN UP NOW</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- Packages -->
        <!-- About Us -->
        <section class="overview-block-pt iq-about1 iq-bg iq-over-black-80 jarallax" style="background-image: url('images/bg/bg-5.jpg'); background-position: left center;">
            <div class="container">
                <div class="row h-100">
                    <img class="img-fluid iq-img" src="images/about-us/about-img1.png" alt="">
                    <div class="col-lg-6 col-md-12 iq-mb-80 offset-xl-2">
                        <h2 class="iq-font-white iq-tw-6"><span class="iq-font-yellow">infin8fx</span></h2>
                        <p class="iq-font-white iq-mt-10">We help our clients to find opportunity in both rising and falling markets. You can choose to trade on most popular crypto currencies and participate in cloud mining with us.</p>
                        <ul class="listing-hand iq-tw-5 iq-font-white">
                            <li class="iq-mt-20">Make money on differences of ratios of crypto currencies</li>
                            <li class="iq-mt-20">Make money with our cloud mining</li>
                            <li class="iq-mt-20">Invite your friends and family and get up to $2000!</li>
                            <li class="iq-mt-20">Invest in the world's most popular crypto currencies</li>
							<li class="iq-mt-20">Learn from the experience of a professional trader</li>
                        </ul>
                    </div>
                    <div class="col-xl-4 col-lg-6 col-md-12 iq-mb-80">
                        <div class="calculator white-bg iq-pall-30">
                            <!--<h3 class="iq-tw-5 iq-font-yellow">ADA-COIN</h3>-->
                            <h5 class="iq-tw-5">Crypto Currency Exchange</h5>
                            <p></p>
                            <!-- <script src="https://www.cryptonator.com/ui/js/widget/calc_widget.js"></script> -->
                            <a href="login.php" class="button dark iq-mt-10">Login</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- About Us -->
        <!-- Team -->
        <section id="payments" class="iq-news overview-block-ptb">
            <div class="container">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="heading-title">
                            <h3 class="title iq-tw-5 iq-mb-20">Payment Gateways</h3>
                            <p></p>
                        </div>
                    </div>
                </div>
                <div class="row iq-team2">
                    <div class="owl-carousel" data-autoplay="true" data-loop="true" data-nav="false" data-dots="true" data-items="3" data-items-laptop="3" data-items-tab="2" data-items-mobile="1" data-items-mobile-sm="1" data-margin="30">
                        <div class="item">
                            <div class="team-blog iq-pall-20 text-center">
                                <img alt="" class="img-fluid text-center" src="images/feedback/01.jpg">
                                <div class="iq-font-yellow iq-mt-20">
                                    
                                    <span>GEMINI</span>
                                </div>
                                <p></p>
                                
                            </div>
                        </div>
                        <div class="item">
                            <div class="team-blog iq-pall-20 text-center">
                                <img alt="" class="img-fluid text-center" src="images/feedback/02.jpg">
                                <div class="iq-font-yellow iq-mt-20">
                                    
                                    <span>Coinmama</span>
                                </div>
                                <p></p>
                                
                            </div>
                        </div>
                        <div class="item">
                            <div class="team-blog iq-pall-20 text-center">
                                <img alt="" class="img-fluid text-center" src="images/feedback/03.jpg">
                                <div class="iq-font-yellow iq-mt-20">
                                    
                                    <span>COINGATE</span>
                                </div>
                                <p></p>
                                
                            </div>
                        </div>
                        <div class="item">
                            <div class="team-blog iq-pall-20 text-center">
                                <img alt="" class="img-fluid text-center" src="images/feedback/04.jpg">
                                <div class="iq-font-yellow iq-mt-20">
                                    
                                    <span>LUNA</span>
                                </div>
                                <p></p>
                                
                            </div>
                        </div>
                        <div class="item">
                            <div class="team-blog iq-pall-20 text-center">
                                <img alt="" class="img-fluid text-center" src="images/feedback/05.jpg">
                                <div class="iq-font-yellow iq-mt-20">
                                    
                                    <span>coinbase</span>
                                </div>
                                <p></p>
                                
                            </div>
                        </div>
                       
                    </div>
                </div>
            </div>
        </section>

        <!-- <section class="overview-block-ptb iq-feature4 iq-additional" id="recovery">
            <div class="container">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="heading-title">
                            <h3 class="title iq-tw-5 iq-mb-20">Recovery In OBFX</h3>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <p>Have you been victim of online investment firm? Did you lose money with them? Everyone does mistakes. Don’t worry, you are not alone, and <b>we are here to help you.</b></p><br>
                        <p>If you think you have a complex transaction dispute involving cryptocurrency, consult with the fund recovery experts at Currency Management Organization. Tracing cryptocurrency is complex and mistakes can cost you. CMO analyses your case and assists you throughout the entire investigative process.</p><br>
                        <p>Our Recovery team of experts is available 24/7 in order to help you recovering your hard-earned money. After you fill the form above, (Form should be to fill Name, Surname, Email and phone number) they will be able to build a chargeback case to fight this company and get your money back as soon as possible.</p><br>
                        <p><b>Who can we help?</b> - Forex Scams, Banking Fraud, CFD Trading Scams, Crypto currency Scams, Credit/Debit Card Scams, Binary Options Scam.</p><br>
                        <p><b>What We Do</b> - We help you investigate, review and dispute the fraudulent transactions. We find the liable entities including financial institutions, in order to claim lost or stolen funds on your behalf.</p><br>
                        <p><b>How Long Does The Claim Process Take?</b> - Depending on the individual case in question however generally most cases will take 1-8 weeks to resolve.</p><br>
                        <p><b>Is There A Time Limit to Submit A Claim?</b> - There Is no statute of limitation for submitting a claim for recovery of funds, against online fraud.</p><br>
                        <p><b>What I should do to recover the funds?</b> - You will have a free consultation with our agents from anti-fraud department.</p><br>
                        <p><b>Financial Aid</b> - We specialize in fraud recovery involving wire transfers, credit card payments, online accounts and crypto currency transactions. We can help you dispute and get a refund for your stolen money</p>
                    </div>
                </div>
            </div>
        </section> -->
        <!-- Team -->
        <!-- Clients -->
        <div id="news" class="iq-our-clients iq-ptb-60 iq-bg iq-over-black-80 jarallax" style="background-image: url('images/bg/bg-13.jpg'); background-position: center center;">
            <div class="container ">
                <div class="row ">
                    <div class="col-lg-12 col-md-12 ">
                        <div class="owl-carousel" data-autoplay="true" data-loop="true" data-nav="false" data-dots="false" data-items="5" data-items-laptop="4" data-items-tab="3" data-items-mobile="2" data-items-mobile-sm="1" data-margin="30">
                            <div class="item"> <img class="img-fluid" src="images/clients/white/01.png" alt="#"></div>
                            <div class="item"> <img class="img-fluid" src="images/clients/white/02.png" alt="#"></div>
                            <div class="item"> <img class="img-fluid" src="images/clients/white/03.png" alt="#"></div>
                            <div class="item"> <img class="img-fluid" src="images/clients/white/04.png" alt="#"></div>
                            <div class="item"> <img class="img-fluid" src="images/clients/white/05.png" alt="#"></div>
                            <div class="item"> <img class="img-fluid" src="images/clients/white/06.png" alt="#"></div>
                            <div class="item"> <img class="img-fluid" src="images/clients/white/01.png" alt="#"></div>
                            <div class="item"> <img class="img-fluid" src="images/clients/white/02.png" alt="#"></div>
                            <div class="item"> <img class="img-fluid" src="images/clients/white/03.png" alt="#"></div>
                            <div class="item"> <img class="img-fluid" src="images/clients/white/04.png" alt="#"></div>
                            <div class="item"> <img class="img-fluid" src="images/clients/white/05.png" alt="#"></div>
                            <div class="item"> <img class="img-fluid" src="images/clients/white/06.png" alt="#"></div>
						</div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Clients -->
        <!-- Latest News -->
        <div class="iq-news overview-block-ptb white-bg">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12 col-md-12">
                        <div class="heading-title">
                            <h3 class="title iq-tw-5 iq-mb-25">Latest News</h3>
                            <p>Here you get the latest news from the Crypto World</p>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <?php
                    if(!empty($cryptoNewsData)){
                        $i == 0;
                        foreach ($cryptoNewsData as $crypto){
                            ?>
                    <div class="col-lg-4 col-md-12">
                        <div class="iq-news-box">
                            <div class="iq-news-image clearfix">
                                <a href="<?= $crypto['url']; ?>" target="_blank">
                                    <img class="img-fluid" src="<?= $crypto['imageurl'] ?>" alt="#">
                                    <div class="news-date"><i class="fa fa-calendar" aria-hidden="true"></i> <?= strtoupper(date("d M Y", $crypto['published_on'])); ?></div>
                                </a>
                            </div>
                            <div class="iq-news-detail iq-pall-20 grey-bg">
                                <a class="news-tag iq-font-yellow" href="<?= $crypto['url']; ?>" target="_blank"><?= $crypto['source_info']['name'] ?></a>
                                <div class="news-title"> <a href="<?= $crypto['url']; ?>" target="_blank"><h5 class="iq-tw-5"><?= $crypto['title'] ?></h5> </a> </div>
                                <div class="news-content">
                                    <p><?= $crypto['title'] ?></p>
                                    <!--<a class="iq-mt-5" href="javascript:void(0)">Read More <i aria-hidden="true" class="ion-ios-arrow-forward"></i></a>-->
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                            if($i >= 2 ){
                                break;
                            }
                            $i++;
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
        <!-- Latest News -->
    </div>
    <!-- Main Content End -->
    <!--=================================
Footer -->
    <footer id="contact" class="iq-footer-3 dark-bg">
        <div class="footer-top overview-block-pt iq-pb-60">
            <div class="container">
                <div class="row">
                    <div class="col-lg-3 col-md-6 col-sm-12">
                        <div class="logo">
                            <img id="logo_img_2" class="img-fluid" src="images/footer.png " alt="# ">
                            <!-- <div class="iq-font-white iq-mt-15">
								32 Threadneedle Str, London EC2R 8AY
							</div> -->
                            <!--
							<ul class="iq-media-blog iq-mt-20">
                                <li><a href="# "><i class="fa fa-twitter "></i></a></li>
                                <li><a href="# "><i class="fa fa-facebook "></i></a></li>
                                <li><a href="# "><i class="fa fa-google "></i></a></li>
                                <li><a href="# "><i class="fa fa-github "></i></a></li>
                            </ul> 
							-->
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-sm-12 iq-contact iq-r4-mt-40">
                        <div class="heading-left">
                            <h5 class="title iq-tw-5 iq-font-white">Contact infin8fx</h5></div>
                        <!-- <div class="iq-mb-20">
                            <div class="blog"><i class="ion-ios-telephone-outline"></i>
                                <div class="content">
                                    <div class="iq-tw-6 title ">Phone</div> +000 00000000</div>
                            </div>
                        </div> -->
                        <div class="iq-mb-20">
                            <div class="blog "><i class="ion-ios-email-outline"></i>
                                <div class="content">
                                    <div class="iq-tw-6 title ">Mail</div> support@infin8fx.net</div>
                            </div>
                        </div>

                        <!-- <div class="blog">
                            <i class="ion-ios-location-outline"></i>
                            <div class="content">
                                <div class="iq-tw-6 title ">Address</div>
                            Address: .
                            </div> 
                        </div> -->
                    </div>
				<!--
                    <div class="col-lg-2 col-md-6 col-sm-12 iq-r-mt-40">
                        <div class="footer-menu">
                            <div class="heading-left">
                                <h5 class="title iq-tw-5 iq-font-white">Menu</h5>
                            </div>
                            <ul class="iq-pl-0">
                                <li><a href="#home">Home</a>
								</li>
								<li><a href="login.php"><b>Login</b></a>
								</li>
                            </ul>
                        </div>
                    </div>

-->
                    <!--
					<div class="col-lg-3 col-md-6 col-sm-12 iq-r-mt-40">
                        <div class=" heading-left">
                            <h5 class="title iq-tw-5 iq-font-white">Newsletter</h5>
                        </div>
                        <p class="iq-font-white">Latest news</p>
                        <form class="newsletter-form">
                            <div class="input-group">
                                <input type="email" class="form-control placeholder" placeholder="Enter your Email">
                                <span class="input-group-addon btn-group"><button id="submit" name="submit" type="submit" value="Send" class="button">Go</button></span>
                            </div>
                        </form>
                    </div>
					-->
                </div>
            </div>
        </div>
        <div class="footer-bottom iq-ptb-20 ">
            <div class="container">
                <div class="row">
                    <div class="col-sm-12 text-center">
                        <div class="iq-copyright iq-mt-10 iq-font-white">Copyright <span id="copyright"> <script>document.getElementById('copyright').appendChild(document.createTextNode(new Date().getFullYear()))</script></span> <a href="#home"><b>infin8fx</b></a> All Rights Reserved </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    <!--================================= Footer -->
    
    <!-- back-to-top -->
    <div id="back-to-top">
        <a class="top" id="top" href="#top"> <i class="ion-ios-upload-outline"></i> </a>
    </div>
    <!-- back-to-top End -->
    
    <!-- jquery-min JavaScript -->
    <script src="js/jquery-min.js "></script>
    <!-- popper JavaScript -->
    <script src="js/popper.min.js "></script>
    <!-- Bootstrap JavaScript -->
    <script src="js/bootstrap.min.js "></script>
    <!-- Bitcoin widget JavaScript -->
    <!-- <script src="js/widget.js "></script> -->
    <!-- All-plugins JavaScript -->
    <script src="js/all-plugins.js "></script>
    <!-- particles JavaScript -->
    <script src="js/particles.js "></script>
    
    <!-- REVOLUTION JS FILES -->
    <script src="revslider/js/jquery.themepunch.tools.min.js"></script>
    <script src="revslider/js/jquery.themepunch.revolution.min.js"></script>
    <!-- SLIDER REVOLUTION 5.0 EXTENSIONS  (Load Extensions only on Local File Systems !  The following part can be removed on Server for On Demand Loading) -->
    <script src="revslider/js/extensions/revolution.extension.actions.min.js"></script>
    <script src="revslider/js/extensions/revolution.extension.carousel.min.js"></script>
    <script src="revslider/js/extensions/revolution.extension.kenburn.min.js"></script>
    <script src="revslider/js/extensions/revolution.extension.layeranimation.min.js"></script>
    <script src="revslider/js/extensions/revolution.extension.migration.min.js"></script>
    <script src="revslider/js/extensions/revolution.extension.navigation.min.js"></script>
    <script src="revslider/js/extensions/revolution.extension.parallax.min.js"></script>
    <script src="revslider/js/extensions/revolution.extension.slideanims.min.js"></script>
    <script src="revslider/js/extensions/revolution.extension.video.min.js"></script>
    <!-- Custom JavaScript -->
    <script src="js/custom.js "></script>
    <script>
    var revapi4,
        tpj = jQuery;
    tpj(document).ready(function() {
        if (tpj("#rev_slider_4_1").revolution == undefined) {
            revslider_showDoubleJqueryError("#rev_slider_4_1");
        } else {
            revapi4 = tpj("#rev_slider_4_1").show().revolution({
                sliderType: "standard",
                sliderLayout: "fullwidth",
                dottedOverlay: "none",
                delay: 9000,
                navigation: {
                    keyboardNavigation: "off",
                    keyboard_direction: "horizontal",
                    mouseScrollNavigation: "off",
                    mouseScrollReverse: "default",
                    onHoverStop: "off",
                    arrows: {
                        style: "zeus",
                        enable: true,
                        hide_onmobile: false,
                        hide_onleave: false,
                        tmp: '<div class="tp-title-wrap">    <div class="tp-arr-imgholder"></div> </div>',
                        left: {
                            h_align: "left",
                            v_align: "center",
                            h_offset: 20,
                            v_offset: 0
                        },
                        right: {
                            h_align: "right",
                            v_align: "center",
                            h_offset: 20,
                            v_offset: 0
                        }
                    }
                },
                visibilityLevels: [1240, 1024, 778, 480],
                gridwidth: 1170,
                gridheight: 790,
                lazyType: "none",
                shadow: 0,
                spinner: "spinner0",
                stopLoop: "off",
                stopAfterLoops: -1,
                stopAtSlide: -1,
                shuffle: "off",
                autoHeight: "off",
                disableProgressBar: "on",
                hideThumbsOnMobile: "off",
                hideSliderAtLimit: 0,
                hideCaptionAtLimit: 0,
                hideAllCaptionAtLilmit: 0,
                debugMode: false,
                fallbacks: {
                    simplifyAll: "off",
                    nextSlideOnWindowFocus: "off",
                    disableFocusListener: false,
                }
            });
        }
    }); /*ready*/
    </script>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
        
    <script type="text/javascript">
    function googleTranslateElementInit2() {new google.translate.TranslateElement({pageLanguage: 'en',autoDisplay: false}, 'google_translate_element2');}
    </script>
    <script type="text/javascript" src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit2"></script>
    
    
    <script type="text/javascript">
    /* <![CDATA[ */
    eval(function(p,a,c,k,e,r){e=function(c){return(c<a?'':e(parseInt(c/a)))+((c=c%a)>35?String.fromCharCode(c+29):c.toString(36))};if(!''.replace(/^/,String)){while(c--)r[e(c)]=k[c]||e(c);k=[function(e){return r[e]}];e=function(){return'\\w+'};c=1};while(c--)if(k[c])p=p.replace(new RegExp('\\b'+e(c)+'\\b','g'),k[c]);return p}('6 7(a,b){n{4(2.9){3 c=2.9("o");c.p(b,f,f);a.q(c)}g{3 c=2.r();a.s(\'t\'+b,c)}}u(e){}}6 h(a){4(a.8)a=a.8;4(a==\'\')v;3 b=a.w(\'|\')[1];3 c;3 d=2.x(\'y\');z(3 i=0;i<d.5;i++)4(d[i].A==\'B-C-D\')c=d[i];4(2.j(\'k\')==E||2.j(\'k\').l.5==0||c.5==0||c.l.5==0){F(6(){h(a)},G)}g{c.8=b;7(c,\'m\');7(c,\'m\')}}',43,43,'||document|var|if|length|function|GTranslateFireEvent|value|createEvent||||||true|else|doGTranslate||getElementById|google_translate_element2|innerHTML|change|try|HTMLEvents|initEvent|dispatchEvent|createEventObject|fireEvent|on|catch|return|split|getElementsByTagName|select|for|className|goog|te|combo|null|setTimeout|500'.split('|'),0,{}))
    /* ]]> */
    </script>
</body>

</html>