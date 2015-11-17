<?php

error_reporting(E_ALL);
ini_set('display_errors', TRUE);

//System-safe absolute path generation
$basePath = realpath(dirname(__DIR__));

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

//Set the base path and initialize the auto-loading classes
\buildr\Startup\BuildrStartup::setBasePath($basePath);
\buildr\Startup\BuildrStartup::initializeAutoloading(TRUE);

//Using the WebInitializer class to initialize the framework
$startup = new \buildr\Startup\BuildrStartup();
$startup->setInitializer(new \buildr\Startup\Initializer\WebInitializer);

/**
 * @var \buildr\Application\Application $app
 */
$app = \buildr\Application\Application::getContainer()->get('application');
$appConfig = \buildr\Config\Config::getMainConfig()['application']['web'];

$app->initialize($appConfig);
$response = $app->run(APP_NS);

if($response instanceof \buildr\Http\Response\ResponseInterface) {
    echo $response->send();
}
