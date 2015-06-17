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
    'name' => 'edgeRouter',
    'protocol' => 'https',
    'domain' => '192.168.1.1',
    'port' => '443',
    'username' => 'ubnt',
    'password' => 'ubnt',
    // don't have a valid cert? set this to false
    'verify' => true
));
$client   = new Client($endpoint);

//force downloads archive
$client->save(false);
