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

// get auth cookies (only required because I'm not making any other api calls)
$client->auth();

// serialize the endpoint, so you can save it for another request
// this way you don't have to login multiple times.
$serializedEndpoint = serialize($endpoint);

// this is to show that when you unserialize it, it becomes an Endpoint object
$endpoint2 = unserialize($serializedEndpoint);

$client2 = new Client($endpoint2);

$client2->data('dhcp_leases');

echo '<pre>';
echo $client2->prettyJson();
