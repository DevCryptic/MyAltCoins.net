<?php
define('DIRECT', TRUE);
function getRealIpAddr()
{
	if (!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet
	{
		$ip=$_SERVER['HTTP_CLIENT_IP'];
	}
	elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is pass from proxy
	{
		$ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
	}
	else
	{
		$ip=$_SERVER['REMOTE_ADDR'];
	}
	//$final = end(explode(',', $ip));
	$ip1 = explode(',', $ip); 
	$final = end($ip1);
	//return $ip;
    return $final;

}

$currentpage = $_SERVER['SCRIPT_NAME'];
function CheckPageA($page)
{
	global $currentpage;
	if (strstr($currentpage, $page))
	{
		echo ' active';
	}
}
function CheckPageAdmin($page)
{
	global $currentpage;
	if (strstr($currentpage, $page))
	{
		echo ' current_section';
	}
}
function CheckPageB($page)
{
	global $currentpage;
	if (strstr($currentpage, $page))
	{
		echo ' act_item';
	}
}

$_SERVER['REMOTE_ADDR'] = getRealIpAddr();
require 'function.php';
require 'mail/PHPMailerAutoload.php';
$user = new users;
$userstat = new ustat;
$rvpn = new rvpn;

/*
	General Website Config
*/
$site_url = "myaltcoins.net/";
$site_title = "MyAltCoins";
$rvpnmailer = "smtp";
$rvpnregconfirmation = "1";
$rsport = "587";
$rshost = "ghost.mxroute.com";
$rsuser = "mailer@myaltcoins.net";
$rspass = "aZTF%{iWO7(T28$";
$rpublickey = "6LdqWR0UAAAAAL6V4pnzl-Mm7qSayU3CcQFBFVph";
$rprivatekey = "6LdqWR0UAAAAALP9M2j0G1Gv2h_o_pYpKQ-Obr6X";
?>