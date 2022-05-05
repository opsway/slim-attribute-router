<?php

declare(strict_types=1);

namespace Tests\Unit\Attributes;

use OpsWay\Slim\AttributeRouter\Exception\InvalidRouteException;
use OpsWay\Slim\AttributeRouter\Group;
use OpsWay\Slim\AttributeRouter\Route;
use PHPUnit\Framework\TestCase;
use Throwable;

use function array_merge;

/** @psalm-suppress PropertyNotSetInConstructor */
class RouteTest extends TestCase
{
    private const DEFAULT_PARAMS = [
        'methods'      => ['GET', 'PUT'],
        'path'         => 'some/{part_id:\d+}.ext',
        'name'         => 'Route.NAME',
        'group'        => '/Group',
        'isDeprecated' => true,
    ];

    /**
     * @dataProvider validCasesProvider
     * @psalm-param array{
     *   'methods': array<array-key,string>,
     *   'path': string,
     *   'name': string,
     *   'group': ?string,
     *   'isDeprecated': bool
     * } $data
     */
    public function testSuccess(array $data) : void
    {
        $route         = new Route(...$data);
        $expectedGroup = empty($data['group']) ? Group::DEFAULT_NAME : '/group';

        self::assertEquals([Route::METHOD_GET, Route::METHOD_PUT], $route->methods());
        self::assertEquals('/some/{part_id:\d+}.ext', $route->path());
        self::assertEquals($expectedGroup, $route->group());
        self::assertEquals('route.name', $route->name());
        self::assertTrue($route->isDeprecated());
    }

    /**
     * @dataProvider invalidCasesProvider
     * @psalm-param array{
     *   'methods': array<array-key,string>,
     *   'path': string,
     *   'name': string,
     *   'group': string,
     *   'isDeprecated': bool
     * } $data
     */
    public function testValidationExceptions(Throwable $expectedException, array $data) : void
    {
        $this->expectException($expectedException::class);
        $this->expectExceptionMessage($expectedException->getMessage());

        new Route(...$data);
    }

    public function validCasesProvider() : iterable
    {
        yield 'When passed all valid params' => [self::DEFAULT_PARAMS];
        yield 'When passed all valid params and group equals empty string' => [
            array_merge(self::DEFAULT_PARAMS, ['group' => '']),
        ];
        yield 'When passed all valid params and group equals null' => [
            array_merge(self::DEFAULT_PARAMS, ['group' => null]),
        ];
    }

    public function invalidCasesProvider() : iterable
    {
        yield 'When passed empty route name' => [
            new InvalidRouteException('Route name can not be empty'),
            array_merge(self::DEFAULT_PARAMS, ['name' => '']),
        ];

        yield 'When passed route name with invalid characters' => [
            new InvalidRouteException('Invalid format of route name "name_!@#"'),
            array_merge(self::DEFAULT_PARAMS, ['name' => 'Name_!@#']),
        ];

        yield 'When passed invalid route path' => [
            new InvalidRouteException('Invalid format of route group "/group" or path "/invalid/ /path"'),
            array_merge(self::DEFAULT_PARAMS, ['path' => 'invalid/ /path']),
        ];

        yield 'When passed invalid group' => [
            new InvalidRouteException(
                'Invalid format of route group "/invalid/ /group" or path "/some/{part_id:\d+}.ext"'
            ),
            array_merge(self::DEFAULT_PARAMS, ['group' => 'invalid/ /group']),
        ];

        yield 'When passed invalid method' => [
            new InvalidRouteException('Unexpected routing method "FAKE"'),
            array_merge(self::DEFAULT_PARAMS, ['methods' => ['GET', 'POST', 'FAKE']]),
        ];
    }
}
