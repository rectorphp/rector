<?php

declare (strict_types=1);
namespace Rector\Bridge;

use Rector\Config\RectorConfig;
use Rector\Contract\Rector\RectorInterface;
use ReflectionProperty;
use RectorPrefix202409\Webmozart\Assert\Assert;
/**
 * @api
 * @experimental since 1.1.2
 * Utils class to ease building bridges by 3rd-party tools
 */
final class SetRectorsResolver
{
    /**
     * @return array<class-string<RectorInterface>>
     */
    public function resolveFromFilePath(string $configFilePath) : array
    {
        Assert::fileExists($configFilePath);
        $rectorConfig = new RectorConfig();
        /** @var callable $configCallable */
        $configCallable = (require $configFilePath);
        $configCallable($rectorConfig);
        // get tagged class-names
        $tagsReflectionProperty = new ReflectionProperty($rectorConfig, 'tags');
        $tagsReflectionProperty->setAccessible(\true);
        $tags = $tagsReflectionProperty->getValue($rectorConfig);
        $rectorClasses = $tags[RectorInterface::class] ?? [];
        \sort($rectorClasses);
        return \array_unique($rectorClasses);
    }
}
