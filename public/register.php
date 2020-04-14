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

if ($user -> IsLogged($odb))
{
	header('Location: dashboard.php');
	die();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title><?php echo $site_title; ?> - Register</title> 
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
									$username = $_POST['username'];
									$password = $_POST['passwd'];
									$repeat = $_POST['rpasswd'];
									$email = $_POST['email'];

									$errors = array();
									if (empty($username) || empty($password) || empty($repeat) || empty($email))
									{
										$errors[] = 'Please fill in all required fields.';
									}
									$checkUsername = $odb -> prepare("SELECT * FROM `accounts` WHERE `username`= :username");
									$checkUsername -> execute(array(':username' => $username));
									$countUsername = $checkUsername -> rowCount();
									if ($countUsername != 0)
									{
										$errors[] = 'The username you have entered is already in use.';
									}
									$checkEmail = $odb -> prepare("SELECT * FROM `accounts` WHERE `email`= :email");
									$checkEmail -> execute(array(':email' => $email));
									$countEmail = $checkEmail -> rowCount();
									if ($countEmail != 0)
									{
										$errors[] = 'The email you have entered is already in use.';
									}
										if (strlen($_POST['username']) < 4) {
										$errors[] = 'The username you have entered is too short.';
										}
										if (strlen($_POST['passwd']) < 4) {
										$errors[] = 'The username you have entered is too short.';
										}
										if (strlen($_POST['username']) > 15) {
										$errors[] = 'The username you have entered is too long.';
										}
									if (!filter_var($email, FILTER_VALIDATE_EMAIL))
									{
										$errors[] = 'You have entered an invalid e-mail address.';
									}
									if (!ctype_alnum($username))
									{
										$errors[] = 'The username you have entered is invalid.';
									}
									if ($password != $repeat)
									{
										$errors[] = 'The passwords you have entered does not match.';
									}
									if (empty($errors))
									{
										if ($rvpnregconfirmation == 1) {
											$sha = hash("sha512", $password);
											$activation = generateRandomString();
											$insertUser = $odb -> prepare("INSERT INTO `accounts` VALUES(NULL, :username, :password, :email, 0, :activation, UNIX_TIMESTAMP(), 0)");
											$insertUser -> execute(array(':username' => $username, ':password' => MD5($password), ':email' => $email, ':activation' => $activation));
											$confirmationlink = 'https://'.$site_url.'activate.php?u='.$username.'&confirm='.$activation.'';
											$content = 'Please <a href="'.$confirmationlink.'" style="color:#36beec; text-decoration:underline;">click here</a> to validate your account.';
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
											$mail->Subject = 'MyAltCoins Account Confirmation';
											$mail->msgHTML($content);
											$mail->send();

							  echo '<div class="alert alert-success" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button> You have successfully registered an account. Please check your email for activation instructions. Be sure to check the junk/spam folder. It can take upto 10 minutes for the email to arrive.</div><meta http-equiv="refresh" content="10;url=login.php">'; 
										} else {
											$sha = hash("sha512", $password);
											$activation = generateRandomString();
											$insertUser = $odb -> prepare("INSERT INTO `accounts` VALUES(NULL, :username, :password, :email, 0, :activation, UNIX_TIMESTAMP(), 0)");
											$insertUser -> execute(array(':username' => $username, ':password' => MD5($password), ':email' => $email, ':activation' => $activation));
							  echo '<div class="alert alert-success" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button> You have successfully registered an account. Please check your email for activation instructions. Be sure to check the junk/spam folder.</div><meta http-equiv="refresh" content="10;url=login.php">'; 
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
                <input type="text" style="color:black;" name="username" id="username" placeholder="Username" required/>
                <input type="email" style="color:black;" name="email" id="email" value="" placeholder="Email" required/>
                <input type="password" style="color:black;" name="passwd" id="passwd" placeholder="Password" required/>
                <input type="password" style="color:black;" name="rpasswd" id="rpasswd" placeholder="Re-enter Password" required/>
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
