<?php

declare(strict_types=1);

namespace Rector\Core\FileSystem;

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

        $classes = array_keys($robotLoader->getIndexedClasses());

        sort($classes);

        return $classes;
    }
}
