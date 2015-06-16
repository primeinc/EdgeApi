<?php
require '../vendor/autoload.php';

use PrimeInc\EdgeApi\Endpoint;
use PrimeInc\EdgeApi\Client;

if (class_exists('\Whoops\Run')) {
    $whoops = new \Whoops\Run;
    $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
    $whoops->register();
}

//create new endpoint config
$endpoint = new Endpoint(array(
    'name' => '',
    'protocol' => 'https',
    'domain' => '',
    'port' => '443',
    'username' => '',
    'password' => '',
    'verify' => true
));
$client   = new Client($endpoint);

//force downloads archive
$client->save(false);
