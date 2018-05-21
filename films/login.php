<?php
session_start();
$message = '';
if (isset($_POST['username'])) {
	$username = trim($_POST['username']);
	$_SESSION['username'] = $username;
}
if (isset($_POST['signupButton'])) {
	header("location: signup.php");
	exit ();
}
if (isset($_POST['username']) && isset($_POST['password'])){
	$password = $_POST['password'];
	$link = mysqli_connect("localhost", "make91", "x", "make91");
	if ($link) {
		$stmt = mysqli_prepare($link, "SELECT password, id, api_key FROM user_test1 WHERE username=?");
		mysqli_stmt_bind_param($stmt, "s", $username);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_bind_result($stmt, $passwordFromDB, $idFromDB, $apikeyFromDB);
		mysqli_stmt_fetch($stmt);
		mysqli_stmt_close($stmt);
		if (password_verify($password, $passwordFromDB)) {
			$_SESSION['loggedin'] = TRUE;
            $_SESSION['userid'] = $idFromDB;
            $_SESSION['apikey'] = $apikeyFromDB;
			// persistent login
            if (isset($_POST['rememberme'])) {
                $hash = base64_encode(random_bytes(50));
                $expires = time() + (86400 * 3650);
                $expiresSQL = date('Y-m-d G:i:s', $expires);
                setcookie('filmlist-remember-me', $hash, $expires, "/");
                $clientIP = include 'getIP.php';
                $stmt = mysqli_prepare($link, "INSERT INTO persistent_logins1 (hash, user_id, ip, expires) VALUES (?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt, "siss", $hash, $idFromDB, $clientIP, $expiresSQL);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
		} else {
			$message = "Wrong username and password!";
		}
	}
	mysqli_close($link);
}
if (isset($_SESSION['loggedin'])){
	header("location: .");
	exit ();
}
if (isset($_SESSION['loggedout'])) {
	$message = "You have been logged out.";
	unset ( $_SESSION ['loggedout'] );
}
if (!isset($_SESSION['fromIndex']) && isset($_COOKIE['filmlist-remember-me'])) {
	header("location: .");
	exit ();
}
?>
<!doctype html>
<html>
<head>
<title>Log in</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="Login form">
<meta name="author" content="Marcus Kivi">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"
    integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm"
    crossorigin="anonymous">
<link href="./styles.css" rel="stylesheet" type="text/css">
</head>
<body>
<div class="container">
	<div class="login-form">
		<form method="post">
			<h2 class="text-center">Log in</h2>
			<?php $message and printf("<p class='alert-warning'>%s</p>",$message) ?>
			<div class="form-group" tooltip="username: user">
				<input type="text" class="form-control" placeholder="Username" autofocus="autofocus" name="username" required="required" value="<?php print(htmlentities($_SESSION['username'], ENT_QUOTES, "UTF-8"));?>">
			</div>
			<div class="form-group" tooltip="password: user">
				<input type="password" class="form-control" placeholder="Password" name="password" required="required">
			</div>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="rememberCheck" name="rememberme">
                <label class="form-check-label" for="rememberCheck">Remember me</label>
            </div>
			<div class="form-group">
				<input type="submit" name="loginButton" class="btn btn-primary btn-block" value="Log in">
			</div>
		</form>
		<form method="post">
			<div class="form-group mt-5">
				 <input type="submit" name="signupButton" class="btn btn-warning btn-block" value="Sign up">
			</div>
		</form>
	</div>
</div>
</body>
</html>
