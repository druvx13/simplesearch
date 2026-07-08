<?php

namespace SimpleSearch\Router;

class Route
{
    private string $method;
    private string $pattern;
    private $handler;
    private array $middleware = [];

    public function __construct(string $method, string $pattern, callable|array $handler)
    {
        $this->method = strtoupper($method);
        $this->pattern = $pattern;
        $this->handler = $handler;
    }

    public function middleware(string $middleware): self
    {
        $this->middleware[] = $middleware;
        return $this;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getPattern(): string
    {
        return $this->pattern;
    }

    public function getHandler(): callable|array
    {
        return $this->handler;
    }

    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    public function matches(string $method, string $path): bool
    {
        if ($this->method !== strtoupper($method)) {
            return false;
        }

        // Convert pattern with {param} placeholders to regex
        $regex = preg_replace('/\{(\w+)\}/', '(?P<$1>[^\/]+)', $this->pattern);
        $regex = '#^' . $regex . '$#';

        return (bool) preg_match($regex, $path);
    }

    public function extractParams(string $path): array
    {
        $regex = preg_replace('/\{(\w+)\}/', '(?P<$1>[^\/]+)', $this->pattern);
        $regex = '#^' . $regex . '$#';

        if (preg_match($regex, $path, $matches)) {
            $params = [];
            foreach ($matches as $key => $value) {
                if (is_string($key)) {
                    $params[$key] = $value;
                }
            }
            return $params;
        }

        return [];
    }
}
