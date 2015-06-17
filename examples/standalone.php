<?php
require '../vendor/autoload.php';

// This examples shows how to do this without using the class
// Still requires Guzzle

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;

if (class_exists('\Whoops\Run')) {
    $whoops = new \Whoops\Run;
    $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
    $whoops->register();
}

$cookieJar = new CookieJar();

$uri      = 'https://192.168.1.1:443';
$username = 'ubnt';
$password = 'ubnt';

// Create a client and provide a base URL
$client = new Client([
    'base_uri' => $uri,
    'headers' => [
        // Setting the Referer is a necessary annoyance
        'Referer' => $uri,
        'Accept' => 'application/json',
        'X-Requested-With' => 'XMLHttpRequest',
    ],
    // Ignore Invalid SSL Cert
    'verify' => false,
    // Save the cookies for authentication
    'cookies' => $cookieJar
]);

// Create a POST request and add the username & password
$response = $client->post('/', [
    'form_params' => [
        'username' => $username,
        'password' => $password
    ],
    // Guzzle 6.0.1 won't grab cookies when redirected
    // Page issues both cookies on the first POST request, but also sends a 302 Redirect
    // When Guzzle follows the redirect (via a GET request) it doesn't save the cookies from the first POST request
    'allow_redirects' => false
]);

// Assuming valid auth - you'll get a guzzle 403 exception if its not valid

// Request get.json which requires auth session
$response = $client->get('/api/edge/get.json');
$data     = $response->getBody();

$data = json_decode($data);

echo '<pre>';
echo json_encode($data, JSON_PRETTY_PRINT);
