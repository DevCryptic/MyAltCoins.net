<?php
class users
{
	function IsLogged($odb)
	{
		@session_start();
		if (isset($_SESSION['rUsername'], $_SESSION['rID'], $_SESSION['rSecret']))
		{
			$username = $_SESSION['rUsername'];
			$userid = $_SESSION['rID'];
			$SQLCheckLogin = $odb -> prepare("SELECT COUNT(*) FROM `accounts` WHERE `username` = :username AND `id` = :id LIMIT 1");
			$SQLCheckLogin -> execute(array(':username' => $username, ':id' => $userid));
			$countLogin = $SQLCheckLogin -> fetchColumn(0);
			if ($countLogin == 1)
			{
				$SQLGetInfo = $odb -> prepare("SELECT `email` FROM `accounts` WHERE `username` = :username AND `id` = :id LIMIT 1");
				$SQLGetInfo -> execute(array(':username' => $username, ':id' => $userid));
				$userInfo = $SQLGetInfo -> fetch(PDO::FETCH_ASSOC);
				$email = $userInfo['email'];
				$userip = $_SERVER['REMOTE_ADDR'];
				$login_check = hash("sha512", $userid.$userip.$email.'85a689a6v8');
				$login_string = $_SESSION['rSecret'];
				if ($login_check == $login_string)
				{
					return true;
				} else {
					return false;
				}
			} else {
				return false;
			}
		}
		else
		{
			return false;
		}
	}

	function IsAdmin($odb)
	{
		@session_start();
		if (isset($_SESSION['aUsername'], $_SESSION['aID'], $_SESSION['aSecret']))
		{
			$username = $_SESSION['aUsername'];
			$userid = $_SESSION['aID'];

			$SQLCheckLogin = $odb -> prepare("SELECT COUNT(*) FROM `admins` WHERE `username` = :username AND `id` = :id LIMIT 1");
			$SQLCheckLogin -> execute(array(':username' => $username, ':id' => $userid));
			$countLogin = $SQLCheckLogin -> fetchColumn(0);
			if ($countLogin == 1)
			{
				$SQLGetInfo = $odb -> prepare("SELECT `email` FROM `admins` WHERE `username` = :username AND `id` = :id LIMIT 1");
				$SQLGetInfo -> execute(array(':username' => $username, ':id' => $userid));
				$userInfo = $SQLGetInfo -> fetch(PDO::FETCH_ASSOC);
				$email = $userInfo['email'];
				$userip = $_SERVER['REMOTE_ADDR'];
				$login_check = hash("sha512", $userid.$userip.$email.'85a689a6v8');
				$login_string = $_SESSION['aSecret'];
				if ($login_check == $login_string)
				{
					if (($username == "cryptic") && ($userid == 1)) {
						return true;
					}
					else {
						return false;
					}
				} 
				else {
					return false;
				}
			} else {
				return false;
			}
		}
		else
		{
			return false;
		}
	}
	function IsActive($odb)
	{
		$SQL = $odb -> prepare("SELECT `isverified` FROM `accounts` WHERE `id` = :id");
		$SQL -> execute(array(':id' => $_SESSION['rID']));
		$isactive = $SQL -> fetchColumn(0);
		if ($isactive == 1)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

}
class ustat
{
	function getEmail($odb)
	{
		$SQL = $odb -> prepare("SELECT `email` FROM `accounts` WHERE `id` = :id");
		$SQL -> execute(array(':id' => $_SESSION['rID']));
		$result = $SQL -> fetchColumn(0);
		return $result;
	}
	
	function getAccounts($odb)
	{
		$SQL = $odb -> query("SELECT COUNT(*) FROM `accounts`");
		return $SQL->fetchColumn(0);
	}
}
class rvpn
{

	function sendMail($odb, $subject, $content, $target, $username)
	{
		$from = $this -> getSiteMail($odb);
		$fromname = $this -> getSiteTitle($odb);
		$mail = new PHPMailer;
		$mail->isSendmail();
		$mail->setFrom($from, $fromname);
		$mail->addReplyTo($from, $fromname);
		$mail->addAddress($target, $username);
		$mail->Subject = $subject;
		$mail->msgHTML($content);
		if (!$mail->send()) {
			return false;
		} else {
			return true;
		}
	}
	function genPass($length = 8)
	{
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
		$randomString .= $characters[rand(0, strlen($characters) - 1)];
		}
		return $randomString;
	}
}

?>
