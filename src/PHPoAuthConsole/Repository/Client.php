<?php
/**
 * GitHub HTTP client
 *
 * PHP version 5.4
 *
 * @category   PHPoAuthConsole
 * @package    Repository
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2014 Pieter Hordijk
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version    1.0.0
 */
namespace PHPoAuthConsole\Repository;

/**
 * GitHub HTTP client
 *
 * @category   PHPoAuthConsole
 * @package    Repository
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class Client
{
    /**
     * @var string The GitHub API base URL
     */
    const BASE_URL = 'https://api.github.com';

    /**
     * @var string The User Agent (mandatory for github API requests)
     */
    const UA_STRING = 'PHPoAuthConsole (https://github.com/PeeHaa/PHPoAuthConsole)';

    /**
     * Makes a GET request to github
     *
     * @param string $path The path to the resource on github
     *
     * @return string The result of the request
     */
    public function get($path)
    {
        return $this->request('GET', $path);
    }

    /**
     * Makes a request to GitHub
     *
     * @param string $method The HTTP verb of the request
     * @param string $path   The path to the resource on github
     *
     * @return string The result of the request
     */
    public function request($method, $path)
    {
        $uri = $path;

        if (preg_match('#^https?://#', $path) !== 1) {
            $uri = self::BASE_URL . $path;
        } else {
            $uri = $path;
        }

        return file_get_contents($uri, false, $this->createContext($method));
    }

    /**
     * Create the stream context for fgc
     *
     * @param string $method The HTTP verb
     *
     * @return resource The stream context
     */
    private function createContext($method)
    {
        return stream_context_create([
            'http' => [
                'method' => $method,
                'header' => 'User-Agent: ' . self::UA_STRING,
            ],
        ]);
    }
}
