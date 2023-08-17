<?php

declare (strict_types=1);
namespace Rector\Testing\PHPUnit;

use PHPUnit\Framework\TestCase;
use Rector\Config\RectorConfig;
use Rector\Core\Contract\Rector\PhpRectorInterface;
use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\DependencyInjection\LazyContainerFactory;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Util\Reflection\PrivatesAccessor;
abstract class AbstractLazyTestCase extends TestCase
{
    /**
     * @var \Rector\Config\RectorConfig|null
     */
    private static $rectorConfig;
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
        return self::$rectorConfig;
    }
    protected function forgetRectorsRules() : void
    {
        $rectorConfig = self::getContainer();
        // 1. forget instance first, then remove tags
        $rectors = $rectorConfig->tagged(RectorInterface::class);
        foreach ($rectors as $rector) {
            $rectorConfig->offsetUnset(\get_class($rector));
        }
        // 2. remove all tagged rules
        $privatesAccessor = new PrivatesAccessor();
        $privatesAccessor->propertyClosure($rectorConfig, 'tags', static function (array $tags) : array {
            unset($tags[RectorInterface::class]);
            unset($tags[PhpRectorInterface::class]);
            return $tags;
        });
        // 3. remove after binding too, to avoid setting configuration over and over again
        $privatesAccessor->propertyClosure($rectorConfig, 'afterResolvingCallbacks', static function (array $afterResolvingCallbacks) : array {
            foreach (\array_keys($afterResolvingCallbacks) as $key) {
                if ($key === AbstractRector::class) {
                    continue;
                }
                if (\is_a($key, RectorInterface::class, \true)) {
                    unset($afterResolvingCallbacks[$key]);
                }
            }
            return $afterResolvingCallbacks;
        });
    }
}
