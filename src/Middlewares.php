<?php

declare(strict_types=1);

namespace OpsWay\Slim\AttributeRouter;

use Assert\Assertion;
use Assert\AssertionFailedException;
use Attribute;
use OpsWay\Slim\AttributeRouter\Exception\InvalidMiddlewareException;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Middlewares
{
    use MiddlewaresTrait;

    /** @param string ...$classes */
    public function __construct(...$classes)
    {
        $this->middlewares = $classes;
        $this->validate();
    }

    protected function validate() : void
    {
        try {
            Assertion::minCount(
                $this->middlewares,
                1,
                'Attribute Middlewares can not be defined without passed middlewares classes'
            );
        } catch (AssertionFailedException $e) {
            throw new InvalidMiddlewareException($e->getMessage(), 0, $e);
        }
        $this->traitValidate();
    }
}
