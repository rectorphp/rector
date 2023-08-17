<?php

declare (strict_types=1);
namespace Rector\Core\DependencyInjection\Laravel;

use RectorPrefix202308\Illuminate\Container\Container;
use Rector\Core\Util\Reflection\PrivatesAccessor;
/**
 * Helper service to modify Laravel container
 */
final class ContainerMemento
{
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
