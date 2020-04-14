<?php
ob_start();
require_once 'includes/config.php';
require_once 'includes/global.php';

function generateRandomString($length = 20) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
?>

<?
if(!empty($_GET['email']) && filter_var($_GET['email'], FILTER_VALIDATE_EMAIL) && !empty($_GET['token'])) 
{
	$email = $_GET['email'];
	$token = $_GET['token'];
	
	$SQLGetInfo = $odb -> prepare("SELECT `dateRequested`, `isvalid`, `ipaddress` FROM `pwresets` WHERE `email` = :email AND `token` = :token");
	$SQLGetInfo -> execute(array(':email' => $email, ':token' => $token));
	$userInfo = $SQLGetInfo -> fetch(PDO::FETCH_ASSOC);
	$dateRequested = $userInfo['dateRequested'];
	$isvalid = $userInfo['isvalid'];
	$ip = $userInfo['ipaddress'];
	if ($isvalid==0)
	{
	echo '<div class="alert alert-danger" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button> Invalid Email/Token. The password reset link may have already been used or has expired.</div>';
	}
	else
	{
		$npassword = generateRandomString();
		$SQLUpdate = $odb -> prepare("UPDATE `accounts` SET `password` = :password WHERE `email` = :email");
		$SQLUpdate -> execute(array(':password' => MD5($npassword),':email' => $email));
		
		$getUsername = $odb -> prepare("SELECT `username` FROM `accounts` WHERE `email` = :email");
		$getUsername -> execute(array(':email' => $email));
		$username= $getUsername->fetchColumn();


		$Invalidate = $odb -> prepare("UPDATE `pwresets` SET `isvalid` = 0 WHERE `email` = :email AND `token` = :token");
		$Invalidate -> execute(array(':email' => $email,':token' => $token));
		
		$content = 'Hello '.$username.', we\'ve got you covered!<br>
												Your new password is: '.$npassword.'<br>
												Login and change it immediately.<p>
												You are recieving this email because a password reset was requested by '.$ip.'. If you did not request a password reset please disregard this email.';
											$from = $rsuser;
											$fromname = $site_title;
											$mail = new PHPMailer;
											if ($rvpnmailer == 'php') {
												$mail->isSendmail();
											} else {
												$mail->isSMTP();
												$mail->SMTPSecure = $smtpauthtype;
												$mail->Host = $rshost;
												$mail->Port = $rsport;
												$mail->SMTPAuth = $smtpauthstat;
												$mail->Username = $rsuser;
												$mail->Password = $rspass;
											}
											$mail->setFrom($from, $fromname);
											$mail->addReplyTo($from, $fromname);
											$mail->addAddress($email, $username);
											$mail->Subject = 'Your new MyAltCoins Password';
											$mail->msgHTML($content);
											$mail->send();

		echo '<div class="alert alert-success" data-uk-alert><a href="#" class="uk-alert-close uk-close"></a> You have successfully reset password. Check your email for further details.</div>';


	}

}
else {
	

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title><?php echo $site_title; ?> - Reset Password</title> 
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="./assets/favicon.ico">  
	<script src='https://www.google.com/recaptcha/api.js'></script>

</head>
<body class="bg_register">
	<? include('templates/header_guest.php');?>
	<main id="swapper">
		<section class="login register">
			<div class="container">
				<div class="row div_row">
					<div class="div_logo" align="center">
					</div>

					<form action="#" class="login-form" method="POST" name="form-register">
						<?php 
							if (isset($_POST['sm_account']))
							{
								$captcha = $_POST['g-recaptcha-response'];
								$response=file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".$rprivatekey."&response=".$captcha."&remoteip=".$_SERVER['REMOTE_ADDR']);
								if($response.success==false || !($captcha))
								{
									echo '<div class="uk-alert uk-alert-danger" data-uk-alert><a href="#" class="uk-alert-close uk-close"></a> You have entered an invalid captcha code.</div>';

								} else {
									$email = $_POST['email'];
									$errors = array();
									if (empty($email))
									{
										$errors[] = 'Please fill in all required fields.';
									}
									if (!filter_var($email, FILTER_VALIDATE_EMAIL))
									{
										$errors[] = 'You have entered an invalid e-mail address.';
									}

									$checkEmail = $odb -> prepare("SELECT * FROM `accounts` WHERE `email`= :email");
									$checkEmail -> execute(array(':email' => $email));
									$countEmail = $checkEmail -> rowCount();
									if ($countEmail == 0)
									{
										$errors[] = 'The email you have entered is not linked to an account.';
									}
									if (empty($errors))
									{
									#getusername
									
									$getUsername = $odb -> prepare("SELECT `username` FROM `accounts` WHERE `email` = :email");
									$getUsername -> execute(array(':email' => $email));
									$username= $getUsername->fetchColumn();

									#
										if ($rvpnregconfirmation == 1) {
											$token = generateRandomString();
											$ip = getRealIpAddr();
											$insertReset = $odb -> prepare("INSERT INTO `pwresets` VALUES(NULL, :email, :token, UNIX_TIMESTAMP(), 1, :ipaddr)");
											$insertReset -> execute(array(':email' => $email, ':token' => $token, ':ipaddr' => $ip));

											$confirmationlink = 'https://'.$site_url.'reset.php?email='.$email.'&token='.$token.'';
											
											$content = 'Hello '.$username.', have you forgotten your password? Don\'t worry, it happens to the best of us.
												<br>
												Please <a href="'.$confirmationlink.'">click here</a> to reset your password.
												<p>
												If the link above doest not work, please copy and paste the following link in your browser:
												<a href="'.$confirmationlink.'" target="_blank">'.$confirmationlink.'</a>
												<p>
												You are recieving this email because a password reset was requested by <strong>'.$ip.'</strong>. If you did not request this password reset please disregard this email.
												</body>
												</html>';
											$from = $rsuser;
											$fromname = $site_title;
											$mail = new PHPMailer;
											if ($rvpnmailer == 'php') {
												$mail->isSendmail();
											} else {
												$mail->isSMTP();
												$mail->SMTPSecure = $smtpauthtype;
												$mail->Host = $rshost;
												$mail->Port = $rsport;
												$mail->SMTPAuth = $smtpauthstat;
												$mail->Username = $rsuser;
												$mail->Password = $rspass;
											}
											$mail->setFrom($from, $fromname);
											$mail->addReplyTo($from, $fromname);
											$mail->addAddress($email, $username);
											$mail->Subject = 'MyAltCoins Password Reset Request';
											$mail->msgHTML($content);
											$mail->send();

							  echo '<div class="alert alert-success" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button> You have successfully submitted a reset password request. An email containing instructions has been sent to '.$email.'. Be sure to check the junk/spam folder.</div>'; 
										}
								}
									else
									{
										echo '<div class="alert alert-danger" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
										foreach($errors as $error)
										{
											echo '- '.$error.'<br />';
										}
										echo '</div>';
									}
							}
						}
						?>
									 <div class="login-page">
        <div class="form">
                <input type="email" name="email" id="tb_email" style="color:black;" placeholder="Email Address" value="<?=isset($old_post['email'])?$old_post['email']:''?>" required/>
				<div class="g-recaptcha" data-sitekey="<?php echo $rpublickey; ?>"></div>
				<button id="sm_account" name="sm_account">Submit</button>
                <p class="message" style="font-size: 15px;">Already a member? <a href="login.php">Click Here</a>
                </p>
            </form>
        </div>
    </div>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>


				</div>
			</div>
		</section>
	</main>	

    <script type="text/javascript">
    	var recaptchaCallback = function(response) {
	        //console.log(response);
	    };

    </script>
</body>
</html>
<?}?>
