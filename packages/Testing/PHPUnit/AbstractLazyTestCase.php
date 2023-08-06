<?php

declare (strict_types=1);
namespace Rector\Testing\PHPUnit;

use PHPUnit\Framework\TestCase;
use Rector\Core\DependencyInjection\LazyContainerFactory;
abstract class AbstractLazyTestCase extends TestCase
{
    /**
     * @template TType as object
     * @param class-string<TType> $class
     * @return TType
     */
    protected function make(string $class) : object
    {
        // @todo cache container
        $lazyContainerFactory = new LazyContainerFactory();
        $container = $lazyContainerFactory->create();
        return $container->make($class);
    }
}
