<?php
/**
 * CLI script to update versions of the PHPoAuthLib
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
$download->updateMaster();
$download->updateCustom([
    'c-214',
    'c-216',
    'c-224',
]);
