<?php

//Define a constant that indicates unit testing
if(!defined('BUILDR_RUNNING_TESTS')) {
    define('BUILDR_RUNNING_TESTS', 1);
}

error_reporting(E_ALL);
ini_set('display_errors', 'On');

$basePath = realpath(dirname(__DIR__));

//System-safe absolute path generation
$startupLocation = [
    $basePath,
    'src',
    'Startup',
    'BuildrStartup.php'
];
$startupLocation = implode(DIRECTORY_SEPARATOR, $startupLocation);
$startupLocation = realpath($startupLocation);

//Load startup class
require_once $startupLocation;

/**
 * Startup Initialization block
 */

//Initialize the autoloader
\buildr\Startup\BuildrStartup::setBasePath($basePath);
\buildr\Startup\BuildrStartup::initializeAutoloading(TRUE);

//Run the initializer
$startup = new \buildr\Startup\BuildrStartup();
$startup->setInitializer(new \buildr\Startup\Initializer\UnitTestingInitializer());


