<?php

declare(strict_types=1);

namespace Rector\Utils\NodeDocumentationGenerator\RobotLoader;

use Nette\Loaders\RobotLoader;

/**
 * @see \Rector\NodeDocumentationGenerator\Tests\RobotLoader\NodeClassProviderTest
 * @see \Rector\Utils\NodeDocumentationGenerator\Tests\RobotLoader\NodeClassProviderTest
 */
final class NodeClassProvider
{
    /**
     * @return string[]
     */
    public function getNodeClasses(): array
    {
        $robotLoader = new RobotLoader();
        $robotLoader->addDirectory(__DIR__ . '/../../../../vendor/nikic/php-parser/lib/PhpParser/Node');
        $robotLoader->setTempDirectory(sys_get_temp_dir() . '/_robotloader_nodes');
        $robotLoader->rebuild();

        $classNames = [];
        foreach (array_keys($robotLoader->getIndexedClasses()) as $className) {
            $classNames[] = (string) $className;
        }

        return $classNames;
    }
}
