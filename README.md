# slim-attribute-router
Slim attribute router

This package allows you to add routes to your slim4 (https://www.slimframework.com) application using attributes.

### Features
* Route methods support
* Route name support
* Route group support
* Middlewares support

## Installation
This package can be installed using Composer  
Navigate into your project's root directory and execute the bash command shown below
```bash
composer require opsway/slim-attribute-router
```

## Enabling the Attribute Router
Attribute router extends slims default RouteCollector, all you need to do is instantiate attribute router and pass
it on to AppFactory. First parameter is array of namespace parts of app. It will be used for filtering classes in which 
search of parameters will go on.

```php
<?php
use OpsWay\Slim\AttributeRouter\Router;
use Slim\Factory\AppFactory;
$route = new Router([
    ['NameSpace'], // array of namespaces parts of app 
	AppFactory::determineResponseFactory(),
	new CallableResolver($container) // optional DI container
);
AppFactory::setRouteCollector($attributeRouteCollector);
$app = AppFactory::create();
$app->run();
```

## Attribute signature
**#[Route({methods}, {path}[[, {group}][, {name}][, {isDeprecated}]])]**

| Parameter      | Example              | Description                               |
|----------------|----------------------|-------------------------------------------|
| {methods}      | ['GET', 'POST']      | (array)  The allowed HTTP request methods |
| {path}         | '/hello/{parameter}' | (string) The route pattern                |
| {group}        | 'group'              | (string) The group name                   |
| {name}         | 'route.Name'         | (string) The name of the route            |
| {isDeprecated} | 'false'              | (bool) Flag if route is deprecated        |

* The "methods" parameter is required and must be not empty
* The "path" parameter is required and must be not empty
* Rest of parameters are optional

**#[Group({name} [[, {classes}]])]**

| Parameter | Example               | Description                     |
|-----------|-----------------------|---------------------------------|
| {name}    | '/api'                | (string) The group name         |
| {classes} | ['Middleware::class'] | (array) Middlewares class names |

* The "name" parameter is required and must be compatible with URI string requirements
* The "classes" parameter is optional. List of classes implementing Psr\Http\Server\MiddlewareInterface

**#[Middlewares({firstClass} [, {secondClass}])]**

| Parameter    | Example               | Description                    |
|--------------|-----------------------|--------------------------------|
| {firstClass} | ['Middleware::class'] | (string) Middleware class name |

* The "firstClass" parameter is required and must class name of class that implements Psr\Http\Server\MiddlewareInterface
* The "secondClass" and all other parameters is optional.
* All arguments of constructor are converted to an array of middleware classes. It should be at least one middleware class

## Specifying attributes examples

Example specifying route with name and group parameters at class level
```php
<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

#[Route([Route::METHOD_GET], '/api/hello-world', '/hello-world-group', 'api.hello-world.route-name')]
class HelloWorld
{
    public function __invoke(Request $request, Response $response): Response
    {
        // some php code
        return $response;
    }
}
```

Example specifying route without additional parameters at method level
```php
<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class HelloWorld
{
    #[Route([Route::METHOD_GET], '/api/hello-world')]
    public function __invoke(Request $request, Response $response): Response
    {
        // some php code
        return $response;
    }
}
```

Example specifying group with middlewares at class level
```php
<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

#[Group('/api', [FirstMidleware::class, SecondMidleware::class])]
class HelloWorld
{
    public function __invoke(Request $request, Response $response): Response
    {
        // some php code
        return $response;
    }
}
```
