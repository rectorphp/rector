<?php

declare(strict_types=1);

namespace Rector\Utils\PHPStanStaticTypeMapperChecker\Finder;

use Nette\Loaders\RobotLoader;
use Nette\Utils\Strings;

final class PHPStanTypeClassFinder
{
    /**
     * @return string[]
     */
    public function find(): array
    {
        $robotLoader = new RobotLoader();
        $robotLoader->addDirectory($this->getPHPStanPharSrcTypeDirectoryPath());

        $robotLoader->setTempDirectory(sys_get_temp_dir() . '/_phpstan_types');
        $robotLoader->acceptFiles = ['*Type.php'];
        $robotLoader->rebuild();

        $classLikesToFilePaths = $robotLoader->getIndexedClasses();
        $classLikes = array_keys($classLikesToFilePaths);

        return $this->filterClassesOnly($classLikes);
    }

    /**
     * @see https://github.com/dg/nette-robot-loader/blob/593c0e40e511c0b0700610a6a3964a210219139f/tests/Loaders/RobotLoader.phar.phpt#L33
     */
    private function getPHPStanPharSrcTypeDirectoryPath(): string
    {
        $phpstanPharRealpath = realpath(__DIR__ . '/../../../../vendor/phpstan/phpstan/phpstan.phar');

        return 'phar://' . $phpstanPharRealpath . '/src/Type';
    }

    /**
     * @param class-string[] $classLikes
     * @return class-string[]
     */
    private function filterClassesOnly(array $classLikes): array
    {
        $classes = [];
        foreach ($classLikes as $classLike) {
            if (! class_exists($classLike)) {
                continue;
            }

            if (Strings::match($classLike, '#\\\\Accessory\\\\#')) {
                continue;
            }

            $classes[] = $classLike;
        }

        return $classes;
    }
}
