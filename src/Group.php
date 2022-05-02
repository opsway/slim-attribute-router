<?php

declare(strict_types=1);

namespace OpsWay\Slim\AttributeRouter;

use Assert\Assertion;
use Assert\AssertionFailedException;
use Attribute;
use OpsWay\Slim\AttributeRouter\Exception\InvalidGroupException;

use function filter_var;
use function ltrim;
use function sprintf;

use const FILTER_FLAG_PATH_REQUIRED;
use const FILTER_VALIDATE_URL;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Group
{
    use MiddlewaresTrait;

    private string $name;

    public const DEFAULT_NAME = '/no_group';

    /**
     * @psalm-param array<array-key, class-string> $classes
     */
    public function __construct(string $name, array $classes = [])
    {
        $this->name        = sprintf('/%s', ltrim($name, '/'));
        $this->middlewares = $classes;

        $this->validate();
    }

    protected function validate() : void
    {
        try {
            Assertion::notEmpty($this->name, 'Group name can not be empty');
            Assertion::true(
                (bool) filter_var('https://example.com' . $this->name, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED),
                sprintf('Invalid format of group name "%s"', $this->name),
            );
        } catch (AssertionFailedException $e) {
            throw new InvalidGroupException($e->getMessage(), 0, $e);
        }
        $this->traitValidate();
    }

    public function name() : string
    {
        return $this->name;
    }
}
