<?php declare(strict_types=1);

namespace Rector\Autodiscovery\Rector\FileSystem;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Autodiscovery\FileMover\FileMover;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Class_\EntityTagValueNode;
use Rector\FileSystemRector\Rector\AbstractFileSystemRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Symplify\PackageBuilder\FileSystem\SmartFileInfo;

/**
 * @sponsor Thanks https://spaceflow.io/ for sponsoring this rule - visit them on https://github.com/SpaceFlow-app
 *
 * Inspiration @see https://github.com/rectorphp/rector/pull/1865/files#diff-0d18e660cdb626958662641b491623f8
 *
 * @see \Rector\Autodiscovery\Tests\Rector\FileSystem\MoveEntitiesToEntityDirectoryRector\MoveEntitiesToEntityDirectoryRectorTest
 */
final class MoveEntitiesToEntityDirectoryRector extends AbstractFileSystemRector
{
    /**
     * @var FileMover
     */
    private $fileMover;

    public function __construct(FileMover $fileMover)
    {
        $this->fileMover = $fileMover;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Move entities to Entity namespace', [new CodeSample(
            <<<'PHP'
// file: app/Controller/Product.php

namespace App\Controller;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Product
{
}
PHP
            ,
            <<<'PHP'
// file: app/Entity/Product.php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Product
{
}
PHP
        )]);
    }

    public function refactor(SmartFileInfo $smartFileInfo): void
    {
        $nodes = $this->parseFileInfoToNodes($smartFileInfo);
        if (! $this->areDoctrineEntityNodes($nodes)) {
            return;
        }

        // is entity in expected directory?
        if (Strings::match($smartFileInfo->getRealPath(), '#\bEntity\b#')) {
            return;
        }

        $nodesWithFileDestination = $this->fileMover->createMovedNodesAndFilePath($smartFileInfo, $nodes, 'Entity');

        // nothing to move
        if ($nodesWithFileDestination === null) {
            return;
        }

        $this->removeFile($smartFileInfo);
        $this->printNodesWithFileDestination($nodesWithFileDestination);
    }

    /**
     * @param Node[] $nodes
     */
    private function areDoctrineEntityNodes(array $nodes): bool
    {
        /** @var Class_|null $class */
        $class = $this->betterNodeFinder->findFirstInstanceOf($nodes, Class_::class);
        if ($class === null) {
            return false;
        }

        $phpDocInfo = $this->getPhpDocInfo($class);
        if ($phpDocInfo === null) {
            return false;
        }

        return (bool) $phpDocInfo->getByType(EntityTagValueNode::class);
    }
}
