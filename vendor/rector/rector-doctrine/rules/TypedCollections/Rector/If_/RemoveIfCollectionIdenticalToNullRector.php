<?php

declare (strict_types=1);
namespace Rector\Doctrine\TypedCollections\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Stmt\If_;
use Rector\Doctrine\TypedCollections\TypeAnalyzer\CollectionTypeDetector;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Doctrine\Tests\TypedCollections\Rector\If_\RemoveIfCollectionIdenticalToNullRector\RemoveIfCollectionIdenticalToNullRectorTest
 */
final class RemoveIfCollectionIdenticalToNullRector extends AbstractRector
{
    /**
     * @readonly
     */
    private CollectionTypeDetector $collectionTypeDetector;
    public function __construct(CollectionTypeDetector $collectionTypeDetector)
    {
        $this->collectionTypeDetector = $collectionTypeDetector;
    }
    public function getNodeTypes() : array
    {
        return [If_::class];
    }
    /**
     * @param If_ $node
     */
    public function refactor(Node $node) : ?If_
    {
        if (!$node->cond instanceof BooleanOr) {
            return null;
        }
        $leftCondition = $node->cond->left;
        if (!$leftCondition instanceof Identical) {
            return null;
        }
        if (!$leftCondition->right instanceof ConstFetch || !$this->isName($leftCondition->right->name, 'null')) {
            return null;
        }
        if (!$this->collectionTypeDetector->isCollectionType($leftCondition->left)) {
            return null;
        }
        $rightCondition = $node->cond->right;
        $node->cond = $rightCondition;
        return $node;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove collection identical to null from if || condition', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\Common\Collections\Collection;

final class SomeClass
{
    private Collection $collection;

    public function someMethod()
    {
        if ($this->collection === null || $this->collection->isEmpty()) {
            return true;
        }

        return false;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\Common\Collections\Collection;

final class SomeClass
{
    private Collection $collection;

    public function someMethod()
    {
        if ($this->collection->isEmpty()) {
            return true;
        }

        return false;
    }
}
CODE_SAMPLE
)]);
    }
}
