<?php
/**
 * Bootstraps the PHPoAuthConsole application
 *
 * PHP version 5.4
 *
 * @category   PHPoAuthConsole
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2014 Pieter Hordijk
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version    1.0.0
 */
namespace PHPoAuthConsole;

use PHPoAuthConsole\Psr0\Autoloader;
use PHPoAuthConsole\Network\Http\Request;
use PHPoAuthConsole\Storage\ImmutableArray;
use PHPoAuthConsole\Service\Collection;
use PHPoAuthConsole\Presentation\Dump;

/**
 * Setup the environment
 */
require_once __DIR__ . '/init.deployment.php';

/**
 * Bootstrap the library
 */
require_once __DIR__ . '/src/PHPoAuthConsole/Psr0/Autoloader.php';

/**
 * Setup autoloading
 */
$autoloader = new Autoloader(__NAMESPACE__, __DIR__ . '/src');
$autoloader->register();

/**
 * We don't need further bootstrapping for CLI scripts
 */
if (php_sapi_name() === 'cli') {
    return;
}

/**
 * Setup the request
 */
$request = new Request(
    new ImmutableArray(explode('/', trim(preg_replace('/\?.*/', '', $_SERVER['REQUEST_URI']), '/'))),
    new ImmutableArray($_GET),
    new ImmutableArray($_POST),
    new ImmutableArray($_SERVER),
    new ImmutableArray($_FILES),
    new ImmutableArray($_COOKIE)
);

/**
 * Setup pretty dump object
 */
$dump = new Dump();

/**
 * Gets the type of the response
 */
function getType($rawResponse)
{
    if (json_decode($rawResponse) !== null) {
        return 'json';
    }

    $internalErrors = libxml_use_internal_errors(true);
    $xml = simplexml_load_string($rawResponse);
    libxml_use_internal_errors($internalErrors);

    if ($xml !== false) {
        return 'xml';
    }

    return null;
}

/**
 * Get all versions supported
 */
$versions = scandir(__DIR__ . '/versions/releases');

foreach ($versions as $index => $version) {
    if (strpos($version, 'v') !== 0 && $version !== 'master') {
        unset($versions[$index]);
    }
}

arsort($versions);

/**
 * Version in URI path matcher pattern
 */
$versionPattern = '(?:v\d+\.\d+\.\d+)|(?:master)';

/**
 * Get the current targeted version
 */
$version = null;

preg_match('#^(' . $versionPattern . ')$#', $request->path(0), $matches);

if (isset($matches[1])) {
    $version = $request->path(0);
} elseif (isset($_COOKIE['version']) && preg_match('#^(' . $versionPattern . ')$#', $_COOKIE['version']) === 1) {
    $version = $_COOKIE['version'];
}

if ($version !== null) {
    // bootstrap the correct oauth lib version
    require_once __DIR__ . '/versions/releases/' . $version . '/src/OAuth/bootstrap.php';

    /**
     * Initialize the oauth services
     */
    $services = new Collection();

    $services->add('Twitter', $credentials['twitter']['key'], $credentials['twitter']['secret'])
        ->add('BitBucket', $credentials['bitbucket']['key'], $credentials['bitbucket']['secret'])
        ->add('Etsy', $credentials['etsy']['key'], $credentials['etsy']['secret'])
        ->add('FitBit', $credentials['fitbit']['key'], $credentials['fitbit']['secret'])
        ->add('Flickr', $credentials['flickr']['key'], $credentials['flickr']['secret'])
        ->add('Tumblr', $credentials['tumblr']['key'], $credentials['tumblr']['secret'])
        ->add('Xing', $credentials['xing']['key'], $credentials['xing']['secret'])
        ->add('Amazon', $credentials['amazon']['key'], $credentials['amazon']['secret'], '\\OAuth\\OAuth2\\Service\\Amazon')
        ->add('Bitly', $credentials['bitly']['key'], $credentials['bitly']['secret'], '\\OAuth\\OAuth2\\Service\\Bitly')
        ->add('Box', $credentials['box']['key'], $credentials['box']['secret'], '\\OAuth\\OAuth2\\Service\\Box')
        ->add('Dailymotion', $credentials['dailymotion']['key'], $credentials['dailymotion']['secret'], '\\OAuth\\OAuth2\\Service\\Dailymotion')
        ->add('Dropbox', $credentials['dropbox']['key'], $credentials['dropbox']['secret'], '\\OAuth\\OAuth2\\Service\\Dropbox')
        ->add('Facebook', $credentials['facebook']['key'], $credentials['facebook']['secret'], '\\OAuth\\OAuth2\\Service\\Facebook')
        ;
}

setcookie('version', $version, time()+60*60*24*30, '/', $request->server('SERVER_NAME'), $request->isSecure(), true);

/**
 * Setup routing and content templates
 */
ob_start();

if (preg_match('#^(' . $versionPattern . ')$#', $request->path(0)) !== 1) {

    if ($version === null) {
        $versions = scandir(__DIR__ . '/versions/releases');

        $version = array_pop($versions);

        setcookie('version', $version, time()+60*60*24*30, '/', $request->server('SERVER_NAME'), $request->isSecure(), true);
    }

    header('Location: ' . $request->getBaseUrl() . '/' . $version);
    exit;
} elseif (preg_match('#^/' . $versionPattern . '$#', $request->getPath()) === 1) {
    require __DIR__ . '/templates/overview.phtml';
// oauth1 return
} elseif (preg_match('#^/' . $versionPattern . '/(.*)/authorize$#', $request->getPath()) === 1 && $request->get('oauth_token') !== null) {
    $services->getAccessToken(
        $request->path(1),
        $request->get('oauth_token'),
        $request->get('oauth_verifier')
    );

    header('Location: ' . $request->getBaseUrl() . '/' . $request->path(0));
    exit;
// oauth2 return
} elseif (preg_match('#^/' . $versionPattern . '/(.*)/authorize$#', $request->getPath()) === 1 && $request->get('code') !== null) {
    $services->getAccessToken2(
        $request->path(1),
        $request->get('code')
    );

    header('Location: ' . $request->getBaseUrl() . '/' . $request->path(0));
    exit;
} elseif (preg_match('#^/' . $versionPattern . '/(.*)/authorize$#', $request->getPath(), $matches) === 1) {
    $services->authorize($request->path(1));
} elseif (preg_match('#^/' . $versionPattern . '/[^/]+$#', $request->getPath(), $matches) === 1 && $request->getMethod() === 'GET') {
    require __DIR__ . '/templates/console.phtml';
} elseif (preg_match('#^/' . $versionPattern . '/[^/]+$#', $request->getPath(), $matches) === 1 && $request->getMethod() === 'POST') {
    if (!$services->isAuthenticated($request->path(1))) {
        header('Location: ' . $request->getBaseUrl() . '/' . $request->path(0) . '/' . $request->path(1) . '/authorize/');
        exit;
    }

    $apiCall = [
        'uri'    => $request->post('url'),
        'method' => $request->post('method'),
    ];

    $rawResponse = $services->request($request->path(1), $request->post('method'), $request->post('url'));

    $result = [
        'data' => $rawResponse,
        'type' => getType($rawResponse),
    ];

    require __DIR__ . '/templates/console.phtml';
} else {
    require __DIR__ . '/templates/not-found.phtml';
}

$content = ob_get_clean();
ob_end_clean();

require __DIR__ . '/templates/page.phtml';
