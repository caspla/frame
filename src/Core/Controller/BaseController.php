<?php
namespace Core\Controller;

use Core\View;

/**
 * Class BaseController
 * @package Core\Controller
 * @author Pascal Frey
 */
abstract class BaseController
{
    /**
     * @callback
     */
    public function initialize()
    {

    }

    /**
     * @callback
     */
    public function finalize()
    {

    }

    /**
     * Redirect to a route
     *
     * @param $route
     * @param int $httpResponseCode
     */
    public function redirect($route, $httpResponseCode = 301)
    {
        header('Location: ' . $route, null, $httpResponseCode);
        exit;
    }

    /**
     * Render a view template and return its content
     *
     * @param $viewTemplate
     * @param array $vars
     * @return string
     */
    public function render($viewTemplate, $vars = [])
    {
        $view = new View($viewTemplate);
        foreach ($vars as $key => $value) $view->$key = $value;
        return $view->render();
    }
}