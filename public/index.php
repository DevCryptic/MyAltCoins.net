<?php
ob_start();
require_once 'includes/config.php';
require_once 'includes/global.php';

if (!($user -> IsLogged($odb)))
{
	header('Location: login.php');
	die();
}

if (($user -> IsActive($odb)))
{
	header('Location: dashboard.php');
	die();
}