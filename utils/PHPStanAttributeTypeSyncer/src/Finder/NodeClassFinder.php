<?php

declare(strict_types=1);

namespace Rector\Utils\PHPStanAttributeTypeSyncer\Finder;

use Nette\Loaders\RobotLoader;

final class NodeClassFinder
{
    /**
     * @return string[]
     */
    public function findCurrentPHPDocParserNodeClasses(): array
    {
        return $this->findClassesByNamePatternInDirectories(
            '*Node.php',
            [
                // @todo not sure if needed
                //                __DIR__ . '/../../../../vendor/phpstan/phpdoc-parser/src/Ast/Type',
                __DIR__ . '/../../../../vendor/phpstan/phpdoc-parser/src/Ast/PhpDoc',
            ]
        );
    }

    /**
     * @param string[] $directories
     * @return string[]
     */
    private function findClassesByNamePatternInDirectories(string $namePattern, array $directories): array
    {
        $robotLoader = new RobotLoader();
        foreach ($directories as $directory) {
            $robotLoader->addDirectory($directory);
        }

        $robotLoader->setTempDirectory(sys_get_temp_dir() . '/_phpdoc_parser_ast');
        $robotLoader->acceptFiles = [$namePattern];
        $robotLoader->rebuild();

        $classLikesToPaths = $robotLoader->getIndexedClasses();

        $classLikes = array_keys($classLikesToPaths);
        $excludedClasses = ['PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode'];

        // keep only classes, skip interfaces
        $classes = [];
        foreach ($classLikes as $classLike) {
            if (! class_exists($classLike)) {
                continue;
            }

            $classes[] = $classLike;
        }

        return array_diff($classes, $excludedClasses);
    }
}
