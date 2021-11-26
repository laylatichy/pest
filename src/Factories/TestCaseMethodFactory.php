<?php

declare(strict_types=1);

namespace Pest\Factories;

use Closure;
use Pest\Exceptions\ShouldNotHappen;
use Pest\Factories\Concerns\HigherOrderable;
use Pest\TestSuite;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class TestCaseMethodFactory
{
    use HigherOrderable;
    /**
     * Determines if the Test Case will be the "only" being run.
     */
    public bool $only = false;

    /**
     * The Test Case Dataset, if any.
     *
     * @var array<Closure|iterable<int|string, mixed>|string>
     */
    public array $datasets = [];

    /**
     * The Test Case depends, if any.
     *
     * @var array<int, string>
     */
    public array $depends = [];

    /**
     * The Test Case groups, if any.
     *
     * @var array<int, string>
     */
    public array $groups = [];

    /**
     * Creates a new Factory instance.
     */
    public function __construct(
        public string $filename,
        public ?string $description,
        public ?Closure $closure,
    ) {
        if ($this->closure === null) {
            $this->closure = function () {
                Assert::getCount() > 0 ?: self::markTestIncomplete(); // @phpstan-ignore-line
            };
        }

        $this->bootHigherOrderable();
    }

    /**
     * Makes the Test Case classes.
     */
    public function getClosure(TestCase $concrete): Closure
    {
        $concrete::flush(); // @phpstan-ignore-line

        if ($this->description === null) {
            throw ShouldNotHappen::fromMessage('Description can not be empty.');
        }

        $closure = $this->closure;

        $testCase = TestSuite::getInstance()->tests->get($this->filename);

        $testCase->factoryProxies->proxy($concrete);
        $this->factoryProxies->proxy($concrete);

        $method = $this;

        return function () use ($testCase, $method, $closure): mixed { // @phpstan-ignore-line
            /* @var TestCase $this */

            $testCase->proxies->proxy($this);
            $method->proxies->proxy($this);

            $testCase->chains->chain($this);
            $method->chains->chain($this);

            return \Pest\Support\Closure::bind($closure, $this, $this::class)(...func_get_args());
        };
    }

    /**
     * Determine if the test case will receive argument input from Pest, or not.
     */
    public function receivesArguments(): bool
    {
        return count($this->datasets) > 0 || count($this->depends) > 0;
    }
}
