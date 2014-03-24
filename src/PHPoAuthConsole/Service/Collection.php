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
 * @package    Service
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class Collection implements \Iterator
{
    /**
     * @var \PHPoAuthConsole\Service\ScopeFetcher
     */
    private $scopeFetcher;

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
        $this->scopeFetcher = new ScopeFetcher();

        $uriFactory = new UriFactory();

        $this->uri = $uriFactory->createFromSuperGlobalArray($_SERVER);
        $this->uri->setQuery('');

        $this->storage = new Session();

        $this->serviceFactory = new ServiceFactory();
    }

    /**
     * Adds a service to the collection
     *
     * @param string $name              The name of the service
     * @param string $key               The API key
     * @param string $secret            The API secret
     * @param string $serviceWithScopes Name of the service class if it has scopes (oauth2 services)
     *
     * @return \PHPoAuthConsole\Service\Collection The collection
     */
    public function add($name, $key, $secret, $serviceWithScopes = null)
    {
        if ($serviceWithScopes === null) {
            $this->services[$this->normalizeName($name)] = $this->serviceFactory->createService(
                $name,
                new Credentials($key, $secret, $this->uri->getAbsoluteUri()),
                $this->storage
            );
        } else {
            $this->services[$this->normalizeName($name)] = $this->serviceFactory->createService(
                $name,
                new Credentials($key, $secret, $this->uri->getAbsoluteUri()),
                $this->storage,
                $this->scopeFetcher->fetch($serviceWithScopes)
            );
        }

        return $this;
    }

    /**
     * Makes an API request to a service
     *
     * @param string $name   The name of the service
     * @param string $method The HTTP method
     * @param string $path   The path to the resource
     * @param array  $params The parameters to be send for the request
     *
     * @return string The result (/ response) of the request
     */
    public function request($name, $method, $path, array $params = null)
    {
        return $this->services[$this->normalizeName($name)]->request($path, $method, $params);
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

        if ($this->services[$name] instanceof ServiceInterfaceV1) {
            $token = $this->services[$name]->requestRequestToken();

            $authorizationUriData = [
                'oauth_token' => $token->getRequestToken(),
            ];

            if ($name === 'Flickr') {
                $authorizationUriData['perms'] = 'delete';
            }

            $url = $this->services[$name]->getAuthorizationUri($authorizationUriData);
        } elseif ($this->services[$name] instanceof ServiceInterfaceV2) {
            $url = $this->services[$name]->getAuthorizationUri();
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

    public function getAccessToken2($name, $token)
    {
        $name = $this->normalizeName($name);

        $token = $this->services[$name]->requestAccessToken($_GET['code']);
    }

    private function normalizeName($name)
    {
        return $name;

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
