<?php
/**
 * Fetches all scopes of services
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

/**
 * Fetches all scopes of services
 *
 * @category   PHPoAuthConsole
 * @package    Service
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class ScopeFetcher
{
    /**
     * Fetches all scopes of the service
     *
     * @param string $serviceName The service
     *
     * @return array The scopes of the service
     */
    public function fetch($serviceName)
    {
        if (!class_exists($serviceName)) {
            return [];
        }

        $reflection = new \ReflectionClass($serviceName);

        return $this->getScopes($reflection->getConstants());
    }

    /**
     * Filters the constants to get the scopes
     *
     * @param array $constants List of all the class constants
     *
     * @return array List of all the scopes of the service
     */
    private function getScopes(array $constants)
    {
        $scopes = [];

        foreach ($constants as $key => $value) {
            if (strpos($key, 'SCOPE_') !== 0) {
                continue;
            }

            // @todo: properly handle facebook's namespaced scopes
            if (substr($value, -strlen(':APP_NAMESPACE')) === ':APP_NAMESPACE') {
                continue;
            }

            $scopes[] = $value;
        }

        return $scopes;
    }
}
