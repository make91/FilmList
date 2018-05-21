<?php
session_start();
$message = '';

if (isset($_POST['backButton'])) {
	header("location: login.php");
	exit ();
}
if (isset($_POST['username'])) {
	$username = trim($_POST['username']);
	$_SESSION['username'] = $username;
}
if (isset($_POST['username']) && isset($_POST['password']) && isset($_POST['password2'])) {
	$password = $_POST['password'];
	$password2 = $_POST['password2'];
	if (strlen ($username) < 1 || strlen ($username) > 30) {
		$message = "Username must be 1-30 characters long";
	} else if ($password != $password2) {
		$message = "Passwords do not match!";
	} else if (strlen ($password) < 4) {
		$message = "Password must be at least 4 characters long";
	} else {
		$hash = password_hash($password, PASSWORD_BCRYPT);
        include 'api/config.php';
        $db = $config['db'];
        $link = mysqli_connect($db['servername'], $db['username'], $db['password'], $db['dbname']);
		if ($link) {
			try {
				$stmt = mysqli_prepare($link, "SELECT username FROM user_test1 WHERE username=?");
				mysqli_stmt_bind_param($stmt, "s", $username);
				mysqli_stmt_execute($stmt);
				mysqli_stmt_store_result($stmt); 
				$rows = mysqli_stmt_num_rows($stmt);
				mysqli_stmt_close($stmt);
				if ($rows > 0) {
					$message = "The username is taken";
				} else {
                    $apikey = base64_encode(random_bytes(15));
                    $removeChars = array('+','/','=');
                    $apikey = str_replace($removeChars, '', $apikey);
					$stmt = mysqli_prepare($link, "INSERT INTO user_test1 (username, password, api_key) VALUES (?, ?, ?)");
					mysqli_stmt_bind_param($stmt, "sss", $username, $hash, $apikey);
					mysqli_stmt_execute($stmt);
					$id = mysqli_stmt_insert_id($stmt);
					mysqli_stmt_close($stmt);
					$_SESSION['loggedin'] = TRUE;
					$_SESSION['userid'] = $id;
                    $_SESSION['apikey'] = $apikey;
				}
			} catch(Exception $e) {
				$message = $e->getMessage();
			}
		}
		mysqli_close($link);
	}
}
if (isset($_SESSION['loggedin'])){
	header("location: .");
	exit ();
}
?>
<!doctype html>
<html>
<head>
<title>Sign up</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="Signup form">
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
			<h2 class="text-center">Sign up</h2>
			<?php $message and printf("<p class='alert-warning'>%s</p>",$message) ?>
			<div class="form-group">
				<input type="text" class="form-control" placeholder="Username" autofocus="autofocus" name="username" required="required" value="<?php print(htmlentities($_SESSION['username'], ENT_QUOTES, "UTF-8"));?>">
			</div>
			<div class="form-group">
				<input type="password" class="form-control" placeholder="Password" name="password" required="required">
			</div>
			<div class="form-group">
				<input type="password" class="form-control" placeholder="Confirm password" name="password2" required="required">
			</div>
			<div class="form-group">
				 <input type="submit" name="signupButton" class="btn btn-primary btn-block" value="Sign up">
			</div>
		</form>
		<form method="post">
			<div class="form-group mt-5">
				 <input type="submit" name="backButton" class="btn btn-warning btn-block" value="Go back">
			</div>
		</form>
	</div>
</div>
</body>
</html>
