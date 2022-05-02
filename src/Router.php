<?php

declare(strict_types=1);

namespace OpsWay\Slim\AttributeRouter;

use Composer\Autoload\ClassLoader;
use OpsWay\Slim\AttributeRouter\VO\RouteDataVO;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use ReflectionClass;
use ReflectionMethod;
use Slim\Interfaces\CallableResolverInterface;
use Slim\Interfaces\InvocationStrategyInterface;
use Slim\Interfaces\RouteParserInterface;
use Slim\Routing\RouteCollector;
use Slim\Routing\RouteCollectorProxy;
use Throwable; //phpcs:ignore

use function array_filter;
use function array_keys;
use function array_merge;
use function file_exists;
use function str_contains;

use const ARRAY_FILTER_USE_KEY;

class Router extends RouteCollector
{
    private const COMPOSER_AUTOLOAD = __DIR__ . '/../../../autoload.php';
    private const GROUP_ROUTES_KEY  = 'routes';
    private const GROUP_MW_KEY      = 'middlewares';

    private array $groups = [];

    public function __construct(
        private array $nsParts,
        ResponseFactoryInterface $responseFactory,
        CallableResolverInterface $callableResolver,
        ?ContainerInterface $container = null,
        ?InvocationStrategyInterface $defaultInvocationStrategy = null,
        ?RouteParserInterface $routeParser = null,
        ?string $cacheFile = null
    ) {
        parent::__construct(
            $responseFactory,
            $callableResolver,
            $container,
            $defaultInvocationStrategy,
            $routeParser,
            $cacheFile
        );

        $this->setupRoutes();
    }

    private function setupRoutes() : void
    {
        /** @psalm-var class-string $handlerClass */
        foreach ($this->getHandlersClasses() as $handlerClass) {
            $reflectionClass = new ReflectionClass($handlerClass);
            $this->addRoutes($reflectionClass, $handlerClass);

            foreach ($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC) as $reflectionMethod) {
                if ($reflectionMethod->name === '__construct' || $reflectionMethod->class !== $handlerClass) {
                    continue;
                }
                $this->addRoutes($reflectionMethod, $handlerClass);
            }
        }

        /**
         * @var string $groupName
         * @var array $groupParams
         */
        foreach ($this->groups as $groupName => $groupParams) {
            $setRoutesClosure = function (RouteCollector|RouteCollectorProxy $collector) use ($groupParams) : void {
                /** @var RouteDataVO $grRoute */
                foreach ($groupParams[self::GROUP_ROUTES_KEY] as $grRoute) {
                    $route = $collector->map($grRoute->getMethods(), $grRoute->getPath(), $grRoute->getHandler());
                    foreach ($grRoute->getMiddlewares() as $groupRouteMiddleware) {
                        $route->add($groupRouteMiddleware);
                    }
                }
            };
            if ($groupName === Group::DEFAULT_NAME) {
                $setRoutesClosure($this);
            } else {
                $group = $this->group($groupName, $setRoutesClosure);
                /** @var string $groupMiddleware */
                foreach ($groupParams[self::GROUP_MW_KEY] as $groupMiddleware) {
                    $group->add($groupMiddleware);
                }
            }
        }
    }

    private function getHandlersClasses() : array
    {
        /**
         * @var ClassLoader $autoload
         * @psalm-suppress UnresolvableInclude
         */
        $autoload = include self::COMPOSER_AUTOLOAD;

        $nsParts     = $this->nsParts;
        $classMap    = $autoload->getClassMap();
        $handlersMap = array_filter($classMap, static function (string $className) use ($nsParts) : bool {
            /** @var string $nsPart */
            foreach ($nsParts as $nsPart) {
                if (str_contains($className, $nsPart)) {
                    return true;
                }
            }
            return false;
        }, ARRAY_FILTER_USE_KEY);
        $handlersMap = array_filter($handlersMap, static function (string $classPath) : bool {
            try {
                return file_exists($classPath);
            } catch (Throwable) {
                return false;
            }
        });

        return array_keys($handlersMap);
    }

    private function addRoutes(ReflectionClass|ReflectionMethod $reflectionObject, string $class) : void
    {
        foreach ($reflectionObject->getAttributes(Group::class) as $groupAttribute) {
            $groupInstance = $groupAttribute->newInstance();
            $groupName     = $groupInstance->name();
            $this->initGroup($groupName);
            /** @psalm-suppress MixedArrayAssignment */
            $this->groups[$groupName][self::GROUP_MW_KEY] = $groupInstance->middlewares();
        }

        $middlewares = [];
        foreach ($reflectionObject->getAttributes(Middlewares::class) as $middlewareAttribute) {
            $mwaInstance   = $middlewareAttribute->newInstance();
            $middlewares[] = $mwaInstance->middlewares();
        }

        foreach ($reflectionObject->getAttributes(Route::class) as $routeAttribute) {
            $route   = $routeAttribute->newInstance();
            $handler = $reflectionObject instanceof ReflectionClass ? '__invoke' : $reflectionObject->getName();
            $this->addRouteToGroup($route, $class . ':' . $handler, array_merge(...$middlewares));
        }
    }

    /**
     * @psalm-param array<array-key, string> $middlewares
     */
    private function addRouteToGroup(Route $route, string $handler, array $middlewares) : void
    {
        $group = $route->group();
        $this->initGroup($group);
        /** @psalm-suppress MixedArrayAssignment */
        $this->groups[$group][self::GROUP_ROUTES_KEY][] = new RouteDataVO(
            $route->methods(),
            $route->path(),
            $handler,
            $middlewares
        );
    }

    private function initGroup(string $group) : void
    {
        $this->groups[$group] ??= [self::GROUP_ROUTES_KEY => [], self::GROUP_MW_KEY => []];
    }
}
