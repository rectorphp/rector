<?php

declare(strict_types=1);

namespace Rector\Utils\PHPStanAttributeTypeSyncer\Finder;

use Nette\Loaders\RobotLoader;

final class NodeClassFinder
{
    /**
     * @var string[]
     */
    private const EXCLUDED_CLASSES = ['PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode'];

    /**
     * @return class-string[]
     */
    public function findCurrentPHPDocParserNodeClasses(): array
    {
        return $this->findClassesByNamePatternInDirectories(
            '*Node.php',
            [
                __DIR__ . '/../../../../vendor/phpstan/phpdoc-parser/src/Ast/Type',
                __DIR__ . '/../../../../vendor/phpstan/phpdoc-parser/src/Ast/PhpDoc',
            ]
        );
    }

    /**
     * @param string[] $directories
     * @return class-string[]
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

        $indexedClasses = $robotLoader->getIndexedClasses();

        $classLikes = array_keys($indexedClasses);

        // keep only classes, skip interfaces
        $classes = [];
        foreach ($classLikes as $classLike) {
            if (! class_exists($classLike)) {
                continue;
            }

            $classes[] = $classLike;
        }

        return array_diff($classes, self::EXCLUDED_CLASSES);
    }
}
