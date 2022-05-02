<?php

declare(strict_types=1);

namespace Tests\Unit\Attributes;

use OpsWay\Slim\AttributeRouter\Exception\InvalidMiddlewareException;
use OpsWay\Slim\AttributeRouter\Middlewares;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Attributes\Stubs\FirstValidMiddleware;
use Tests\Unit\Attributes\Stubs\SecondValidMiddleware;
use Throwable;

/** @psalm-suppress PropertyNotSetInConstructor */
class MiddlewareTest extends TestCase
{
    public function testValidParams() : void
    {
        $middlewares = [FirstValidMiddleware::class, SecondValidMiddleware::class];
        $middleware  = new Middlewares(...$middlewares);
        self::assertCount(2, $middleware->middlewares());
        self::assertEquals($middlewares, $middleware->middlewares());
    }

    /**
     * @dataProvider validationCasesProvider
     * @psalm-param array<array-key,string> $classes
     */
    public function testValidationExceptions(Throwable $expectedException, array $classes) : void
    {
        $this->expectException($expectedException::class);
        $this->expectExceptionMessage($expectedException->getMessage());

        new Middlewares(...$classes);
    }

    /**
     * @psalm-suppress MixedInferredReturnType
     */
    public function validationCasesProvider() : iterable
    {
        yield 'When not passed middleware classes' => [
            new InvalidMiddlewareException(
                'Attribute Middlewares can not be defined without passed middlewares classes'
            ),
            [],
        ];

        yield 'When passed empty class name' => [
            new InvalidMiddlewareException('Middleware class name can not be empty'),
            [''],
        ];

        yield 'When passed invalid type of class name' => [
            new InvalidMiddlewareException('Each middleware class must be passed as string'),
            [FirstValidMiddleware::class, 12345, SecondValidMiddleware::class],
        ];

        yield 'When passed not existing middleware class' => [
            new InvalidMiddlewareException('Unknown middleware class "\FakeMiddleware"'),
            [FirstValidMiddleware::class, '\FakeMiddleware'],
        ];
    }
}
