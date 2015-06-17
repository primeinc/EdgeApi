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

echo '<pre>';

$client->data('sys_info');
echo $client->prettyJson(); // this will grab the last data fetched automatically

$dhcp_stats = $client->data('dhcp_stats');
echo $client->prettyJson($dhcp_stats); // or you can feed it data

// gets a pre-defined config structure (looks like at least a vast majority of the device config)
$client->get(); //This one takes a while
echo $client->prettyJson();
