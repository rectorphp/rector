<?php

declare (strict_types=1);
namespace Rector\Privatization\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Privatization\Rector\MethodCall\PrivatizeLocalGetterToPropertyRector\PrivatizeLocalGetterToPropertyRectorTest
 */
final class PrivatizeLocalGetterToPropertyRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Privatize getter of local property to property', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    private $some;

    public function run()
    {
        return $this->getSome() + 5;
    }

    private function getSome()
    {
        return $this->some;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    private $some;

    public function run()
    {
        return $this->some + 5;
    }

    private function getSome()
    {
        return $this->some;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$node->var instanceof Variable) {
            return null;
        }
        if (!$this->nodeNameResolver->isName($node->var, 'this')) {
            return null;
        }
        $classLike = $this->betterNodeFinder->findParentType($node, Class_::class);
        if (!$classLike instanceof Class_) {
            return null;
        }
        $methodName = $this->getName($node->name);
        if ($methodName === null) {
            return null;
        }
        $classMethod = $classLike->getMethod($methodName);
        if (!$classMethod instanceof ClassMethod) {
            return null;
        }
        return $this->matchLocalPropertyFetchInGetterMethod($classMethod);
    }
    private function matchLocalPropertyFetchInGetterMethod(ClassMethod $classMethod) : ?PropertyFetch
    {
        $stmts = (array) $classMethod->stmts;
        if (\count($stmts) !== 1) {
            return null;
        }
        $onlyStmt = $stmts[0] ?? null;
        if (!$onlyStmt instanceof Return_) {
            return null;
        }
        $returnedExpr = $onlyStmt->expr;
        if (!$returnedExpr instanceof PropertyFetch) {
            return null;
        }
        return $returnedExpr;
    }
}
