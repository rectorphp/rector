<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\Reflection\BetterReflection;

final class RectorBetterReflectionSourceLocatorFactory
{
    public function __construct(\Roave\BetterReflection\SourceLocator\Type\SourceLocator $sourceLocator)
    {
        dump($sourceLocator);
    }
}
