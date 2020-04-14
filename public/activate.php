<?php
ob_start();
session_start();
require_once 'includes/config.php';
require_once 'includes/global.php';

if ($user -> IsActive($odb))
{
	header('Location: dashboard.php');
	die();
}
?>
<!DOCTYPE html>
<html lang="en">
 <title><?php echo $site_title; ?> - Activate</title>   
<body>
    <div class="container">
      <div class="wrap-main">

            <?if (!isset($_GET['confirm']) && !isset($_GET['u'])) {
						echo '
						We have sent a confirmation mail to your inbox. Please check your inbox and click on the confirmation link to activate your account.
						If you didn\'t receive a mail from us yet, please wait 15 minutes. Don\'t forget to check your Junk folder aswell';
					} else {
						$confirmation = htmlspecialchars($_GET['confirm']);
						$username = htmlspecialchars($_GET['u']);
						$SQLCheckActivation = $odb -> prepare("SELECT COUNT(*) FROM `accounts` WHERE `username` = :username AND `activation` = :activation AND `isverified` = 0");
						$SQLCheckActivation -> execute(array(':username' => $username, ':activation' => $confirmation));
						$activationCheck = $SQLCheckActivation -> fetchColumn(0);
						if ($activationCheck == 1)
							{
								$SQL = $odb -> prepare("UPDATE `accounts` SET `isverified` = 1 WHERE `username` = :username");
								$SQL -> execute(array(':username' => $username));
								echo 'Your account has been verified! You will be redirected to the login page shortly.<meta http-equiv="refresh" content="5;url=login.php">';
							} else {
								echo 'Invalid activation link!';
							}
					}
			?>
        </div>
    </div>                          
    <!-- /.container -->

</body>
</html>