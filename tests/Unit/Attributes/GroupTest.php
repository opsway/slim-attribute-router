<?php

declare(strict_types=1);

namespace Tests\Unit\Attributes;

use OpsWay\Slim\AttributeRouter\Exception\InvalidGroupException;
use OpsWay\Slim\AttributeRouter\Group;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Attributes\Stubs\FirstValidMiddleware;
use Tests\Unit\Attributes\Stubs\SecondValidMiddleware;

/** @psalm-suppress PropertyNotSetInConstructor */
class GroupTest extends TestCase
{
    /**
     * @dataProvider getValidParams
     * @psalm-param array<class-string> $middlewares
     */
    public function testValidParams(string $name, string $expectedName, array $middlewares) : void
    {
        $group = new Group($name, $middlewares);
        self::assertEquals($expectedName, $group->name());
        self::assertEquals($middlewares, $group->middlewares());
    }

    /**
     * @dataProvider getInvalidParams
     */
    public function testValidationExceptions(string $name) : void
    {
        $this->expectException(InvalidGroupException::class);
        new Group($name);
    }

    /**
     * @psalm-return iterable<string, array{string, string, array<class-string>}>
     */
    public function getValidParams() : iterable
    {
        yield 'Valid name starting with "/" and not empty valid middleware classes array' => [
            '/middleware',
            '/middleware',
            [FirstValidMiddleware::class, SecondValidMiddleware::class],
        ];
        yield 'Valid name not starting with "/" and not empty valid middleware classes array' => [
            'middleware',
            '/middleware',
            [FirstValidMiddleware::class],
        ];
        yield 'Valid name and empty middleware classes array' => ['middleware', '/middleware', []];
    }

    /**
     * @psalm-return iterable<string, array<string>>
     */
    public function getInvalidParams() : iterable
    {
        yield 'Name with space' => [' name'];
        yield 'Tab in name' => ["n\tame"];
    }
}
