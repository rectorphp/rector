<?php

declare(strict_types=1);

namespace Rector\Autodiscovery\Rector\FileNode;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\PhpParser\Node\CustomNode\FileNode;
use Rector\Core\Rector\AbstractRector;
use Rector\Doctrine\PhpDocParser\DoctrineDocBlockResolver;
use Rector\FileSystemRector\ValueObject\MovedFileWithNodes;
use Rector\FileSystemRector\ValueObjectFactory\MovedFileWithNodesFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @sponsor Thanks https://spaceflow.io/ for sponsoring this rule - visit them on https://github.com/SpaceFlow-app
 *
 * Inspiration @see https://github.com/rectorphp/rector/pull/1865/files#diff-0d18e660cdb626958662641b491623f8
 *
 * @see \Rector\Autodiscovery\Tests\Rector\FileNode\MoveEntitiesToEntityDirectoryRector\MoveEntitiesToEntityDirectoryRectorTest
 */
final class MoveEntitiesToEntityDirectoryRector extends AbstractRector
{
    /**
     * @var string
     * @see https://regex101.com/r/auSMk3/1
     */
    private const ENTITY_PATH_REGEX = '#\bEntity\b#';

    /**
     * @var DoctrineDocBlockResolver
     */
    private $doctrineDocBlockResolver;

    /**
     * @var MovedFileWithNodesFactory
     */
    private $movedFileWithNodesFactory;

    public function __construct(
        DoctrineDocBlockResolver $doctrineDocBlockResolver,
        MovedFileWithNodesFactory $movedFileWithNodesFactory
    ) {
        $this->doctrineDocBlockResolver = $doctrineDocBlockResolver;
        $this->movedFileWithNodesFactory = $movedFileWithNodesFactory;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Move entities to Entity namespace', [
            new CodeSample(
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
        ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [FileNode::class];
    }

    /**
     * @param FileNode $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isDoctrineEntityFileNode($node)) {
            return null;
        }

        // is entity in expected directory?
        $smartFileInfo = $node->getFileInfo();
        if (Strings::match($smartFileInfo->getRealPath(), self::ENTITY_PATH_REGEX)) {
            return null;
        }

        $movedFileWithNodes = $this->movedFileWithNodesFactory->createWithDesiredGroup(
            $smartFileInfo,
            $node->stmts,
            'Entity'
        );
        if (! $movedFileWithNodes instanceof MovedFileWithNodes) {
            return null;
        }

        $this->removedAndAddedFilesCollector->addMovedFile($movedFileWithNodes);

        return null;
    }

    private function isDoctrineEntityFileNode(FileNode $fileNode): bool
    {
        $class = $this->betterNodeFinder->findFirstInstanceOf($fileNode->stmts, Class_::class);
        if (! $class instanceof Class_) {
            return false;
        }

        return $this->doctrineDocBlockResolver->isDoctrineEntityClass($class);
    }
}
