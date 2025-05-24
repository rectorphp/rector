<?php

declare (strict_types=1);
namespace Rector\Doctrine\TypedCollections\Rector\Expression;

use PhpParser\Node;
use PhpParser\Node\Expr\AssignOp\Coalesce;
use PhpParser\Node\Stmt\Expression;
use PhpParser\NodeVisitorAbstract;
use Rector\Doctrine\TypedCollections\TypeAnalyzer\CollectionTypeDetector;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Doctrine\Tests\TypedCollections\Rector\Expression\RemoveCoalesceAssignOnCollectionRector\RemoveCoalesceAssignOnCollectionRectorTest
 */
final class RemoveCoalesceAssignOnCollectionRector extends AbstractRector
{
    /**
     * @readonly
     */
    private CollectionTypeDetector $collectionTypeDetector;
    public function __construct(CollectionTypeDetector $collectionTypeDetector)
    {
        $this->collectionTypeDetector = $collectionTypeDetector;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove coalesce assign on collection typed property, as it is always assigned in the constructor', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @ORM\Entity
 */
class SomeEntity
{
    private $collection;

    public function run()
    {
        $items = $this->collection ?? new ArrayCollection();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\Common\Collections\Collection;

/**
 * @ORM\Entity
 */
class SomeEntity
{
    private $collection;

    public function run()
    {
        $items = $this->collection;
    }
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes() : array
    {
        return [Expression::class];
    }
    /**
     * @param Expression $node
     */
    public function refactor(Node $node) : ?int
    {
        if (!$node->expr instanceof Coalesce) {
            return null;
        }
        if (!$this->collectionTypeDetector->isCollectionType($node->expr)) {
            return null;
        }
        return NodeVisitorAbstract::REMOVE_NODE;
    }
}
