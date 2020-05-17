<?php

declare(strict_types=1);

namespace Rector\RectorGenerator\Node;

use Nette\Loaders\RobotLoader;

final class NodeClassProvider
{
    /**
     * @return string[]
     */
    public function getNodeClasses(): array
    {
        $robotLoader = new RobotLoader();
        $robotLoader->addDirectory($this->getDirectoryPath());
        $robotLoader->setTempDirectory(sys_get_temp_dir() . '/_robotloader_nodes');
        $robotLoader->rebuild();

        return array_keys($robotLoader->getIndexedClasses());
    }

    private function getDirectoryPath(): string
    {
        $pathUsedAsStandaloneProject = __DIR__ . '/../../../../vendor/nikic/php-parser/lib/PhpParser/Node';
        $pathUsedAsDependency = __DIR__ . '/../../../../../../nikic/php-parser/lib/PhpParser/Node';

        return ! file_exists($pathUsedAsStandaloneProject)
            ? $pathUsedAsDependency
            : $pathUsedAsStandaloneProject;
    }
}
