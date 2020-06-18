<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\NewlineBeforeNewAssignSetRector\Fixture;

use Nette\Loaders\RobotLoader;

final class SkipMethodCallPropertyCall
{
    public function run($directories, $name)
    {
        $robotLoader = new RobotLoader();
        foreach ($directories as $directory) {
            $robotLoader->addDirectory($directory);
        }

        $robotLoader->setTempDirectory(sys_get_temp_dir() . '/_rector_finder');
        $robotLoader->acceptFiles = [$name];
        $robotLoader->rebuild();
    }
}
