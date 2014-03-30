<?php
/**
 * GitHub release and branch downloader
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
 * GitHub release and branch downloader
 *
 * @category   PHPoAuthConsole
 * @package    Repository
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 */
class Download
{
    /**
     * @var string The path of the repo on GitHub
     */
    const REPO_PATH = '/repos/Lusitanian/PHPoAuthLib';

    /**
     * @var string The master zipball
     */
    const MASTER_ZIPBALL = 'https://github.com/Lusitanian/PHPoAuthLib/archive/master.zip';

    /**
     * @var \PHPoAuthConsole\Repository\Client The GitHub HTTP client
     */
    private $client;

    /**
     * @var string The location in which to store the versions
     */
    private $location;

    /**
     * Creates instance
     *
     * @param \PHPoAuthConsole\Repository\Client $client   The GitHub HTTP client
     * @param string                             $location The location in which to store the versions
     */
    public function __construct(Client $client, $location)
    {
        $this->client   = $client;
        $this->location = $location;
    }

    /**
     * Downloads all the latest versions of releases and branches of the repository
     */
    public function updateVersions()
    {
        $this->getReleases();
    }

    /**
     * Downloads latest master of the repository
     */
    public function updateMaster()
    {
        $this->getRelease('master', self::MASTER_ZIPBALL);
    }

    /**
     * Downloads custom versions of the lib
     *
     * Custom versions are simply dev/master which can be used to add custom code to to test and debug issues
     * CUstom versions always follow the convention of c#issue
     *
     * @param array $versions List of custom versions
     */
    public function updateCustom(array $versions = [])
    {
        if (empty($versions)) {
            return;
        }

        foreach ($versions as $version) {
            $this->getRelease($version, self::MASTER_ZIPBALL);
        }
    }

    /**
     * Gets all releases from the GitHub repo and extracts them in the correct directory
     */
    private function getReleases()
    {
        $releases = json_decode($this->client->get(self::REPO_PATH . '/tags'), true);

        foreach ($releases as $release) {
            if (is_dir($this->location . '/releases/' . $release['name'])) {
                continue;
            }

            $this->getRelease($release['name'], $release['zipball_url']);
        }
    }

    /**
     * Gets the zipball of a single release
     *
     * @param string $name       The name of the release
     * @param string $zipballUri The URI of the zipball
     */
    private function getRelease($name, $zipballUri)
    {
        $tempDirectory = sys_get_temp_dir() . '/' . uniqid();

        mkdir($tempDirectory);

        file_put_contents(
            $tempDirectory . '/archive.zip',
            $this->client->get($zipballUri)
        );

        $this->extractRelease($tempDirectory);
        $this->fixDirectoryStructure($tempDirectory, $this->location . '/releases/' . $name);
    }

    /**
     * Extracts (unzips) and deltes a release's zipball
     *
     * @param string $zipLocation The location of the ZIP
     *
     * @throws \Exception When the zip could not be opened
     */
    private function extractRelease($zipLocation)
    {
        $zip = new \ZipArchive();

        if ($zip->open($zipLocation . '/archive.zip') !== true) {
            throw new \Exception('Could not open zip file (`' . $zipLocation . '`).');
        }

        $zip->extractTo($zipLocation);
        $zip->close();
    }

    /**
     * PHP's ZipArchive class is having a hard time simply extracting a directory zo we rae going to manually fix
     * the directory structure here.
     *
     * Disclaimer: I might have simply missed something stupid so feel free to PR a better solution and call me an idiot
     *
     * @param string $tempDirectory   The temporary location of the release contents
     * @param string $releaseLocation The location of the release
     */
    private function fixDirectoryStructure($tempDirectory, $releaseLocation)
    {
        $dirContents = scandir($tempDirectory);

        foreach ($dirContents as $directory) {
            if ($directory === 'PHPoAuthLib-master') {
                // skip custom releases to prevent overwriting custom code
                if (preg_match('#/releases/c-[\d]+$#', $releaseLocation) === 1 && is_dir($releaseLocation)) {
                    continue;
                }

                if (preg_match('#/releases/c-[\d]+$#', $releaseLocation) !== 1) {
                    $this->deleteDirectory($releaseLocation);
                }
            }

            if (strpos($directory, 'Lusitanian-PHPoAuthLib-') === 0 || $directory === 'PHPoAuthLib-master') {
                rename($tempDirectory . '/' . $directory, $releaseLocation);

                break;
            }
        }
    }

    /**
     * Recursively deletes a directory
     *
     * @param string $directory The directory to remove
     */
    private function deleteDirectory($directory)
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            if ($fileinfo->isDir()){
                rmdir($fileinfo->getRealPath());
            } else {
                unlink($fileinfo->getRealPath());
            }
        }

        rmdir($directory);
    }
}
