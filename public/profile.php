<?php
ob_start();
require_once 'includes/config.php';
require_once 'includes/global.php';

if (!($user -> IsLogged($odb)))
{
	header('Location: login.php');
	die();
}

//Get Details
$SQLGetUserInfo = $odb -> prepare("SELECT * FROM `accounts` WHERE `id` = :id LIMIT 1");
$SQLGetUserInfo -> execute(array(':id' => $_SESSION['rID']));
$userInfo = $SQLGetUserInfo -> fetch(PDO::FETCH_ASSOC);
$userID = $userInfo['id'];
$userName = $userInfo['username'];
$email = $userInfo['email'];
$isVerified = $userInfo['isverified'];
$memberSince = $userInfo['acdate'];
$sharing = $userInfo['ispublic'];

?>


<?

if ( isset( $_POST['updateProfile'] ) ) {
	$password = $_POST['password1'];
	$repeat = $_POST['password2'];
	$userIDx = trim($_REQUEST['userID']);
	$sharingEnabled =  trim($_REQUEST['sharingEnabled']);
	
	/*echo $password;
	echo "xxx";
	echo $repeat;	echo "xxx";
	echo $userIDx;	echo "xxx";
	echo $sharingEnabled;*/
	
	$errors = array();
	$success = array();
	if ($password != $repeat)
	{
		$errors[] = 'Passwords did not match.';
	}
	
	if (!is_numeric($userIDx) || (!is_numeric($sharingEnabled)))
	{
		$errors[] = 'Invalid UserID or Sharing Enabled Value.';
	}	
	
	if (($sharingEnabled !=1) && ($sharingEnabled != 0))
	{
		$errors[] = 'Invalid Sharing Enabled Value';
	}

	if (empty($errors))
	{
		if (($_SESSION['rID']) == $userIDx)
		{
			if ($sharing != $sharingEnabled){
			$statement = $odb->prepare("UPDATE accounts SET `ispublic` = :sharing WHERE `id` =:id");
			$statement->execute(array(":sharing" => $sharingEnabled, ":id" => $userIDx));
			$success[] = 'Sharing Settings have been updated. To finish apply changes click <a href="profile.php">here</a>';
			}
			
			if (($password == $repeat) && ($password != ''))
			{
					if (strlen($password) < 6) {
						echo 'The password you have entered is too short. You need a minimum of 6 characters.';
					} else {
							$statement = $odb->prepare("UPDATE accounts SET password=:password WHERE id=:id");
							$statement->execute(array(":password" => md5($password), ":id" => $userIDx ));
							$success[] = 'Password has been updated.';
						}
			}

		}
		else{
			echo "You can not modify another users profile.";
		}
		foreach($success as $success)
		{
			echo '<div class="alert alert-success">'.$success.'</div><br />';
		}
		echo '</div>';
	}else {
		foreach($errors as $error)
		{
			echo '<div class="alert alert-danger">'.$error.'</div><br />';
		}
		echo '</div>';

	}

}

?>

<?php
	
			include ('templates/header.php');
			echo '<title>MyAltCoins - Profile Settings</title><div class="container">
       	    <h1 align="center">Account Settings</h1>
			<form class="form" id="wrap" action="profile.php" method="post">
			<div id="content">
				<h3>Username: '.$userName.'</h3>
				<h3>Email: '.$email.'</h3>
				<h3>Email Verified: '.($isVerified = 1 ? "Yes" : "No") .'</h3>
				<h3>Joined: '.date('Y-m-d', $memberSince).'</h3>
				<div style="display: inline;">
	            <h3>Sharing Enabled:</h3>
	            <select name="sharingEnabled" id="sharingEnabled" style="color: black;">
				    <option value="1" '.($sharing == 1 ? "selected" : "").'>Yes</option>
				    <option value="0" '.($sharing == 0 ? "selected" : "").'>No</option>
				</select>
				</div>
				<h3>Update Password:</h3>
				<input style="color: black;" type="password" name="password1" id="password1" />
				<h3>Confirm Password:</h3>
				<input style="color: black;" type="password" name="password2" id="password2"/>
				<input type="hidden" name="userID" value='.$userID.'>
				<p></p>
          	    <input type="submit" name="updateProfile" dcvalue="" class="btn btn-primary" value="Update">
          	     </p>
          	     </div>
			</form>
			</div>
			';
			
			
			echo '<div class="container">
	    <div align="center">
        <h1>Portfolio Sharing</h1>
        <h3>You can share your Portfolio with others by giving them the link below.</h3><h3>They will only be able to SEE your Portfolio, they will not be able to make any changes. For the link to work, make sure you enable sharing above.</h3>
        <br>
        	<h3>Your Portfolio Sharing Link is:</h3>
        <br>
			<h3><a href="https://'.$site_url.'view.php?u='.$userID.'">https://'.$site_url.'view.php?u='.$userID.'</a></h3>';
			echo '</div>
    </div>

    </div>
    ';
    include ('templates/footer.php');
	
?>

