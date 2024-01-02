<?php

declare (strict_types=1);
namespace Rector\Testing\PHPUnit;

use PHPUnit\Framework\TestCase;
use Rector\Config\RectorConfig;
use Rector\DependencyInjection\LazyContainerFactory;
abstract class AbstractLazyTestCase extends TestCase
{
    /**
     * @var \Rector\Config\RectorConfig|null
     */
    protected static $rectorConfig;
    /**
     * @api
     * @param string[] $configFiles
     */
    protected function bootFromConfigFiles(array $configFiles) : void
    {
        $rectorConfig = self::getContainer();
        foreach ($configFiles as $configFile) {
            $rectorConfig->import($configFile);
        }
    }
    /**
     * @template TType as object
     * @param class-string<TType> $class
     * @return TType
     */
    protected function make(string $class) : object
    {
        return self::getContainer()->make($class);
    }
    protected static function getContainer() : RectorConfig
    {
        if (!self::$rectorConfig instanceof RectorConfig) {
            $lazyContainerFactory = new LazyContainerFactory();
            self::$rectorConfig = $lazyContainerFactory->create();
        }
        self::$rectorConfig->boot();
        return self::$rectorConfig;
    }
}
