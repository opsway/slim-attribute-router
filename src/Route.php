<?php

declare(strict_types=1);

namespace OpsWay\Slim\AttributeRouter;

use Assert\Assertion;
use Assert\AssertionFailedException;
use Attribute;
use OpsWay\Slim\AttributeRouter\Exception\InvalidRouteException;

use function array_map;
use function filter_var;
use function is_array;
use function ltrim;
use function preg_match;
use function sprintf;
use function str_replace;
use function strtolower;

use const FILTER_FLAG_PATH_REQUIRED;
use const FILTER_VALIDATE_URL;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Route
{
    public const METHOD_GET      = 'GET';
    public const METHOD_POST     = 'POST';
    public const METHOD_PATCH    = 'PATCH';
    public const METHOD_PUT      = 'PUT';
    public const METHOD_DELETE   = 'DELETE';
    public const METHOD_OPTIONS  = 'OPTIONS';
    public const ALLOWED_METHODS = [
        self::METHOD_GET,
        self::METHOD_POST,
        self::METHOD_PATCH,
        self::METHOD_PUT,
        self::METHOD_DELETE,
        self::METHOD_OPTIONS,
    ];

    public const CUSTOM_PATTERNS = [
        '__UUID4__' => '[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}',
    ];

    /** @var string[] */
    private array $methods;
    private string $path;
    private string $group;
    private string $name;
    private bool $isDeprecated;

    /**
     * @psalm-param string|array<array-key,string> $methods
     */
    public function __construct(
        string|array $methods,
        string $path,
        string $name,
        ?string $group = Group::DEFAULT_NAME,
        bool $isDeprecated = false
    ) {
        $this->methods      = ! is_array($methods) ? [$methods] : $methods;
        $this->methods      = array_map('strtoupper', $this->methods);
        $this->path         = $path !== '' ? sprintf('/%s', ltrim($path, '/')) : $path;
        $this->name         = strtolower($name);
        $group              = empty($group) ? Group::DEFAULT_NAME : strtolower($group);
        $this->group        = $group !== Group::DEFAULT_NAME ? sprintf('/%s', ltrim($group, '/')) : $group;
        $this->isDeprecated = $isDeprecated;

        $this->validate();
        $this->injectCustomRoutePatterns();
    }

    /**
     * @psalm-suppress PossiblyInvalidArgument
     */
    protected function validate() : void
    {
        try {
            Assertion::notEmpty($this->name, 'Route name can not be empty');
            Assertion::regex($this->name, '/^[0-9a-z.\-_]+$/', 'Invalid format of route name "%s"');
            Assertion::true(
                (bool) filter_var(
                    'https://example.com' . $this->group . $this->path,
                    FILTER_VALIDATE_URL,
                    FILTER_FLAG_PATH_REQUIRED
                ),
                sprintf('Invalid format of route group "%s" or path "%s"', $this->group, $this->path),
            );
            Assertion::allInArray($this->methods, self::ALLOWED_METHODS, 'Unexpected routing method "%s"');
        } catch (AssertionFailedException $e) {
            throw new InvalidRouteException($e->getMessage(), 0, $e);
        }
    }

    /** @psalm-return array<array-key, string> */
    public function methods() : array
    {
        return $this->methods;
    }

    public function path() : string
    {
        return $this->path;
    }

    public function group() : string
    {
        return $this->group;
    }

    public function name() : string
    {
        return $this->name;
    }

    public function isDeprecated() : bool
    {
        return $this->isDeprecated;
    }

    private function injectCustomRoutePatterns() : void
    {
        $matches = [];
        if (! preg_match('/__\w+__/', $this->path, $matches)) {
            return;
        }
        foreach ($matches as $placeholder) {
            $pattern = self::CUSTOM_PATTERNS[$placeholder] ?? null;
            if (! $pattern) {
                continue;
            }
            $this->path = str_replace($placeholder, $pattern, $this->path);
        }
    }
}
