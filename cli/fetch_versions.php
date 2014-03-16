<?php
/**
 * CLI script to update version of the PHPoAuthLib
 *
 * PHP version 5.4
 *
 * @category   PHPoAuthConsole
 * @package    Cli
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2014 Pieter Hordijk
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version    1.0.0
 */
namespace PHPoAuthConsole\Cli;

use PHPoAuthConsole\Repository\Client;
use PHPoAuthConsole\Repository\Download;

require_once __DIR__ . '/../bootstrap.php';

$download = new Download(new Client(), __DIR__ . '/../versions');
$download->updateVersions();

exit;

$versionsPath = realpath(__DIR__ . '/../versions');

$streamContext = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => 'User-Agent: PHPoAuthConsole (https://github.com/PeeHaa/PHPoAuthConsole)',
    ],
]);

$releases = json_decode(file_get_contents(
    'https://api.github.com/repos/Lusitanian/PHPoAuthLib/tags',
    false,
    $streamContext
), true);

foreach ($releases as $release) {
    if (is_dir($versionsPath . '/releases/' . $release['name'])) {
        continue;
    }

    mkdir($versionsPath . '/releases/' . $release['name']);

    file_put_contents(
        $versionsPath . '/releases/' . $release['name'] . '/archive.zip',
        file_get_contents($release['zipball_url'], false, $streamContext)
    );

    $zip = new \ZipArchive();
    if ($zip->open($versionsPath . '/releases/' . $release['name'] . '/archive.zip') === true) {

        $zip->extractTo($versionsPath . '/releases/' . $release['name']);
        $zip->close();

        $dirContents = scandir($versionsPath . '/releases/' . $release['name']);

        foreach ($dirContents as $directory) {
            if (strpos($directory, 'Lusitanian-PHPoAuthLib-') === 0) {
                rename(
                    $versionsPath . '/releases/' . $release['name'] . '/'. $directory . '/*',
                    $versionsPath . '/releases/' . $release['name'] . '/'
                );

                break;
            }
        }
    }

    break;
}
