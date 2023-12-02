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
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $class = $node;
        $hasChanged = \false;
        $isFinal = $class->isFinal();
        $this->traverseNodesWithCallable($node, function (Node $node) use($class, &$hasChanged, $isFinal) : ?PropertyFetch {
            if (!$node instanceof MethodCall) {
                return null;
            }
            if ($node->isFirstClassCallable()) {
                return null;
            }
            if (!$node->var instanceof Variable) {
                return null;
            }
            if (!$this->nodeNameResolver->isName($node->var, 'this')) {
                return null;
            }
            $methodName = $this->getName($node->name);
            if ($methodName === null) {
                return null;
            }
            $classMethod = $class->getMethod($methodName);
            if (!$classMethod instanceof ClassMethod) {
                return null;
            }
            if (!$classMethod->isPrivate() && !$isFinal) {
                return null;
            }
            $propertyFetch = $this->matchLocalPropertyFetchInGetterMethod($classMethod);
            if (!$propertyFetch instanceof PropertyFetch) {
                return null;
            }
            $hasChanged = \true;
            return $propertyFetch;
        });
        if ($hasChanged) {
            return $node;
        }
        return null;
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
