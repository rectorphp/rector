<?php

namespace Rector\Order\Tests\Rector\ClassMethod\OrderConstructorDependenciesByTypeAlphabeticallyRector\Fixture;

class Fixture
{
    public function __construct(
        LatteToTwigConverter $latteToTwigConverter,
        SymfonyStyle $symfonyStyle,
        LatteAndTwigFinder $latteAndTwigFinder,
        SmartFileSystem $smartFileSystem
    ) {
    }
}

?>
-----
<?php

namespace Rector\Order\Tests\Rector\ClassMethod\OrderConstructorDependenciesByTypeAlphabeticallyRector\Fixture;

class Fixture
{
    public function __construct(
        LatteAndTwigFinder $latteAndTwigFinder,
        LatteToTwigConverter $latteToTwigConverter,
        SmartFileSystem $smartFileSystem,
        SymfonyStyle $symfonyStyle
    ) {
    }
}

?>
