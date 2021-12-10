<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ThisType;
use PHPStan\Type\TypeWithClassName;
use Rector\Core\NodeAnalyzer\CallAnalyzer;
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\MethodCall\RemoveEmptyMethodCallRector\RemoveEmptyMethodCallRectorTest
 */
final class RemoveEmptyMethodCallRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\AstResolver
     */
    private $reflectionAstResolver;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\CallAnalyzer
     */
    private $callAnalyzer;
    public function __construct(\Rector\Core\PhpParser\AstResolver $reflectionAstResolver, \Rector\Core\NodeAnalyzer\CallAnalyzer $callAnalyzer)
    {
        $this->reflectionAstResolver = $reflectionAstResolver;
        $this->callAnalyzer = $callAnalyzer;
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
        $scope = $this->getScope($node);
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return null;
        }
        $type = $scope->getType($node->var);
        if (!$type instanceof \PHPStan\Type\TypeWithClassName) {
            return null;
        }
        $classLike = $this->reflectionAstResolver->resolveClassFromObjectType($type);
        if (!$classLike instanceof \PhpParser\Node\Stmt\ClassLike) {
            return null;
        }
        if ($this->shouldSkipClassMethod($classLike, $node, $type)) {
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
    private function getScope(\PhpParser\Node\Expr\MethodCall $methodCall) : ?\PHPStan\Analyser\Scope
    {
        if ($this->callAnalyzer->isObjectCall($methodCall->var)) {
            return null;
        }
        $parentArg = $this->betterNodeFinder->findParentType($methodCall, \PhpParser\Node\Arg::class);
        if ($parentArg instanceof \PhpParser\Node\Arg) {
            return null;
        }
        $scope = $methodCall->var->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return null;
        }
        return $scope;
    }
    /**
     * @param \PhpParser\Node\Stmt\Class_|\PhpParser\Node\Stmt\Enum_|\PhpParser\Node\Stmt\Interface_|\PhpParser\Node\Stmt\Trait_ $classLike
     */
    private function shouldSkipClassMethod($classLike, \PhpParser\Node\Expr\MethodCall $methodCall, \PHPStan\Type\TypeWithClassName $typeWithClassName) : bool
    {
        if (!$classLike instanceof \PhpParser\Node\Stmt\Class_) {
            return \true;
        }
        $methodName = $this->getName($methodCall->name);
        if ($methodName === null) {
            return \true;
        }
        $classMethod = $classLike->getMethod($methodName);
        if (!$classMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return \true;
        }
        if ($classMethod->isAbstract()) {
            return \true;
        }
        if ((array) $classMethod->stmts !== []) {
            return \true;
        }
        $class = $this->betterNodeFinder->findParentType($methodCall, \PhpParser\Node\Stmt\Class_::class);
        if (!$class instanceof \PhpParser\Node\Stmt\Class_) {
            return \false;
        }
        if (!$typeWithClassName instanceof \PHPStan\Type\ThisType) {
            return \false;
        }
        if ($class->isFinal()) {
            return \false;
        }
        return !$classMethod->isPrivate();
    }
    /**
     * @return \PhpParser\Node\Expr\ConstFetch|\PhpParser\Node\Expr\MethodCall
     */
    private function processArrowFunction(\PhpParser\Node\Expr\ArrowFunction $arrowFunction, \PhpParser\Node\Expr\MethodCall $methodCall)
    {
        $parentOfParent = $arrowFunction->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if ($parentOfParent instanceof \PhpParser\Node\Stmt\Expression) {
            $this->removeNode($arrowFunction);
            return $methodCall;
        }
        return $this->nodeFactory->createFalse();
    }
}
