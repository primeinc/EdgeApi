# EdgeAPI
A PHP wrapper for the EdgeOS API

### Installation
1. Use [Composer](http://getcomposer.org) to install EdgeAPI into your project:

    ```bash
    composer require primeinc/edgeapi
    ```

1. Setup the endpoint and guzzle client

    ```php
    //create new endpoint config, with required info
    $endpoint = new \PrimeInc\EdgeApi\Endpoint(array(
        'protocol' => 'https',
        'domain' => '192.168.1.1',
        'port' => '443',
        'username' => 'ubnt',
        'password' => 'ubnt',
        // don't have a valid cert? set the following to false
        'verify' => true
    ));
    $client = new \PrimeInc\EdgeApi\Client($endpoint);
    $client->data('sys_info');
    
    echo $client->prettyJson(); // this will grab the last data fetched automatically
    ```
    For more options, have a look at the **example files** in `examples/` to get a feel for how things work. There is also a standalone template if you don't wish to use this libray.

### Todo's
- Catch & Handle Errors
- Write Tests
- Ability to change endpoint configuration via POST requests
