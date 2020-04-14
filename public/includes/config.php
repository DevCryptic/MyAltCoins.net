<?php
// Turn off error reporting
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// DatabaseInfo
define('DB_HOST', 'localhost');
define('DB_NAME', 'coins');
define('DB_USERNAME', 'coins');
define('DB_PASSWORD', '$8#Mr#h$4G5f^#q$VC2r');

//Timezone Config
try {
$odb = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USERNAME, DB_PASSWORD);
$odb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
 //echo 'LOC Connection failed: ' . $e->getMessage();
 // die();
die ( 'Database is down. Please notify the administrator immediately.' );

} 

$smtpauthtype = 'tls'; // tls - ssl
$smtpauthstat = true; // true - false

//Re-captcha keys, you need to obtain them from https://www.google.com/recaptcha/intro/index.html
$rpublickey = '6LeXOR0UAAAAABnRBtoRYBX1vhPtyKcljCe';
$rprivatekey = '6LeXOR0UAAAAAGPoxxjmuTtcyCgVZiNspZ5uedFJ';


?>
