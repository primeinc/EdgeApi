<?php
namespace PrimeInc\EdgeApi;

use Guzzle\Plugin\Cookie\CookieJar\ArrayCookieJar;
use Guzzle\Plugin\Cookie\CookiePlugin;

/**
 * Super-simple, minimum abstraction EdgeOS API wrapper
 *
 * WIP - methods may change
 *
 * Requires guzzle3
 *
 * @author  William James <will@prime.ms>
 * @version 0.0.1
 */
class Client
{

    protected $endpoint;
    protected $client;
    protected $cookie;
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

        // Create or load the cookieJar
        if (is_a($this->endpoint->getCookies(), 'Guzzle\Plugin\Cookie\CookieJar\ArrayCookieJar')) {
            $this->cookieJar = $this->endpoint->getCookies();
            $this->cookie    = new CookiePlugin($this->cookieJar);
        } else {
            $this->cookieJar = new ArrayCookieJar();
            $this->cookie    = new CookiePlugin($this->cookieJar);
        }

        // Create a client and provide a base URL
        $this->client = new \Guzzle\Http\Client($this->endpoint->getProtocol() . '://' . $this->endpoint->getDomain() . ':' . $this->endpoint->getPort());
        // SSL Cert check
        $this->client->setDefaultOption('verify', $this->endpoint->getVerify());
        // Setting the Referer is a necessary annoyance
        $this->client->setDefaultOption('headers/Referer',
            $this->endpoint->getProtocol() . '://' . $this->endpoint->getDomain() . ':' . $this->endpoint->getPort());
        $this->client->setDefaultOption('headers/Accept', 'application/json');
        $this->client->setDefaultOption('headers/X-Requested-With', 'XMLHttpRequest');
        // Attach cookie object to client
        $this->client->addSubscriber($this->cookie);

    }

    /**
     * gets a pre-defined config structure
     *
     * @return string
     */
    public function get()
    {
        $request = $this->client->get('/api/edge/get.json');

        return $this->getResponse($request);
    }

    /**
     * gets a partial set of the config structure
     *
     * @return string
     */
    public function partial()
    {
        $request = $this->client->get('/api/edge/partial.json');

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
        $request = $this->client->get('/api/edge/data.json');
        $request->getQuery()->set('data', $query);

        return $this->getResponse($request);
    }

    /**
     * verifies connectivity and user admin authentication
     *
     * @throws \Exception
     */
    public function auth()
    {
        // Create a POST request and add the username & password
        $request = $this->client->post('/api/edge/auth.json', array(), array(
            'username' => $this->endpoint->getUsername(),
            'password' => $this->endpoint->getPassword()
        ));

        $response = $this->getResponse($request, false);
        $response = json_decode($response);

        if (!$response->success) {
            throw new \Exception("Failed to login to device");
        }
        if ($response->level != 'admin') {
            throw new \Exception("Account must be an admin to use the API");
        }

        // Send post request again to the login page to get the CSRF Token & an authenticated session_id (the auth.json api doesn't do this =/)
        $request = $this->client->post('/', array(), array(
            'username' => $this->endpoint->getUsername(),
            'password' => $this->endpoint->getPassword()
        ));
        $request->send();

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
        $request = $this->client->get('/api/edge/ping.json');
        $request->getQuery()->set('anon', '1');

        return $this->getResponse($request);
    }

    /**
     * returns or forces a download of the tar file of the current config system
     *
     * @param bool $return
     *
     * @return string
     */
    public function save($return = true)
    {
        $request = $this->client->get('/api/edge/config/save.json');

        $this->getResponse($request);

        //add error checking..

        $request = $this->client->get('/files/config');

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
        $request = $this->client->get('/api/edge/heartbeat.json');

        $response = json_decode($this->getResponse($request, false));

        if ($response->success && $response->PING && $response->SESSION) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param      $request
     *
     * @param bool $check_auth
     *
     * @return string
     * @throws \Exception
     */
    private function getResponse($request, $check_auth = true)
    {
        if ($check_auth) {
            if (!$this->heartbeat()) {
                //throw new \Exception("Not authenticated");
                $this->auth();
            }
        }
        $response   = $request->send();
        $this->data = $response->getBody();

        return $this->data;
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
