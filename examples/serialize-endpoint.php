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

// get auth cookies (only required because I'm not making any other api calls)
$client->auth();

// serialize the endpoint, so you can save it for another request
// this way you don't have to login multiple times.
$serializedEndpoint = serialize($endpoint);

// doing this is here is just to show that when you unserialize it, it becomes and Endpoint object
$endpoint2 = unserialize($serializedEndpoint);

$client2 = new Client($endpoint2);

$client2->data('dhcp_leases');
echo '<pre>';
echo $client2->prettyJson();
echo '</pre>';
