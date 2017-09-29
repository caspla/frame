<?php
// load app configuration
$config = include 'config.php';

// set error reporting
error_reporting($config['error_reporting']);

// include autoloader
include 'vendor/autoload.php';

// include helper functions
include 'helpers.php';

// connect to database when configured
if (isset($config['database']) && is_array($config['database']))
{
    $db = new \Core\Database\Connection();
    $db->connect(
        $config['database']['dsn'],
        isset($config['database']['user']) ? $config['database']['user'] : '',
        isset($config['database']['password']) ? $config['database']['password'] : '',
        isset($config['database']['options']) ? $config['database']['options'] : array(
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        )
    );

    // Share Database object with ActiveRecord class
    \Core\Database\ActiveRecord::database($db);
}

function dispatchRoute($route)
{
    // load controller for current route
    $dispatcher = new Core\Controller\Dispatcher($route);
    $dispatcher->setRoutes(include 'routes.php');
    return $dispatcher->execute();
}