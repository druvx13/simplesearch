<?php

namespace SimpleSearch\Router;

class Router
{
    /** @var Route[] */
    private array $routes = [];

    public function addRoute(Route $route): void
    {
        $this->routes[] = $route;
    }

    public function get(string $pattern, callable|array $handler): Route
    {
        $route = new Route('GET', $pattern, $handler);
        $this->routes[] = $route;
        return $route;
    }

    public function post(string $pattern, callable|array $handler): Route
    {
        $route = new Route('POST', $pattern, $handler);
        $this->routes[] = $route;
        return $route;
    }

    public function any(string $pattern, callable|array $handler): Route
    {
        $route = new Route('ANY', $pattern, $handler);
        $this->routes[] = $route;
        return $route;
    }

    public function match(string $method, string $path): ?Route
    {
        foreach ($this->routes as $route) {
            $routeMethod = $route->getMethod();
            if ($routeMethod === 'ANY' || $routeMethod === strtoupper($method)) {
                if ($route->matches($method, $path)) {
                    return $route;
                }
            }
        }
        return null;
    }

    public function dispatch(string $method, string $path, array $middlewareInstances = []): mixed
    {
        $route = $this->match($method, $path);

        if ($route === null) {
            return null;
        }

        // Run middleware
        foreach ($route->getMiddleware() as $middlewareName) {
            if (isset($middlewareInstances[$middlewareName])) {
                $result = $middlewareInstances[$middlewareName]->handle();
                if ($result === false) {
                    return null;
                }
            }
        }

        $handler = $route->getHandler();
        $params = $route->extractParams($path);

        if (is_array($handler) && count($handler) === 2) {
            [$controller, $method] = $handler;
            if (is_object($controller)) {
                return $controller->$method($params);
            }
        }

        if (is_callable($handler)) {
            return $handler($params);
        }

        return null;
    }
}
