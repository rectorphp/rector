<?php

declare (strict_types=1);
namespace Rector\PHPUnit\RobotLoader;

use RectorPrefix20210514\Nette\Loaders\RobotLoader;
final class RobotLoaderFactory
{
    /**
     * @param string[] $directories
     */
    public function createFromDirectories(array $directories) : \RectorPrefix20210514\Nette\Loaders\RobotLoader
    {
        $robotLoader = new \RectorPrefix20210514\Nette\Loaders\RobotLoader();
        $robotLoader->setTempDirectory(\sys_get_temp_dir() . '/tests_add_see_rector_tests');
        $robotLoader->addDirectory(...$directories);
        $robotLoader->acceptFiles = ['*Test.php'];
        $robotLoader->ignoreDirs[] = '*Expected*';
        $robotLoader->ignoreDirs[] = '*Fixture*';
        $robotLoader->ignoreDirs[] = 'templates';
        return $robotLoader;
    }
}
