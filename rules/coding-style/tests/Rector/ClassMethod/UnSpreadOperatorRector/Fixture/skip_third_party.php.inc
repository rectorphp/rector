<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\UnSpreadOperatorRector\Fixture;

use Nette\Loaders\RobotLoader;

final class SkipThirdParty
{
    public function run(array $directories)
    {
        $robotLoader = new RobotLoader();
        $robotLoader->addDirectory(...$directories);
    }
}

?>