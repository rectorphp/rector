<?php

declare (strict_types=1);
namespace Rector\Autodiscovery\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Util\StringUtils;
use Rector\Doctrine\PhpDocParser\DoctrineDocBlockResolver;
use Rector\FileSystemRector\ValueObject\AddedFileWithNodes;
use Rector\FileSystemRector\ValueObjectFactory\AddedFileWithNodesFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * Inspiration @see https://github.com/rectorphp/rector/pull/1865/files#diff-0d18e660cdb626958662641b491623f8
 *
 * @see \Rector\Tests\Autodiscovery\Rector\Class_\MoveEntitiesToEntityDirectoryRector\MoveEntitiesToEntityDirectoryRectorTest
 */
final class MoveEntitiesToEntityDirectoryRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var string
     * @see https://regex101.com/r/auSMk3/1
     */
    private const ENTITY_PATH_REGEX = '#\\bEntity\\b#';
    /**
     * @readonly
     * @var \Rector\Doctrine\PhpDocParser\DoctrineDocBlockResolver
     */
    private $doctrineDocBlockResolver;
    /**
     * @readonly
     * @var \Rector\FileSystemRector\ValueObjectFactory\AddedFileWithNodesFactory
     */
    private $addedFileWithNodesFactory;
    public function __construct(\Rector\Doctrine\PhpDocParser\DoctrineDocBlockResolver $doctrineDocBlockResolver, \Rector\FileSystemRector\ValueObjectFactory\AddedFileWithNodesFactory $addedFileWithNodesFactory)
    {
        $this->doctrineDocBlockResolver = $doctrineDocBlockResolver;
        $this->addedFileWithNodesFactory = $addedFileWithNodesFactory;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Move entities to Entity namespace', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
, <<<'CODE_SAMPLE'
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
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->doctrineDocBlockResolver->isDoctrineEntityClass($node)) {
            return null;
        }
        // is entity in expected directory?
        $smartFileInfo = $this->file->getSmartFileInfo();
        if (\Rector\Core\Util\StringUtils::isMatch($smartFileInfo->getRealPath(), self::ENTITY_PATH_REGEX)) {
            return null;
        }
        $addedFileWithNodes = $this->addedFileWithNodesFactory->createWithDesiredGroup($smartFileInfo, $this->file, 'Entity');
        if (!$addedFileWithNodes instanceof \Rector\FileSystemRector\ValueObject\AddedFileWithNodes) {
            return null;
        }
        $this->removedAndAddedFilesCollector->removeFile($smartFileInfo);
        $this->removedAndAddedFilesCollector->addAddedFile($addedFileWithNodes);
        return null;
    }
}
