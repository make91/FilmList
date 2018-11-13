<?php
//this enables CORS
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: X-Requested-With, Content-Type, Accept, Origin, Authorization');

require 'vendor/autoload.php';
include 'config.php';
$app = new Slim\App(["settings" => $config]);
session_start();
$container = $app->getContainer();

$container['db'] = function ($c) {

    try{
        $db = $c['settings']['db'];
        $options  = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                          PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                         );
        $pdo = new PDO("mysql:host=" . $db['servername'] . ";dbname=" . $db['dbname'],
                       $db['username'], $db['password'],$options);
        return $pdo;
    }
    catch(\Exception $ex){
        return $ex->getMessage();
    }
};

//insert new film
$app->post('/films', function ($request, $response) {
    try{
        $con = $this->db;
        $apikey = $request->getQueryParam("api_key");
        $sql = "INSERT INTO films(date_seen, title, year, tmdb_id, imdb_id, user_id) SELECT :date_seen,:title,:year,:tmdb_id,:imdb_id,u.id FROM user_test1 u WHERE u.api_key = :apikey";
        $pre  = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $values = array(
            ':date_seen' => $request->getParam('date_seen'),
            ':title' => $request->getParam('title'),
            ':year' => $request->getParam('year'),
            ':tmdb_id' => $request->getParam('tmdb_id'),
            ':imdb_id' => $request->getParam('imdb_id'),
            ':apikey' => $apikey
        );
        $pre->execute($values);
        $lastInsertID = $con->lastInsertId();
        if ($lastInsertID > 0) {
            return $response->withJson(array('status' => 'Film created'),200);
        } else {
            return $response->withJson(array('status' => 'Unable to add'),422);
        }
    }
    catch(\Exception $ex){
        return $response->withJson(array('error' => $ex->getMessage()),422);
    }
});
//list all films
$app->get('/films', function ($request,$response) {
    try{
        $con = $this->db;
        $apikey = $request->getQueryParam("api_key");
        $sql = "SELECT f.id, date_seen, title, year, tmdb_id, imdb_id FROM films f JOIN user_test1 u ON user_id = u.id WHERE api_key = :apikey ORDER BY date_seen DESC, f.id DESC";
        $pre  = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $values = array(
            ':apikey' => $apikey);
        $pre->execute($values);
        $result = $pre->fetchAll();  
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
        $apikey = $request->getQueryParam("api_key");
        $con = $this->db;
        $sql = "SELECT f.id, date_seen, title, year, tmdb_id, imdb_id FROM films f JOIN user_test1 u ON user_id = u.id WHERE f.id = :id AND api_key = :apikey";
        $pre  = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $values = array(
            ':id' => $id,
            ':apikey' => $apikey);
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
        $apikey = $request->getQueryParam("api_key");
        $con = $this->db;
        $sql = "UPDATE films f JOIN user_test1 u ON user_id = u.id SET date_seen=:date_seen,title=:title,year=:year,tmdb_id=:tmdb_id,imdb_id=:imdb_id WHERE f.id = :id AND api_key = :apikey";
        $pre  = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $values = array(
            ':date_seen' => $request->getParam('date_seen'),
            ':title' => $request->getParam('title'),
            ':year' => $request->getParam('year'),
            ':tmdb_id' => $request->getParam('tmdb_id'),
            ':imdb_id' => $request->getParam('imdb_id'),
            ':id' => $id,
            ':apikey' => $apikey
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
        $apikey = $request->getQueryParam("api_key");
        $con = $this->db;
        $sql = "DELETE f FROM films f JOIN user_test1 u ON user_id = u.id WHERE f.id = :id AND api_key = :apikey";
        $pre  = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $values = array(
            ':id' => $id,
            ':apikey' => $apikey);
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
//get data from TMDB
$app->get('/tmdb', function ($request,$response) {
    try{
        $apikey = $request->getQueryParam("api_key");
        $searchTerm = $request->getQueryParam("s");
        $searchTerm = rawurlencode($searchTerm);
        $con = $this->db;
        $sql = "SELECT id FROM user_test1 WHERE api_key = :apikey";
        $pre  = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $values = array(
            ':apikey' => $apikey);
        $pre->execute($values);
        $result = $pre->fetch();
        if($result && strlen($searchTerm) > 0) {
            include 'config.php';
            $keys = $config['api-keys'];
            $result = file_get_contents('https://api.themoviedb.org/3/search/movie?api_key='. $keys[tmdb] .'&language=en-US&page=1&include_adult=false&query='. $searchTerm);
            return $response->withJson(json_decode($result),200);
        }else{
            return $response->withJson(array('status' => 'Not Found'),422);
        }
    }
    catch(\Exception $ex){
        return $response->withJson(array('error' => $ex->getMessage()),422);
    }
});
//get IMDb link from TMDB
$app->get('/imdb', function ($request,$response) {
    try{
        $apikey = $request->getQueryParam("api_key");
        $id = $request->getQueryParam("id");
        $id = rawurlencode($id);
		$id = preg_replace('~\D~', '', $id);
        $con = $this->db;
        $sql = "SELECT id FROM user_test1 WHERE api_key = :apikey";
        $pre  = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $values = array(
            ':apikey' => $apikey);
        $pre->execute($values);
        $result = $pre->fetch();
        if($result && strlen($id) > 0) {
            include 'config.php';
            $keys = $config['api-keys'];
			$result = file_get_contents('https://api.themoviedb.org/3/movie/'. $id .'/external_ids?api_key='. $keys[tmdb]);
            return $response->withJson(json_decode($result),200);
        }else{
            return $response->withJson(array('status' => 'Not Found'),422);
        }
    }
    catch(\Exception $ex){
        return $response->withJson(array('error' => $ex->getMessage()),422);
    }
});
$app->run();