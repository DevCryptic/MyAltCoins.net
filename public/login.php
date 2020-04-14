<?php
ob_start();
require_once 'includes/config.php';
require_once 'includes/global.php';
require_once('includes/recaptchalib.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title><?php echo $site_title; ?> - Login</title> 
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="keywords" content="MyAltCoins, altcoin, currency, crypto, investment, track, tracking, price"/>
	<meta name="description" content="MyAltCoins is an easy to use investment tracking solution. Keep track track of your Bitcoin and Altcoin investments with ease!.">
	<meta name="author" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="./assets/favicon.ico">  
</head>
	<? include('templates/header_guest.php');?>

<body class="bg_login">
    <main id="swapper">
        <section class="login">
            <div class="container">
                <div class="row div_row">
                    <div class="box-login col-sm-6 col-sm-offset-3">
                        <form action="login.php" method="POST" name="form-login">
                            <?php 
								if (!($user -> IsLogged($odb)))
									{
									if (isset($_POST['loginBtn']))
									{
										$username = $_POST['username'];
										$password = $_POST['password'];
										$errors = array();
										if (empty($username) || empty($password))
										{
											$errors[] = 'Please enter your username and password.';
										}
										if (!ctype_alnum($username) || strlen($username) < 4 || strlen($username) > 15)
										{
											$errors[] = 'Username must be 4-15 characters and alphanumeric only!';
										}

										if (empty($errors))
										{
											$SQLCheckLogin = $odb -> prepare("SELECT COUNT(*) FROM `accounts` WHERE `username` = :username AND `password` = :password");
											$SQLCheckLogin -> execute(array(':username' => $username, ':password' => MD5($password)));
											$countLogin = $SQLCheckLogin -> fetchColumn(0);
											if ($countLogin == 1)
											{
												$SQLGetInfo = $odb -> prepare("SELECT `id`, `username` ,`email`, `activation`, `isverified` FROM `accounts` WHERE `username` = :username AND `password` = :password");
												$SQLGetInfo -> execute(array(':username' => $username, ':password' => MD5($password)));
												$userInfo = $SQLGetInfo -> fetch(PDO::FETCH_ASSOC);
												$status = $userInfo['isverified'];
												$userid = $userInfo['id'];
												$email = $userInfo['email'];
												$activation = $userInfo['activation'];
												$userip = $_SERVER['REMOTE_ADDR'];
												if ($status == 0)
												{
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

													echo '<div class="alert alert-danger" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button> Your account is not verified. An account verification email was sent to '.$email.' upon registration. Check your junk/spam folder. </div>';
												}
												elseif ($status == 1)
												{
												$username = $userInfo['username'];
													$_SESSION['rUsername'] = $userInfo['username'];
													$_SESSION['rID'] = $userInfo['id'];
													$session_code = hash("sha512", $userid.$userip.$email.'85a689a6v8');
													$_SESSION['rSecret'] = $session_code;
													echo '<div class="alert alert-success" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button> You have logged in successfully. Redirecting..
														</div><meta http-equiv="refresh" content="3;url=dashboard.php">';
												}

											}
											else
											{
												echo '<div class="alert alert-danger" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button> Incorrect username or password entered!</div>';
											}
										}
										else
										{
											echo '<div class="alert alert-danger" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
											foreach($errors as $error)
											{
												echo '-'.$error.'<br />';
											}
											echo '</div>';
										}
									}
								}
								else
								{
									header('location: dashboard.php');
								}
							?>
                              <div class="login-page">
        <div class="form">
			<h3 style="color: black;">MyAltCoins.net</h3> 
            <p style="color: black;">MyAltCoins.net is an easy to use, simple tracker for all of your crypto currency investments. We also offer an easy way to share your portfolio, click <a href="view.php?u=1">here</a> for an example.</p>
            <form class="login-form" method="POST" name="form-login">
                <input  style="color: black;" type="text" name="username" id="username" placeholder="Username" required/>
                <input style="color: black;" type="password" name="password" id="password" placeholder="Password" required/>
                <button name="loginBtn" id="loginBtn">Login</button>
            </form>
                <p class="message" style="font-size: 15px;">Forgot your password? <a href="reset.php">Click here!</a></p>
                <p class="message" style="font-size: 15px;">Not a member? <a href="register.php">Click here!</a>
                </p>
</form>
        </div>
    </div>

    <script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>

</body>

</html>
