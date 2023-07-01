<?php

declare (strict_types=1);
namespace Rector\Testing\PHPUnit;

use RectorPrefix202307\Illuminate\Container\Container;
use PHPUnit\Framework\TestCase;
abstract class AbstractLazyTestCase extends TestCase
{
    /**
     * @template TType as object
     * @param class-string<TType> $class
     * @return TType
     */
    protected function make(string $class) : object
    {
        $container = new Container();
        return $container->make($class);
    }
}
