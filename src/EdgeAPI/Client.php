<?php
namespace PrimeInc\EdgeApi;

use \GuzzleHttp\Cookie\CookieJar;
use \GuzzleHttp\Psr7\Request;

/**
 * Super-simple, minimum abstraction EdgeOS API wrapper
 *
 * Requires guzzle 6.x
 *
 * @author  William James <will@prime.ms>
 * @version 0.1.0
 */
class Client
{

    protected $endpoint;
    protected $client;
    protected $cookieJar;
    protected $data;

    /**
     * Create a new instance
     *
     * @param Endpoint $endpoint The endpoint object of the device to connect to.
     */
    public function __construct(Endpoint $endpoint)
    {
        $this->endpoint = $endpoint;

        // Load or create the cookieJar
        if (is_a($this->endpoint->getCookies(), 'GuzzleHttp\Cookie\CookieJar')) {
            $this->cookieJar = $this->endpoint->getCookies();
        } else {
            $this->cookieJar = new CookieJar();
        }

        // Create a client and provide a base URL
        $this->client = new \GuzzleHttp\Client([
            'base_uri' => $this->endpoint->getProtocol() . '://' . $this->endpoint->getDomain() . ':' . $this->endpoint->getPort(),
            'headers' => [
                // Setting the Referer is a necessary
                'Referer' => $this->endpoint->getProtocol() . '://' . $this->endpoint->getDomain() . ':' . $this->endpoint->getPort(),
                'Accept' => 'application/json',
                'X-Requested-With' => 'XMLHttpRequest',
            ],
            // SSL Cert check
            'verify' => $this->endpoint->getVerify(),
            // Save the cookies for authentication
            'cookies' => $this->cookieJar
        ]);

    }

    /**
     * gets a pre-defined config structure
     *
     * @return string
     */
    public function get()
    {
        $request = new Request('GET', '/api/edge/get.json');

        return $this->getResponse($request);
    }

    /**
     * gets a partial set of the config structure
     *
     * @return string
     */
    public function partial()
    {
        $request = new Request('GET', '/api/edge/partial.json');

        return $this->getResponse($request);
    }

    /**
     * gets a single set of non-config data
     *
     * $query can be any of the following: dhcp_leases, dhcp_stats, routes, sys_info
     *
     * @param $query
     *
     * @return string
     */
    public function data($query)
    {
        $options = ['query' => ['data' => $query]];
        $request = new Request('GET', '/api/edge/data.json');

        return $this->getResponse($request, $options);
    }

    /**
     * verifies connectivity and user admin authentication
     *
     * @throws \Exception
     */
    public function auth()
    {
        // Form params are no longer set on Request object, but instead on Client->send();
        $options = [
            'form_params' => [
                'username' => $this->endpoint->getUsername(),
                'password' => $this->endpoint->getPassword()
            ],
            // Guzzle 6.0.1 won't grab cookies when redirected
            // Page issues both cookies on the first POST request, but also sends a 302 Redirect
            // When Guzzle follows the redirect (via a GET request) it doesn't save the cookies from the first POST request
            'allow_redirects' => false
        ];

        // Create a POST request and add the username & password
        $request = new Request('POST', '/api/edge/auth.json');

        $response = $this->getResponse($request, $options, false);
        $response = json_decode($response);

        if (!$response->success) {
            throw new \Exception("Failed to login to device");
        }
        if ($response->level != 'admin') {
            throw new \Exception("Account must be an admin to use the API");
        }

        // Send post request again to the login page to get the CSRF Token & an authenticated session_id (the auth.json api doesn't do this =/)
        $request = new Request('POST', '/');
        $this->getResponse($request, $options, false);

        $this->endpoint->setCookies($this->cookieJar);

    }

    /**
     * makes sure the backend daemon is alive
     *
     * does not create a php session
     *
     * @return string
     */
    public function ping()
    {
        $options = ['query' => ['anon' => '1']];
        $request = new Request('GET', '/api/edge/ping.json');

        return $this->getResponse($request, $options, false);
    }

    /**
     * returns or forces a download of the tar file of the current config system
     *
     * @param bool $return
     *
     * @return string|void
     */
    public function save($return = true)
    {
        $request = new Request('GET', '/api/edge/config/save.json');

        $this->getResponse($request);

        // TODO: add error checking..

        $request = new Request('GET', '/files/config');

        $tar = $this->getResponse($request);

        if ($return) {
            return $tar;
        } else {
            if (function_exists('mb_strlen')) {
                $size = mb_strlen($tar, '8bit');
            } else {
                $size = strlen($tar);
            }

            $date = new \DateTime('now');

            header("Content-Type: application/x-tar");
            header("Content-Disposition: attachment; filename='" . $this->endpoint->getName() . '_' . $date->format('Ymd') . ".tar.gz'");
            header("Content-Length: $size");
            header("Content-Transfer-Encoding: binary");

            echo $tar;
        }
    }

    /**
     * makes sure the backend daemon is alive and the session is active
     *
     * @return bool
     */
    public function heartbeat()
    {
        $request = new Request('GET', '/api/edge/heartbeat.json');

        $response = json_decode($this->getResponse($request, [], false));

        if ($response->success && $response->PING && $response->SESSION) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param       $request
     *
     * @param bool  $check_auth
     *
     * @param array $options
     *
     * @return string
     */
    private function getResponse($request, $options = [], $check_auth = true)
    {
        if ($check_auth) {
            $this->checkAuth();
        }

        $response   = $this->client->send($request, $options);
        $this->data = $response->getBody();

        return $this->data;
    }

    /**
     * Checks auth state for requests that require previous authentication
     *
     * @return bool
     * @throws \Exception
     */
    private function checkAuth()
    {
        if (!$this->heartbeat()) {
            $this->auth();
        }
        if ($this->heartbeat()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Takes a string of json and makes it pretty
     *
     * @param string $data
     *
     * @return string
     * @throws \Exception
     */
    public function prettyJson($data = '')
    {
        if (empty($data) && !empty($this->data)) {
            $json = json_decode($this->data);

            return json_encode($json, JSON_PRETTY_PRINT);
        } elseif (!empty($data)) {
            $json = json_decode($data);

            return json_encode($json, JSON_PRETTY_PRINT);
        } else {
            throw new \Exception("No json data to print");
        }
    }


}
