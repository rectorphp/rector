<?php

declare (strict_types=1);
namespace Rector\Testing\PHPUnit;

use RectorPrefix202308\Illuminate\Container\Container;
use PHPUnit\Framework\TestCase;
use Rector\Core\Contract\Rector\PhpRectorInterface;
use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\DependencyInjection\LazyContainerFactory;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Util\Reflection\PrivatesAccessor;
use RectorPrefix202308\Webmozart\Assert\Assert;
abstract class AbstractLazyTestCase extends TestCase
{
    /**
     * @var \Illuminate\Container\Container|null
     */
    private static $container;
    /**
     * @api
     * @param string[] $configFiles
     */
    protected function bootFromConfigFiles(array $configFiles) : void
    {
        $container = self::getContainer();
        foreach ($configFiles as $configFile) {
            $configClosure = (require $configFile);
            Assert::isCallable($configClosure);
            $configClosure($container);
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
    protected static function getContainer() : Container
    {
        if (!self::$container instanceof Container) {
            $lazyContainerFactory = new LazyContainerFactory();
            self::$container = $lazyContainerFactory->create();
        }
        return self::$container;
    }
    /**
     * @api soon be used
     */
    protected function forgetRectorsRules() : void
    {
        $container = self::getContainer();
        // 1. forget instance first! then remove tags
        $rectors = $container->tagged(RectorInterface::class);
        foreach ($rectors as $rector) {
            $container->offsetUnset(\get_class($rector));
        }
        // 2. remove all tagged rules
        $privatesAccessor = new PrivatesAccessor();
        $privatesAccessor->propertyClosure($container, 'tags', static function (array $tags) : array {
            unset($tags[RectorInterface::class]);
            unset($tags[PhpRectorInterface::class]);
            return $tags;
        });
        $rectors = $container->tagged(RectorInterface::class);
        foreach ($rectors as $rector) {
            $container->offsetUnset(\get_class($rector));
        }
        // 3. remove after binding too, to avoid setting configuration over and over again
        $privatesAccessor->propertyClosure($container, 'afterResolvingCallbacks', static function (array $afterResolvingCallbacks) : array {
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
