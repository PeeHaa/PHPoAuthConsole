<?php

/**
 * Setup error reporting
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 0);

/**
 * Setup the timezone
 */
ini_set('date.timezone', 'Europe/Amsterdam');

/**
 * Setup the services credentials
 */
require_once __DIR__ . '/credentials-example.php';

/**
 * Setup Analytics
 */
$analytics = [
    'code'   => null,
    'domain' => null,
];
