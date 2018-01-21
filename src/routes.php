<?php

use Slim\Http\Request;
use Slim\Http\Response;

use Predis\Client as RedisClient;

// Routes

const REDIS_KEY = 'drfts://tz';
const PAGE_SIZE = 50;

$app->get('/', function (Request $request, Response $response, array $args) {
    // Render index view
    return $this->renderer->render($response, 'index.phtml', $args);
});

$app->get('/read', function(Request $request, Response $response, array $args) {
    //
    $page_id = $args['page_id'];
    
    if(!$page_id)
        $page_id = 0;
    
    $redis = new RedisClient(['encoding' => 'utf8']);
    
    $vals = $redis->lrange(REDIS_KEY, $page_id*PAGE_SIZE, ($page_id+1)*PAGE_SIZE);
    $rets = [];
    foreach($vals as $v) {
        array_push($rets, json_decode($v));
        
    }
    
    return json_encode($rets);
});

$app->post('/write', function(Request $request, Response $response, array $args) {
    
    $author = $args['author']; 
    $message = $args['message'];
    $timestamp = time();
    
    $redis = new RedisClient(['encoding' => 'utf8']);
    $redis->rpush(REDIS_KEY, json_encode([
        'author' => $author,
        'message'=> $message,
        'timestamp' => $timestamp
    ]));
});
