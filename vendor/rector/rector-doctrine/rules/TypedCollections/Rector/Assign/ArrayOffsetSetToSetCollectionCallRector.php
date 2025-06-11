<?php

declare (strict_types=1);
namespace Rector\Doctrine\TypedCollections\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use Rector\Doctrine\TypedCollections\TypeAnalyzer\CollectionTypeDetector;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Doctrine\Tests\TypedCollections\Rector\Assign\ArrayOffsetSetToSetCollectionCallRector\ArrayOffsetSetToSetCollectionCallRectorTest
 */
final class ArrayOffsetSetToSetCollectionCallRector extends AbstractRector
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
        return new RuleDefinition('Change dim assign on a Collection to clear ->set() call', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\Common\Collections\Collection;

final class SomeClass
{
    /**
     * @var Collection<int, string>
     */
    public $items;

    public function setItem()
    {
        $this->items['key'] = 'value';
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\Common\Collections\Collection;

final class SomeClass
{
    /**
     * @var Collection<int, string>
     */
    public $items;

    public function setItem()
    {
        $this->items->set('key', 'value');
    }
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes() : array
    {
        return [Assign::class];
    }
    /**
     * @param Assign $node
     */
    public function refactor(Node $node) : ?MethodCall
    {
        if (!$node->var instanceof ArrayDimFetch) {
            return null;
        }
        $arrayDimFetch = $node->var;
        if (!$arrayDimFetch->dim instanceof Expr) {
            return null;
        }
        $assignedExpr = $arrayDimFetch->var;
        if (!$this->collectionTypeDetector->isCollectionType($assignedExpr)) {
            return null;
        }
        $args = [new Arg($arrayDimFetch->dim), new Arg($node->expr)];
        return new MethodCall($assignedExpr, 'set', $args);
    }
}
