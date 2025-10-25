<?php

class Router {
    private $routes = [];

    // Gli $handler sono i controller passati dalla rotta
    public function get($path, $handler) {
        $this->routes['GET'][$path] = $handler;
    }

    public function post($path, $handler) {
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch($method, $uri) {
        $uri = parse_url($uri, PHP_URL_PATH);

        foreach ($this->routes[$method] ?? [] as $path => $handler) {
            // Gestione parametri dinamici tipo /cataloghi/{id}
            $pattern = "@^" . preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([0-9a-zA-Z_-]+)', $path) . "$@";

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);
                [$controllerName, $methodName] = explode('@', $handler);
                $controllerClass = "App\\Controllers\\$controllerName";

                require_once __DIR__ . "/controllers/$controllerName.php";
                $controller = new $controllerClass();

                return call_user_func_array([$controller, $methodName], $matches);
            }
        }

        jsonResponse(['error' => 'Endpoint non trovato'], 404);
    }
}