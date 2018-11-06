<?php
session_start();
unset ( $_SESSION['fromIndex'] );
if (!isset($_SESSION['loggedin'])){
    if (isset($_COOKIE['filmlist-remember-me'])) {
        $hash = $_COOKIE['filmlist-remember-me'];
        include 'api/config.php';
        $db = $config['db'];
        $link = mysqli_connect($db['servername'], $db['username'], $db['password'], $db['dbname']);
        if ($link) {
            $stmt = mysqli_prepare($link, "SELECT hash, user_id FROM persistent_logins1 WHERE hash=?");
            mysqli_stmt_bind_param($stmt, "s", $_COOKIE['filmlist-remember-me']);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $hashFromDB, $useridFromDB);
            mysqli_stmt_fetch($stmt);
            mysqli_stmt_close($stmt);
            if ($hash == $hashFromDB) {
                $stmt = mysqli_prepare($link, "SELECT username, api_key FROM user_test1 WHERE id=?");
                mysqli_stmt_bind_param($stmt, "i", $useridFromDB);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_bind_result($stmt, $usernameFromDB, $apikeyFromDB);
                mysqli_stmt_fetch($stmt);
                mysqli_stmt_close($stmt);
                $_SESSION['loggedin'] = TRUE;
                $_SESSION['username'] = $usernameFromDB;
                $_SESSION['userid'] = $useridFromDB;
                $_SESSION['apikey'] = $apikeyFromDB;
            } else {
                $_SESSION['fromIndex'] = TRUE;
                header ( "location: login.php" );
                exit ();
            }
        }
        mysqli_close($link);
    } else {
        $_SESSION['fromIndex'] = true;
        header ( "location: login.php" );
        exit ();
    }
} else if (isset($_POST["logoutButton"])) {
    if (isset($_COOKIE['filmlist-remember-me'])) {
        include 'api/config.php';
        $db = $config['db'];
        $link = mysqli_connect($db['servername'], $db['username'], $db['password'], $db['dbname']);
        if ($link) {
            $stmt = mysqli_prepare($link, "DELETE FROM persistent_logins1 WHERE hash=?");
            mysqli_stmt_bind_param($stmt, "s", $_COOKIE['filmlist-remember-me']);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
        mysqli_close($link);
        unset($_COOKIE['filmlist-remember-me']);
        setcookie('filmlist-remember-me', null, -1, '/');
    }
    session_destroy();
    unset($_SESSION);
    unset($_SESSION['loggedin']);
    $_SESSION['loggedout'] = true;
    $_SESSION['fromIndex'] = true;
    header ( "location: login.php" );
    exit ();
}
?>
<!doctype html>
<html>
    <head>
        <title>Filmlist</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="Filmlist">
        <meta name="author" content="Marcus Kivi">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"
              integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm"
              crossorigin="anonymous">
        <link href="./styles.css" rel="stylesheet" type="text/css">
        <!--<link href="/static/css/main.af1f5160.css" rel="stylesheet">-->
    </head>
    <body>
        <div class="container">
            <div id="user-box">
                <h3 id="username"><?php echo strtolower($_SESSION['username'])?><span id="user-box-dropdown">&#x25BC;</span></h3>
                <div id="user-hovered" class="card">
                    <p>API key: <span id="apikey"><?php echo $_SESSION['apikey']?></span></p>
                    <form id="logout" method="post">
                        <div class="form-group">
                            <input type="submit" name="logoutButton" class="btn btn-danger" value="Log out">
                        </div>
                    </form>
                </div>
            </div>
            <noscript>You need to enable JavaScript to run this app.</noscript>
            <div id='root'></div>
        </div>
        <!--  <script type="text/babel" src="./App.js"></script>-->
        <!--<script type="text/javascript" src="http://localhost:3991/static/js/bundle.js"></script>-->
        <script type="text/javascript" src="https://marcuskivi.com/test/static/js/bundle.js"></script>
    </body>
</html>
