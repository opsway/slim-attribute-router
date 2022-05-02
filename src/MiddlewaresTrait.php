<?php

declare(strict_types=1);

namespace OpsWay\Slim\AttributeRouter;

use Assert\Assertion;
use Assert\AssertionFailedException;
use OpsWay\Slim\AttributeRouter\Exception\InvalidMiddlewareException;
use Psr\Http\Server\MiddlewareInterface;

trait MiddlewaresTrait
{
    /** @var string[] */
    private array $middlewares;

    protected function traitValidate() : void
    {
        try {
            Assertion::allNotEmpty($this->middlewares, 'Middleware class name can not be empty');
            Assertion::allString($this->middlewares, 'Each middleware class must be passed as string');
            Assertion::allImplementsInterface(
                $this->middlewares,
                MiddlewareInterface::class,
                'Unknown middleware class "%s"'
            );
        } catch (AssertionFailedException $e) {
            throw new InvalidMiddlewareException($e->getMessage(), 0, $e);
        }
    }

    /** @psalm-return array<array-key, string> */
    public function middlewares() : array
    {
        return $this->middlewares;
    }
}
