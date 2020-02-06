<?php

declare(strict_types=1);

namespace Rector\FileSystem;

use Nette\Loaders\RobotLoader;

final class ClassFinder
{
    /**
     * @param string[] $source
     * @return string[]
     */
    public function findClassesInDirectories(array $source): array
    {
        $robotLoader = new RobotLoader();
        foreach ($source as $singleSource) {
            $robotLoader->addDirectory($singleSource);
        }

        $robotLoader->setTempDirectory(sys_get_temp_dir() . '/_class_finder');
        $robotLoader->rebuild();

        return array_keys($robotLoader->getIndexedClasses());
    }
}
