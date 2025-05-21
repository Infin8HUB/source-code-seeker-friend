<?php

require "../../../../config/config.settings.php";

if (empty($_REQUEST)) {
    header('Location:' . APP_URL . "/dashboard.php");
    exit;
}
//echo "<pre>";
//print_r($_REQUEST);
//exit;
if (isset($_REQUEST['status']) && ($_REQUEST['status'] == 'ok' || $_REQUEST['status'] == 'wait')) {
    ?>

    <html>
        <head>
            <link href="https://fonts.googleapis.com/css?family=Nunito+Sans:400,400i,700,900&amp;display=swap" rel="stylesheet">
            <style>
                body {
                    text-align: center;
                    padding: 40px 0;
                    background: #EBF0F5;
                }
                h1 {
                    color: #88B04B;
                    font-family: "Nunito Sans", "Helvetica Neue", sans-serif;
                    font-weight: 900;
                    font-size: 40px;
                    margin-bottom: 10px;
                }
                p {
                    color: #404F5E;
                    font-family: "Nunito Sans", "Helvetica Neue", sans-serif;
                    font-size:20px;
                    margin: 0;
                    max-width: 360px;
                }
                i {
                    color: #9ABC66;
                    font-size: 100px;
                    line-height: 200px;
                    margin-left:-15px;
                }
                .card {
                    background: white;
                    padding: 60px;
                    border-radius: 4px;
                    box-shadow: 0 2px 3px #C8D0D8;
                    display: inline-block;
                    margin: 0 auto;
                }
                .btn-div {
                    display: inline-block;
                    margin-top: 20px;
                }
                .success-btn {
                    background-color: #fab915;
                    font-size: 16px;
                    color: #fff;
                    padding: 10px 50px;
                    border-radius: 4px;
                    font-family: "Nunito Sans", "Helvetica Neue", sans-serif;
                    font-weight: bold;
                    text-decoration: none;
                }
                a {
                    color: #19a8b8;
                    text-decoration: none;
                }
            </style>
        </head>
        <body cz-shortcut-listen="true">
            <div class="card">
                <div style="border-radius:200px; height:200px; width:200px; background: #F8FAF5; margin:0 auto;">
                    <i class="checkmark">✓</i>
                </div>
                <h1>Thank You</h1>
                <p>Your payment was successfully completed.</p>
                <br>
                <p><b>Transaction ID: <?= $_REQUEST['order_nr'] ?></b></p>

                <div class="btn-div">
                    <a href="<?= APP_URL . "/dashboard.php" ?>" class="success-btn" id="" style="border: 0px;">Go to Home</a>
                </div>
            </div>
        </body>
    </html>
    <?php
} else {
    ?>
    <html>
        <head>
            <link href="https://fonts.googleapis.com/css?family=Nunito+Sans:400,400i,700,900&amp;display=swap" rel="stylesheet">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
            <style>
                body {
                    text-align: center;
                    padding: 40px 0;
                    background: #EBF0F5;
                }
                h1 {
                    color: #ec0505;
                    font-family: "Nunito Sans", "Helvetica Neue", sans-serif;
                    font-weight: 900;
                    font-size: 40px;
                    margin-bottom: 10px;
                }
                p {
                    color: #404F5E;
                    font-family: "Nunito Sans", "Helvetica Neue", sans-serif;
                    font-size:20px;
                    margin: 0;
                }
                i {
                    color: #ec0505;
                    font-size: 100px !important;
                    line-height: 200px !important;
                }
                .card {
                    background: white;
                    padding: 60px;
                    border-radius: 4px;
                    box-shadow: 0 2px 3px #C8D0D8;
                    display: inline-block;
                    margin: 0 auto;
                }
                .btn-div {
                    display: inline-block;
                    margin-top: 20px;
                }
                .success-btn {
                    background-color: #fab915;
                    font-size: 16px;
                    color: #fff;
                    padding: 10px 50px;
                    border-radius: 4px;
                    font-family: "Nunito Sans", "Helvetica Neue", sans-serif;
                    font-weight: bold;
                    text-decoration: none;
                }
            </style>
        </head>
        <body cz-shortcut-listen="true">
            <div class="card">
                <div style="border-radius:200px; height:200px; width:200px; background: #F8FAF5; margin:0 auto;">
                    <i class="fa fa-times"></i>
                </div>
                <h1>Sorry!!</h1>
                <p>Your Transaction failed. Please try again...</p>
                <p><b>Transaction ID: <?= $_REQUEST['order_nr'] ?></b></p>
                <br>
                <div class="btn-div">
                    <a href="<?= APP_URL . "/dashboard.php" ?>" class="success-btn" id="" style="border: 0px;">Go to Home</a>
                </div>
            </div>
        </body>
    </html>
    <?php
}
?>