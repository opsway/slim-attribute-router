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
it on to AppFactory. First parameter is root namespace part of app. It will be used for filtering classes in which 
search of parameters will go on.

```php
<?php
use OpsWay\Slim\AttributeRouter\Router;
use Slim\Factory\AppFactory;
$route = new Router([
    ['NameSpace'], // the "root" namespace of app 
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
* All other parameters is optional

## Adding Attribute
See examples above:
