<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\NodeTypeResolver\Contract;

use RectorPrefix20220606\PHPStan\BetterReflection\SourceLocator\Type\SourceLocator;
interface SourceLocatorProviderInterface
{
    public function provide() : SourceLocator;
}
