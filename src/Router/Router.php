<?php
/**
 * Created by PhpStorm.
 * User: bennet
 * Date: 06.07.18
 * Time: 00:36
 */

namespace intraframe\Router;


class Router {

    private $base;
    private $routes = [];
    protected static $_instance = null;

    public static function getInstance() {
        if (null === self::$_instance) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    protected function __clone() {

    }

    protected function __construct() {
    }

    public function get($route, ViewModel $viewmodel) {
        $this->routes[] = ['route' => $route, 'model' => $viewmodel, 'method' => "GET"];
    }

    public function post($route, ViewModel $viewmodel) {
        $this->routes[] = ['route' => $route, 'model' => $viewmodel, 'method' => "POST"];
    }

    public function put($route, ViewModel $viewmodel) {
        $this->routes[] = ['route' => $route, 'model' => $viewmodel, 'method' => "PUT"];
    }

    public function delete($route, ViewModel $viewmodel) {
        $this->routes[] = ['route' => $route, 'model' => $viewmodel, 'method' => "DELETE"];
    }

    public function patch($route, ViewModel $viewmodel) {
        $this->routes[] = ['route' => $route, 'model' => $viewmodel, 'method' => "PATCH"];
    }


    public function routeExists($route, $requestMethod) {
        $routeExists = false;
        if ($this->base != "") {
            $route = substr($route, strlen("/" . $this->base) - 1);
        }
        $routeRequestArray = ($route == '/' ? ['/'] : explode('/', trim($route, '/')));
        foreach ($this->routes as $route_) {
            if ($route_['method'] == $requestMethod) {
                $routeArray = ($route_['route'] == '/' ? ['/'] : explode('/', trim($route_['route'], '/')));

                $routeItemsIgnored = [];

                for ($i = 0; $i < sizeof($routeArray); $i++) {
                    if (self::startsWith($routeArray[$i], "{")) {
                        $routeItemsIgnored[] = $i;
                    }
                }
                for ($i = 0; $i < sizeof($routeRequestArray); $i++) {
                    if (!in_array($i, $routeItemsIgnored)) {
                        $routeExists = ($routeArray[$i] == $routeRequestArray[$i]);
                    }
                }
                if ($routeExists)
                    break;
            }
        }
        return $routeExists;
    }

    public function getRoute($route, $requestMethod) {
        $outputRoute = null;
        if ($this->base != "") {
            $route = substr($route, strlen("/" . $this->base) - 1);
        }
        $routeRequestArray = ($route == '/' ? ['/'] : explode('/', trim($route, '/')));
        $routeIndex = 0;
        foreach ($this->routes as $route_) {
            if ($route_['method'] == $requestMethod) {
                $routeArray = ($route_['route'] == '/' ? ['/'] : explode('/', trim($route_['route'], '/')));
                $routeItemsIgnored = [];
                for ($i = 0; $i < sizeof($routeArray); $i++) {
                    if (self::startsWith($routeArray[$i], "{")) {
                        $routeItemsIgnored[] = $i;
                    }
                }
                for ($i = 0; $i < sizeof($routeRequestArray); $i++) {
                    if (!in_array($i, $routeItemsIgnored)) {
                        if ($routeArray[$i] == $routeRequestArray[$i]) {
                            $outputRoute = $route_;
                        }
                    }
                }
                $this->routes[$routeIndex]['parameterIndexes'] = $routeItemsIgnored;
            }
            $routeIndex++;
        }
        return $outputRoute;
    }

    public function run(\Mustache_Engine $mustacheEngine, $route, $requestMethod) {
        $routeData = self::getRoute($route, $requestMethod);
        $routeArray = explode('/', trim($route, '/'));

        preg_match_all('/{(.*?)}/', $routeData['route'], $matchesRoute);
        $requestParams = [];
        for ($i = 0; $i < sizeof($matchesRoute[1]); $i++) {
            $requestParams[$matchesRoute[1][$i]] = $routeArray[$i + 1];
        }
        $viewModel = $routeData['model'];

        $html = $viewModel->run($requestParams);
        if ($viewModel->getTemplate() == null) {
            echo $html;
        } else {
            $template = $mustacheEngine->loadTemplate($viewModel->getTemplate());
            echo $template->render($viewModel->getModel());
        }
    }

    public function startsWith($haystack, $needle) {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }

    /**
     * @return string
     */
    public function getBasepath() {
        return $this->base;
    }

    /**
     * @param $path
     */
    public function setBasepath($path) {
        $this->base = $path;
    }
}