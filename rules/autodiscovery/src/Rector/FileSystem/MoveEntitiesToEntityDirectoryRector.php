<?php

declare(strict_types=1);

namespace Rector\Autodiscovery\Rector\FileSystem;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Class_\EntityTagValueNode;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\FileSystemRector\Rector\AbstractFileSystemRector;
use Symplify\SmartFileSystem\SmartFileInfo;

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
     * @var string
     * @see https://regex101.com/r/auSMk3/1
     */
    private const ENTITY_PATH_REGEX = '#\bEntity\b#';

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Move entities to Entity namespace', [new CodeSample(
            <<<'CODE_SAMPLE'
// file: app/Controller/Product.php

namespace App\Controller;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Product
{
}
CODE_SAMPLE
            ,
            <<<'CODE_SAMPLE'
// file: app/Entity/Product.php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Product
{
}
CODE_SAMPLE
        )]);
    }

    public function refactor(SmartFileInfo $smartFileInfo): void
    {
        $nodes = $this->parseFileInfoToNodes($smartFileInfo);
        if (! $this->areDoctrineEntityNodes($nodes)) {
            return;
        }

        // is entity in expected directory?
        if (Strings::match($smartFileInfo->getRealPath(), self::ENTITY_PATH_REGEX)) {
            return;
        }

        $movedFile = $this->movedFileWithNodesFactory->create($smartFileInfo, $nodes, 'Entity');
        if ($movedFile === null) {
            return;
        }

        $this->addMovedFile($movedFile);
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

        return $this->hasPhpDocTagValueNode($class, EntityTagValueNode::class);
    }
}
