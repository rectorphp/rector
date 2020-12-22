<?php

namespace Rector\Order\Tests\Rector\ClassMethod\OrderConstructorDependenciesByTypeAlphabeticallyRector\Fixture;

class SkipHasBoolTypeParam
{
    public function __construct(
        LatteToTwigConverter $latteToTwigConverter,
        SymfonyStyle $symfonyStyle,
        LatteAndTwigFinder $latteAndTwigFinder,
        bool $smartFileSystem
    ) {
    }
}

?>
