<?php

declare (strict_types=1);
namespace Rector\Doctrine\TypedCollections\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Stmt\If_;
use Rector\Doctrine\TypedCollections\TypeAnalyzer\CollectionTypeDetector;
use Rector\PhpParser\Node\Value\ValueResolver;
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
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    public function __construct(CollectionTypeDetector $collectionTypeDetector, ValueResolver $valueResolver)
    {
        $this->collectionTypeDetector = $collectionTypeDetector;
        $this->valueResolver = $valueResolver;
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
        if ($node->cond instanceof BooleanOr) {
            $changedCond = $this->refactorBooleanOr($node->cond);
            if ($changedCond instanceof Expr) {
                $node->cond = $changedCond;
                return $node;
            }
        }
        if ($node->cond instanceof BooleanAnd) {
            $changedCond = $this->refactorBooleanAnd($node->cond);
            if ($changedCond instanceof Expr) {
                $node->cond = $changedCond;
                return $node;
            }
        }
        return null;
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
    private function refactorBooleanOr(BooleanOr $booleanOr) : ?Expr
    {
        $leftCondition = $booleanOr->left;
        if (!$leftCondition instanceof Identical) {
            return null;
        }
        if (!$this->valueResolver->isNull($leftCondition->right)) {
            return null;
        }
        if (!$this->collectionTypeDetector->isCollectionType($leftCondition->left)) {
            return null;
        }
        return $booleanOr->right;
    }
    private function refactorBooleanAnd(BooleanAnd $booleanAnd) : ?Expr
    {
        $leftCondition = $booleanAnd->left;
        if (!$leftCondition instanceof NotIdentical) {
            return null;
        }
        if (!$this->valueResolver->isNull($leftCondition->right)) {
            return null;
        }
        if (!$this->collectionTypeDetector->isCollectionType($leftCondition->left)) {
            return null;
        }
        return $booleanAnd->right;
    }
}
