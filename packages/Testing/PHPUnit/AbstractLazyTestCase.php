<?php

declare (strict_types=1);
namespace Rector\Testing\PHPUnit;

use RectorPrefix202308\Illuminate\Container\Container;
use PHPUnit\Framework\TestCase;
use Rector\Core\DependencyInjection\LazyContainerFactory;
abstract class AbstractLazyTestCase extends TestCase
{
    /**
     * @var \Illuminate\Container\Container|null
     */
    private static $container;
    /**
     * @template TType as object
     * @param class-string<TType> $class
     * @return TType
     */
    protected function make(string $class) : object
    {
        return self::getContainer()->make($class);
    }
    protected static function getContainer() : Container
    {
        if (!self::$container instanceof Container) {
            $lazyContainerFactory = new LazyContainerFactory();
            self::$container = $lazyContainerFactory->create();
        }
        return self::$container;
    }
}
