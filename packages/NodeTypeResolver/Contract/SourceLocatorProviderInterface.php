<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\Contract;

use PHPStan\BetterReflection\SourceLocator\Type\SourceLocator;
interface SourceLocatorProviderInterface
{
    public function provide() : \PHPStan\BetterReflection\SourceLocator\Type\SourceLocator;
}
