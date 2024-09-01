<?php

declare (strict_types=1);
namespace Rector\DependencyInjection\Laravel;

use RectorPrefix202409\Illuminate\Container\Container;
use Rector\Util\Reflection\PrivatesAccessor;
/**
 * Helper service to modify Laravel container
 */
final class ContainerMemento
{
    /**
     * @api
     * @see https://tomasvotruba.com/blog/removing-service-from-laravel-container-is-not-that-easy
     */
    public static function forgetTag(Container $container, string $tagToForget) : void
    {
        // 1. forget instances
        $taggedClasses = $container->tagged($tagToForget);
        foreach ($taggedClasses as $taggedClass) {
            $container->offsetUnset(\get_class($taggedClass));
        }
        // 2. forget tagged references
        $privatesAccessor = new PrivatesAccessor();
        $privatesAccessor->propertyClosure($container, 'tags', static function (array $tags) use($tagToForget) : array {
            unset($tags[$tagToForget]);
            return $tags;
        });
    }
    public static function forgetService(Container $container, string $typeToForget) : void
    {
        // 1. remove the service
        $container->offsetUnset($typeToForget);
        // 2. remove all tagged rules
        $privatesAccessor = new PrivatesAccessor();
        $privatesAccessor->propertyClosure($container, 'tags', static function (array $tags) use($typeToForget) : array {
            foreach ($tags as $tagName => $taggedClasses) {
                foreach ($taggedClasses as $key => $taggedClass) {
                    if (\is_a($taggedClass, $typeToForget, \true)) {
                        unset($tags[$tagName][$key]);
                    }
                }
            }
            return $tags;
        });
    }
}
