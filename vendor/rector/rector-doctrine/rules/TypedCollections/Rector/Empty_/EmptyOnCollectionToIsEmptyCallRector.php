<?php

declare (strict_types=1);
namespace Rector\Doctrine\TypedCollections\Rector\Empty_;

use PhpParser\Node;
use PhpParser\Node\Expr\Empty_;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Type\ObjectType;
use Rector\Doctrine\Enum\DoctrineClass;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Doctrine\Tests\TypedCollections\Rector\FuncCall\InArrayOnCollectionToContainsCallRector\InArrayOnCollectionToContainsCallRectorTest
 */
final class EmptyOnCollectionToIsEmptyCallRector extends AbstractRector
{
    public function getNodeTypes() : array
    {
        return [Empty_::class];
    }
    /**
     * @param Empty_ $node
     */
    public function refactor(Node $node) : ?MethodCall
    {
        $emptyExprType = $this->getType($node->expr);
        if (!$emptyExprType instanceof ObjectType) {
            return null;
        }
        if (!$emptyExprType->isInstanceOf(DoctrineClass::COLLECTION)->yes()) {
            return null;
        }
        return new MethodCall($node->expr, 'isEmpty');
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Convert empty() on a Collection to ->isEmpty() call', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @var Collection<int, string>
     */
    private $collection;

    public function someMethod()
    {
        if (empty($this->collection)) {
            // ...
        }
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'

class SomeClass
{
    /**
     * @var Collection<int, string>
     */
    private $collection;

    public function someMethod()
    {
        if ($this->collection->isEmpty()) {
            // ...
        }
    }
}
CODE_SAMPLE
)]);
    }
}
