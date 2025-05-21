<?php
error_reporting(1);
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
    $appUrl = $protocol . $_SERVER['HTTP_HOST'];
    define('APP_URL', $appUrl);
    define('APP_URL_FORCE', false);

    define('FILE_PATH', '/');

    define('MYSQL_HOST', 'localhost');  // MySQL Database host (localhost, 127.0.0.1, X.X.X.X, domain.tld)
    define('MYSQL_USER', 'u630685862_market');   // MySQL User (Please not use 'root', create a dedicated user with full permision user --> go doc)
    define('MYSQL_PASSWD', '&r8@JKc##G7'); // MySQL Password
    define('MYSQL_PORT', '3306');        // MySQL Port (Set empty for not specify port)
    define('MYSQL_DATABASE', 'u630685862_market');        // MySQL Database (Use the file sql.sql for create sql requirement)
    //define('MYSQL_DATABASE', 'inhorfoz_db1');        // MySQL Database (Use the file sql.sql for create sql requirement)

    define('CRYPTED_KEY', 'gPA2rQ2QyOJ66HNJs3SApf1Jg53LJLBi37qCLXacxEuh1j7su0z3CLSOAcyJ');
?>