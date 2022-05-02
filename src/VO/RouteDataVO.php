<?php

declare(strict_types=1);

namespace OpsWay\Slim\AttributeRouter\VO;

class RouteDataVO
{
    /**
     * @psalm-param array<array-key, string> $methods
     * @psalm-param array<array-key, string> $middlewares
     */
    public function __construct(
        private array $methods,
        private string $path,
        private string $handler,
        private array $middlewares,
    ) {
    }

    /** @psalm-return array<array-key, string> */
    public function getMethods() : array
    {
        return $this->methods;
    }

    public function getPath() : string
    {
        return $this->path;
    }

    public function getHandler() : string
    {
        return $this->handler;
    }

    /** @psalm-return array<array-key, string> */
    public function getMiddlewares() : array
    {
        return $this->middlewares;
    }
}
