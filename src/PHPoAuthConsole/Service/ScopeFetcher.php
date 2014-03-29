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

            // @todo: think of a way to handle too many google scopes (splitting up into separate services?)
            if (in_array($key, [
                'SCOPE_ADSENSE', 'SCOPE_ADWORDS', 'SCOPE_GAN', 'SCOPE_PICASAWEB', 'SCOPE_CLOUDSTORAGE', 'SCOPE_ANALYTICS_READ_ONLY',
                'SCOPE_USER_PROVISIONING', 'SCOPE_GROUPS_PROVISIONING', 'SCOPE_ORKUT', 'SCOPE_YOUTUBE_ANALYTICS_MONETARY',
                'SCOPE_ANDROID_PUBLISHER', 'SCOPE_GPLUS_LOGIN', 'SCOPE_DRIVE_APPS_READ_ONLY', 'SCOPE_DRIVE_METADATA_READ_ONLY',
                'SCOPE_DRIVE_READ_ONLY', 'SCOPE_CHROMEWEBSTORE', 'SCOPE_CONTENTFORSHOPPING', 'SCOPE_NICKNAME_PROVISIONING',
                'SCOPE_YOUTUBE_PARTNER_EDIT', // deprecated?? removed??? never existed??? @todo check this scope
                'SCOPE_DRIVE_APPS', 'SCOPE_GOOGLEDRIVE', 'SCOPE_GOOGLEDRIVE_FILES', 'SCOPE_DRIVE_SCRIPTS', // disabling more scopes... either google doesn't like drive or something else is borked on google's end...
            ], true)) {
                continue;
            }

            $scopes[] = $value;
        }

        return $scopes;
    }
}
