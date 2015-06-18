<?php
/**
 * Date: 6/14/2015
 * Time: 8:26 PM
 */

namespace PrimeInc\EdgeApi;

use GuzzleHttp\Cookie\CookieJar;

/**
 * Set-Endpoint object
 *
 * @author  William James <will@prime.ms>
 * @version 0.1.0
 */
class Endpoint
{

    /** @var array Endpoint data */
    protected $data;

    /**
     * @param array $data Array of cookie data provided by a Cookie parser
     */
    public function __construct(array $data = array())
    {
        static $defaults = array(
            'name' => '',
            'protocol' => 'https',
            'domain' => '',
            'port' => '443',
            'username' => '',
            'password' => '',
            'verify' => true,
            'cookies' => null
        );
        $this->data = array_merge($defaults, $data);
    }

    /**
     * Get the Endpoint as an array, note that the cookies need to be serialize independently
     *
     * @return array
     */
    public function toArray()
    {
        return $this->data;
    }

    /**
     * Serialize the cookies before serializing the data array
     *
     * @return array
     */
    public function __sleep()
    {
        $cookieJar = $this->getCookies();

        $this->setCookies($this->serializeCookies($cookieJar));

        return array('data');
    }

    /**
     * Restore the cookieJar object
     *
     */
    public function __wakeup()
    {
        $cookieJar = $this->getCookies();

        $this->setCookies($this->unserializeCookies($cookieJar));
    }

    /**
     * Get the CookieJar
     *
     * @return CookieJar|null
     */
    public function getCookies()
    {
        return $this->data['cookies'];
    }

    /**
     * Set the CookieJar
     *
     * @param CookieJar|string $cookies
     *
     * @return Endpoint
     */
    public function setCookies($cookies)
    {
        return $this->setData('cookies', $cookies);
    }

    /**
     * @param CookieJar $cookieJar
     *
     * @return string
     */
    public function serializeCookies(CookieJar $cookieJar)
    {
        return serialize($cookieJar->toArray());
    }

    /**
     * @param $data
     *
     * @return CookieJar
     */
    public function unserializeCookies($data)
    {
        $cookieJar = new CookieJar();
        $cookieJar->fromArray(unserialize($data), $this->getDomain());

        return $cookieJar;
    }

    /**
     * Get the endpoint name
     *
     * @return string
     */
    public function getName()
    {
        return $this->data['name'];
    }

    /**
     * Set the endpoint name
     *
     * @param string $name Endpoint name
     *
     * @return Endpoint
     */
    public function setName($name)
    {
        return $this->setData('name', $name);
    }

    /**
     * Get the endpoint protocol
     *
     * @return string
     */
    public function getProtocol()
    {
        return $this->data['protocol'];
    }

    /**
     * Set the endpoint protocol
     *
     * @param string $protocol Endpoint protocol
     *
     * @return Endpoint
     */
    public function setProtocol($protocol)
    {
        return $this->setData('protocol', $protocol);
    }

    /**
     * Get the domain
     *
     * @return string|null
     */
    public function getDomain()
    {
        return $this->data['domain'];
    }

    /**
     * Set the domain of the endpoint
     *
     * @param string $domain
     *
     * @return Endpoint
     */
    public function setDomain($domain)
    {
        return $this->setData('domain', $domain);
    }

    /**
     * Get the port of the endpoint
     *
     * @return int
     */
    public function getPort()
    {
        return $this->data['port'];
    }

    /**
     * Set the port of the endpoint
     *
     * @param int $port
     *
     * @return Endpoint
     */
    public function setPort($port)
    {
        return $this->setData('port', $port);
    }

    /**
     * Get the username of the endpoint
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->data['username'];
    }

    /**
     * Set the username of the endpoint
     *
     * @param string $username
     *
     * @return Endpoint
     */
    public function setUsername($username)
    {
        return $this->setData('username', $username);
    }

    /**
     * Get the password of the endpoint
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->data['password'];
    }

    /**
     * Set the password of the endpoint
     *
     * @param string $password
     *
     * @return Endpoint
     */
    public function setPassword($password)
    {
        return $this->setData('password', $password);
    }

    /**
     * Get SSL certificate validation enforcement
     *
     * @return bool
     */
    public function getVerify()
    {
        return $this->data['verify'];
    }

    /**
     * Set SSL certificate validation enforcement
     *
     * TRUE will enforce validation of ssl certs
     *
     * @param bool $verify
     *
     * @return Endpoint
     */
    public function setVerify($verify)
    {
        return $this->setData('verify', $verify);
    }

    /**
     * Set a value and return the endpoint object
     *
     * @param string $key   Key to set
     * @param string $value Value to set
     *
     * @return Endpoint
     */
    private function setData($key, $value)
    {
        $this->data[$key] = $value;

        return $this;
    }

}
