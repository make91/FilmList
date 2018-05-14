<?php
require 'vendor/autoload.php';
include 'config.php';
$app = new Slim\App(["settings" => $config]);
session_start();
$container = $app->getContainer();

$container['db'] = function ($c) {
   
   try{
       $db = $c['settings']['db'];
       $options  = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
       PDO::ATTR_DEFAULT_FETCH_MODE                      => PDO::FETCH_ASSOC,
       );
       $pdo = new PDO("mysql:host=" . $db['servername'] . ";dbname=" . $db['dbname'],
       $db['username'], $db['password'],$options);
       return $pdo;
   }
   catch(\Exception $ex){
       return $ex->getMessage();
   }
};
//this enables CORS
header("Access-Control-Allow-Origin: *");
 //insert new film
$app->post('/films', function ($request, $response) {
   try{
       $con = $this->db;
       $sql = "INSERT INTO films(date_seen, title, user_id) VALUES (:date_seen,:title,:user_id)";
       $pre  = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
       $values = array(
       ':date_seen' => $request->getParam('date_seen'),
       ':title' => $request->getParam('title'),
       ':user_id' => $request->getParam('user_id')
       );
       $result = $pre->execute($values);
       return $response->withJson(array('status' => 'Film created'),200);
   }
   catch(\Exception $ex){
       return $response->withJson(array('error' => $ex->getMessage()),422);
   }
});
//list all films
$app->get('/films', function ($request,$response) {
   try{
       $con = $this->db;
       $userid = $request->getQueryParam("user_id");
       $sql = "SELECT * FROM films WHERE user_id = " . $userid;
//       $pre  = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
//       if (isset($_SESSION['userid'])) {
//           $userid = $_SESSION['userid'];
//       } else {
//           $userid = 1;
//       }
//        $values = array(
//        ':user_id' => $userid);
//       $pre->execute($values);
       $result = null;
       foreach ($con->query($sql) as $row) {
           $result[] = $row;
       }
       if($result){
           return $response->withJson(array('status' => 'true','result'=>$result),200);
       }else{
           return $response->withJson(array('status' => 'Films Not Found'),422);
       }   
   }
   catch(\Exception $ex){
       return $response->withJson(array('error' => $ex->getMessage()),422);
   }
});
//show one film
$app->get('/films/{id}', function ($request,$response) {
    try{
        $id = $request->getAttribute('id');
        $con = $this->db;
        $sql = "SELECT * FROM films WHERE id = :id";
        $pre  = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $values = array(
        ':id' => $id);
        $pre->execute($values);
        $result = $pre->fetch();
        if($result){
            return $response->withJson(array('status' => 'true','result'=> $result),200);
        }else{
            return $response->withJson(array('status' => 'Film Not Found'),422);
        }
    }
    catch(\Exception $ex){
        return $response->withJson(array('error' => $ex->getMessage()),422);
    }
});
//update a film
$app->put('/films/{id}', function ($request,$response) {
    try{
        $id = $request->getAttribute('id');
        $con = $this->db;
        $sql = "UPDATE films SET date_seen=:date_seen,title=:title,user_id=:user_id WHERE id = :id";
        $pre  = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $values = array(
        ':date_seen' => $request->getParam('date_seen'),
        ':title' => $request->getParam('title'),
        ':user_id' => $request->getParam('user_id'),
        ':id' => $id
        );
        $result =  $pre->execute($values);
        if($result){
            return $response->withJson(array('status' => 'Film Updated'),200);
        }else{
            return $response->withJson(array('status' => 'Film Not Found'),422);
        }
    }
    catch(\Exception $ex){
        return $response->withJson(array('error' => $ex->getMessage()),422);
    }
});
//delete a film
$app->delete('/films/{id}', function ($request,$response) {
    try{
        $id = $request->getAttribute('id');
        $con = $this->db;
        $sql = "DELETE FROM films WHERE id = :id";
        $pre  = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $values = array(
        ':id' => $id);
        $result = $pre->execute($values);
        if($result){
            return $response->withJson(array('status' => 'Film Deleted'),200);
        }else{
            return $response->withJson(array('status' => 'Film Not Found'),422);
        }
    }
    catch(\Exception $ex){
        return $response->withJson(array('error' => $ex->getMessage()),422);
    }
});

$app->any('/', function ($request,$response) use ($app) {
//  $url = "secure.php";
//  return $response->withRedirect($url);
//    $app->render('signup.php');
    require "secure.php";
});
$app->any('/secure.php', function ($request,$response) use ($app) {
//  $url = "secure.php";
//  return $response->withRedirect($url);
//    $app->render('signup.php');
    require "secure.php";
});
$app->any('/login.php', function ($request,$response) use ($app) {
//  $url = "secure.php";
//  return $response->withRedirect($url);
//    $app->render('signup.php');
    require "login.php";
});
$app->any('/signup.php', function ($request,$response) use ($app) {
//  $url = "secure.php";
//  return $response->withRedirect($url);
//    $app->render('signup.php');
    require "signup.php";
});

//$app->get('/secure.php', function ($request,$response) {
////  $url = '..';
////  return $res->withRedirect($url);
//    $app->render('secure.php');
//});
//$app->get('/login.php', function ($req, $res, $args) {
////  $url = '..';
////  return $res->withRedirect($url);
//    $app->render('login.php');
//});
$app->run();