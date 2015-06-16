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
    'verify' => false
));
$client   = new Client($endpoint);

// gets a pre-defined config structure (looks like at least a vast majority of the device config)
$client->get();
echo '<pre>';
echo $client->prettyJson();
echo '</pre>';
