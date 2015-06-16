<?php
require '../vendor/autoload.php';

// This examples shows how to do this without using the class
// Still requires Guzzle

use Guzzle\Http\Client;
use Guzzle\Plugin\Cookie\CookiePlugin;
use Guzzle\Plugin\Cookie\CookieJar\ArrayCookieJar;

$cookiePlugin = new CookiePlugin(new ArrayCookieJar());

$uri      = 'https://10.0.1.1:443';
$username = '';
$password = '';

// Create a client and provide a base URL
$client = new Client($uri);
// Ignore Invalid SSL Cert
$client->setDefaultOption('verify', false);
// Setting the Referer is a necessary annoyance
$client->setDefaultOption('headers/Referer', $uri);
// These are optional
$client->setDefaultOption('headers/Accept', 'application/json');
$client->setDefaultOption('headers/X-Requested-With', 'XMLHttpRequest');
// Add the cookie plugin to a client
$client->addSubscriber($cookiePlugin);

// Create a POST request and add the username & password
$request  = $client->post('/', array(), array(
    'username' => $username,
    'password' => $password
));
$response = $request->send();

// Assuming valid auth - you'll get a guzzle 403 exception if its not valid

// Request get.json which requires auth session
$request  = $client->get('/api/edge/get.json');
$response = $request->send();

$data = $response->json();

echo json_encode($data);
