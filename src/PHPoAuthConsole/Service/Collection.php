<?php
/**
 * oAuth service collection
 *
 * PHP version 5.4
 *
 * @category   PHPoAuthConsole
 * @package    Service
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2014 Pieter Hordijk
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version    1.0.0
 */
namespace PHPoAuthConsole\Service;

use OAuth\Common\Http\Uri\UriFactory;
use OAuth\Common\Storage\Session;
use OAuth\ServiceFactory;
use OAuth\Common\Consumer\Credentials;
use OAuth\OAuth1\Service\ServiceInterface as ServiceInterfaceV1;
use OAuth\OAuth2\Service\ServiceInterface as ServiceInterfaceV2;

/**
 * oAuth service collection
 *
 * @category   PHPoAuthConsole
 * @package    Psr0
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class Collection implements \Iterator
{
    /**
     * @var \OAuth\Common\Http\Uri\Uri Oauth URI
     */
    private $uri;

    /**
     * @var \OAuth\Common\Storage\TokenStorageInterface Instance of the token storage
     */
    private $storage;

    /**
     * @var OAuth\ServiceFactory The oauth service factory
     */
    private $serviceFactory;

    /**
     * @var array List of the registered services
     */
    private $services = [];

    /**
     * Creates instance
     */
    public function __construct()
    {
        $uriFactory = new UriFactory();

        $this->uri = $uriFactory->createFromSuperGlobalArray($_SERVER);
        $this->uri->setQuery('');

        $this->storage = new Session();

        $this->serviceFactory = new ServiceFactory();
    }

    /**
     * Adds a service to the collection
     *
     * @param string $name   The name of the service
     * @param string $key    The API key
     * @param string $secret The API secret
     *
     * @return \PHPoAuthConsole\Service\Collection The collection
     */
    public function add($name, $key, $secret)
    {
        $this->services[$this->normalizeName($name)] = $this->serviceFactory->createService(
            $name,
            new Credentials($key, $secret, $this->uri->getAbsoluteUri()),
            $this->storage
        );

        return $this;
    }

    public function request($path, array $params = [])
    {
        $parts = [];
        foreach ($path as $item) {
            $parts[] = $item;
        }

        $name   = $this->normalizeName(array_shift($parts));
        $method = array_pop($parts);

        $parts = array_map('strtolower', $parts);
        $parts = array_map('ucfirst', $parts);

        $abstractedServiceName = '\\PHPoAuthImpl\\Service\\' . $name . '\\' . implode('\\', $parts);

        $service = $this->factory->build($abstractedServiceName);

        //$service = new $abstractedServiceName($this->services[$name]);

        return $service->$method();
    }

    /**
     * Checks whether the user is already authenticated at the service
     *
     * @param string $name The name of the service
     *
     * @return boolean True when the user is authenticated
     */
    public function isAuthenticated($name)
    {
        return $this->storage->hasAccessToken($this->normalizeName($name));
    }

    /**
     * Authorizes for a service
     *
     * @param string $name The name of the service
     */
    public function authorize($name)
    {
        $name = $this->normalizeName($name);
var_dump([
    $this->services[$name] instanceof ServiceInterfaceV1,
    $this->services[$name]->requestRequestToken(),
]);
        if ($this->services[$name] instanceof ServiceInterfaceV1) {
            $token = $this->services[$name]->requestRequestToken();

            $url = $this->services[$name]->getAuthorizationUri(array(
                'oauth_token' => $token->getRequestToken(),
            ));
        }

        header('Location: ' . $url);
        exit;
    }

    public function getAccessToken($name, $token, $verifier)
    {
        $name = $this->normalizeName($name);

        $token = $this->storage->retrieveAccessToken($name);

        $this->services[$name]->requestAccessToken(
            $token,
            $verifier,
            $token->getRequestTokenSecret()
        );
    }

    private function normalizeName($name)
    {
        return ucfirst(strtolower($name));
    }

    /**
     * Rewinds the collection
     */
    public function rewind()
    {
        reset($this->services);
    }

    /**
     * Gets the current item from the collection
     *
     * @return \OAuth\Common\Service\ServiceInterface Instance of an oauth service
     */
    public function current()
    {
        return current($this->services);
    }

    /**
     * Gets the current key of the collection
     *
     * @return string The current key
     */
    public function key()
    {
        return key($this->services);
    }

    /**
     * Advances the iterator pointer
     */
    public function next()
    {
        next($this->services);
    }

    /**
     * Check whether the current key is valid
     *
     * @return boolean True when the key is valid
     */
    public function valid()
    {
        return key($this->services) !== null;
    }
}
