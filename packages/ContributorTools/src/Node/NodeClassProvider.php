<?php declare(strict_types=1);

namespace Rector\ContributorTools\Node;

use Nette\Loaders\RobotLoader;

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

        return array_keys($robotLoader->getIndexedClasses());
    }
}
