<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ThisType;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Reflection\ClassReflectionToAstResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\MethodCall\RemoveEmptyMethodCallRector\RemoveEmptyMethodCallRectorTest
 */
final class RemoveEmptyMethodCallRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var ClassReflectionToAstResolver
     */
    private $classReflectionToAstResolver;
    public function __construct(\Rector\Core\Reflection\ClassReflectionToAstResolver $classReflectionToAstResolver)
    {
        $this->classReflectionToAstResolver = $classReflectionToAstResolver;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove empty method call', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function callThis()
    {
    }
}

$some = new SomeClass();
$some->callThis();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function callThis()
    {
    }
}

$some = new SomeClass();
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $scope = $node->var->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return null;
        }
        $type = $scope->getType($node->var);
        if ($type instanceof \PHPStan\Type\ThisType) {
            $type = $type->getStaticObjectType();
        }
        if (!$type instanceof \PHPStan\Type\ObjectType) {
            return null;
        }
        $class = $this->classReflectionToAstResolver->getClassFromObjectType($type);
        if ($this->shouldSkipClassMethod($class, $node)) {
            return null;
        }
        // if->cond cannot removed, it has to be replaced with false, see https://3v4l.org/U9S9i
        $parent = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if ($parent instanceof \PhpParser\Node\Stmt\If_ && $parent->cond === $node) {
            return $this->nodeFactory->createFalse();
        }
        if ($parent instanceof \PhpParser\Node\Expr\Assign) {
            return $this->nodeFactory->createFalse();
        }
        if ($parent instanceof \PhpParser\Node\Expr\ArrowFunction && $this->nodeComparator->areNodesEqual($parent->expr, $node)) {
            return $this->processArrowFunction($parent, $node);
        }
        $this->removeNode($node);
        return $node;
    }
    private function shouldSkipClassMethod(?\PhpParser\Node\Stmt\Class_ $class, \PhpParser\Node\Expr\MethodCall $methodCall) : bool
    {
        if (!$class instanceof \PhpParser\Node\Stmt\Class_) {
            return \true;
        }
        $methodName = $this->getName($methodCall->name);
        if ($methodName === null) {
            return \true;
        }
        $classMethod = $class->getMethod($methodName);
        if (!$classMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return \true;
        }
        if ($classMethod->isAbstract()) {
            return \true;
        }
        return \count((array) $classMethod->stmts) !== 0;
    }
    private function processArrowFunction(\PhpParser\Node\Expr\ArrowFunction $arrowFunction, \PhpParser\Node\Expr\MethodCall $methodCall) : \PhpParser\Node\Expr
    {
        $parentOfParent = $arrowFunction->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if ($parentOfParent instanceof \PhpParser\Node\Stmt\Expression) {
            $this->removeNode($arrowFunction);
            return $methodCall;
        }
        return $this->nodeFactory->createFalse();
    }
}
