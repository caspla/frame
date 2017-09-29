<?php
namespace Core\Controller;

/**
 * Class Dispatcher
 * @package Core\Controller
 * @author Pascal Frey
 */
class Dispatcher
{
    /**
     * Holds the current route
     *
     * @var string
     */
    protected $currentRoute;

    /**
     * Holds a map of known routes
     *
     * @var array
     */
    protected $routes = [];

    /**
     * Initialize the Dispatcher
     *
     * @param $currentRoute
     */
    public function __construct($currentRoute)
    {
        $this->currentRoute = $currentRoute;
        return $this;
    }

    /**
     * Set map of known routes.
     *
     * @param array $routes
     * @return $this
     */
    public function setRoutes(array $routes)
    {
        $this->routes = $routes;
        return $this;
    }

    // Detect the current route against the map of known routes.
    public function detectRoute()
    {
        // iterate over route map
        foreach ($this->routes as $route => $config)
        {
            // replace placeholder marks with real regular expressions
            $replacements = [
                '/' => '\/',
                '{id}' => '(\d*)',
                '{slug}' => '([\w+-]*)'
            ];
            // build regexp pattern
            $pattern = '~^' . str_replace(array_keys($replacements), $replacements, $route) . '$~';

            // match current route against the regular expression
            preg_match_all($pattern, $this->currentRoute, $matches, PREG_SET_ORDER);

            // if there is a match, return route configuration array
            if (isset($matches[0]))
            {
                array_shift($matches[0]);

                // append params
                $config['params'] = $matches[0];
                return $config;
            }
        }
        return false;
    }

    /**
     * Execute the dispatch process
     */
    public function execute()
    {
        $response = '';

        // detect the current route
        $route = $this->detectRoute();
        if ($route)
        {
            // run controller
            if (isset($route['controller']) && isset($route['action']))
            {
                $response = $this->runController($route['controller'], $route['action'], $route['params']);
            }
            // include a file
            else if (isset($route['file']) && is_file($route['file']))
            {
                ob_start();
                include $route['file'];
                return ob_get_clean();
            }
        }
        else
        {
            $response = '404 - Page not found';
        }
        return $response;
    }

    /**
     * Creates object of the given controller class and runs the given action method.
     *
     * @param string $controller
     * @param string $action
     * @param array $params
     * @return string
     */
    public function runController($controller, $action, $params = [])
    {
        $controllerClass = 'App\\Controller\\' . $controller;

        // create object
        $controllerObject = new $controllerClass;

        // execute "initialize" callback
        $controllerObject->initialize();

        // call action method with optional params as method arguments
        $response = $controllerObject->$action(
            isset($params[0]) ? $params[0] : '',
            isset($params[1]) ? $params[1] : '',
            isset($params[2]) ? $params[1] : '',
            isset($params[3]) ? $params[3] : ''
        );
        // execute "finalize" callback
        $controllerObject->finalize();

        return $response;
    }
}