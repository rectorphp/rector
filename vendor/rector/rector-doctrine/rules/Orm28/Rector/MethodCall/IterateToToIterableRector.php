<?php

declare (strict_types=1);
namespace Rector\Doctrine\Orm28\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Foreach_;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/doctrine/orm/pull/7885
 * @changelog https://github.com/doctrine/orm/pull/8293
 *
 * @see \Rector\Doctrine\Tests\Orm28\Rector\MethodCall\IterateToToIterableRector\IterateToToIterableRectorTest
 */
final class IterateToToIterableRector extends AbstractRector
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [MethodCall::class, ClassMethod::class, Foreach_::class];
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change iterate() => toIterable()', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Internal\Hydration\IterableResult;

class SomeRepository extends EntityRepository
{
    public function run(): IterateResult
    {
        /** @var \Doctrine\ORM\AbstractQuery $query */
        $query = $this->getEntityManager()->select('e')->from('entity')->getQuery();

        return $query->iterate();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Internal\Hydration\IterableResult;

class SomeRepository extends EntityRepository
{
    public function run(): iterable
    {
        /** @var \Doctrine\ORM\AbstractQuery $query */
        $query = $this->getEntityManager()->select('e')->from('entity')->getQuery();

        return $query->toIterable();
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @param ClassMethod|MethodCall|Foreach_ $node
     * @return \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Foreach_|null
     */
    public function refactor(Node $node)
    {
        if ($node instanceof ClassMethod) {
            return $this->refactorClassMethod($node);
        }
        if ($node instanceof Foreach_) {
            return $this->refactorForeach($node);
        }
        $varType = $this->nodeTypeResolver->getType($node->var);
        if (!$varType instanceof ObjectType) {
            return null;
        }
        if (!$varType->isInstanceOf('Doctrine\\ORM\\AbstractQuery')->yes()) {
            return null;
        }
        // Change iterate() method calls to toIterable()
        if (!$this->isName($node->name, 'iterate')) {
            return null;
        }
        $node->name = new Identifier('toIterable');
        return $node;
    }
    private function refactorClassMethod(ClassMethod $classMethod) : ?ClassMethod
    {
        if (!$classMethod->returnType instanceof Node) {
            return null;
        }
        if (!$this->isName($classMethod->returnType, 'Doctrine\\ORM\\Internal\\Hydration\\IterableResult')) {
            return null;
        }
        $classMethod->returnType = new Name('iterable');
        return $classMethod;
    }
    private function refactorForeach(Foreach_ $foreach) : ?Foreach_
    {
        $foreachedExprType = $this->getType($foreach->expr);
        if (!$foreachedExprType instanceof ObjectType) {
            return null;
        }
        if (!$foreachedExprType->isInstanceOf('Doctrine\\ORM\\Internal\\Hydration\\IterableResult')->yes()) {
            return null;
        }
        $itemName = $this->getName($foreach->valueVar);
        if (!\is_string($itemName)) {
            return null;
        }
        $this->traverseNodesWithCallable($foreach->stmts, function (Node $node) use($itemName) : ?Expr {
            // update dim fetched reference to direct ones
            if (!$node instanceof ArrayDimFetch) {
                return null;
            }
            if (!$node->var instanceof Expr) {
                return null;
            }
            if (!$this->isName($node->var, $itemName)) {
                return null;
            }
            return $node->var;
        });
        return $foreach;
    }
}
