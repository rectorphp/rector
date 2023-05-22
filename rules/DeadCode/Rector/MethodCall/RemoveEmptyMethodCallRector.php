<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
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
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\ThisType;
use PHPStan\Type\TypeWithClassName;
use Rector\Core\NodeAnalyzer\CallAnalyzer;
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\Rector\AbstractScopeAwareRector;
use Rector\Core\Reflection\ReflectionResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\MethodCall\RemoveEmptyMethodCallRector\RemoveEmptyMethodCallRectorTest
 */
final class RemoveEmptyMethodCallRector extends AbstractScopeAwareRector
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
    /**
     * @readonly
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    public function __construct(AstResolver $reflectionAstResolver, CallAnalyzer $callAnalyzer, ReflectionResolver $reflectionResolver)
    {
        $this->reflectionAstResolver = $reflectionAstResolver;
        $this->callAnalyzer = $callAnalyzer;
        $this->reflectionResolver = $reflectionResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove empty method call', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [Expression::class, If_::class, Assign::class];
    }
    /**
     * @param Expression|If_|Assign $node
     */
    public function refactorWithScope(Node $node, Scope $scope)
    {
        if ($node instanceof If_) {
            return $this->refactorIf($node, $scope);
        }
        if ($node instanceof Expression) {
            $this->refactorExpression($node, $scope);
            return null;
        }
        if (!$node->expr instanceof MethodCall) {
            return null;
        }
        if (!$this->shouldRemoveMethodCall($node->expr, $scope)) {
            return null;
        }
        $node->expr = $this->nodeFactory->createFalse();
        return $node;
    }
    private function resolveClassLike(MethodCall $methodCall) : ?ClassLike
    {
        $classReflection = $this->reflectionResolver->resolveClassReflectionSourceObject($methodCall);
        if (!$classReflection instanceof ClassReflection) {
            return null;
        }
        return $this->reflectionAstResolver->resolveClassFromName($classReflection->getName());
    }
    private function shouldSkipMethodCall(MethodCall $methodCall) : bool
    {
        if ($this->callAnalyzer->isObjectCall($methodCall->var)) {
            return \true;
        }
        $parentArg = $this->betterNodeFinder->findParentType($methodCall, Arg::class);
        return $parentArg instanceof Arg;
    }
    /**
     * @param \PhpParser\Node\Stmt\Class_|\PhpParser\Node\Stmt\Trait_|\PhpParser\Node\Stmt\Interface_|\PhpParser\Node\Stmt\Enum_ $classLike
     */
    private function shouldSkipClassMethod($classLike, MethodCall $methodCall, TypeWithClassName $typeWithClassName) : bool
    {
        if (!$classLike instanceof Class_) {
            return \true;
        }
        $methodName = $this->getName($methodCall->name);
        if ($methodName === null) {
            return \true;
        }
        $classMethod = $classLike->getMethod($methodName);
        if (!$classMethod instanceof ClassMethod) {
            return \true;
        }
        if ($classMethod->isAbstract()) {
            return \true;
        }
        if ((array) $classMethod->stmts !== []) {
            return \true;
        }
        $class = $this->betterNodeFinder->findParentType($methodCall, Class_::class);
        if (!$class instanceof Class_) {
            return \false;
        }
        if (!$typeWithClassName instanceof ThisType) {
            return \false;
        }
        if ($class->isFinal()) {
            return \false;
        }
        return !$classMethod->isPrivate();
    }
    private function shouldRemoveMethodCall(MethodCall $methodCall, Scope $scope) : bool
    {
        if ($this->shouldSkipMethodCall($methodCall)) {
            return \false;
        }
        $type = $scope->getType($methodCall->var);
        if (!$type instanceof TypeWithClassName) {
            return \false;
        }
        $classLike = $this->resolveClassLike($methodCall);
        if (!$classLike instanceof ClassLike) {
            return \false;
        }
        /** @var Class_|Trait_|Interface_|Enum_ $classLike */
        return !$this->shouldSkipClassMethod($classLike, $methodCall, $type);
    }
    /**
     * If->cond cannot removed,
     * it has to be replaced with false, see https://3v4l.org/U9S9i
     */
    private function refactorIf(If_ $if, Scope $scope) : ?If_
    {
        if (!$if->cond instanceof MethodCall) {
            return null;
        }
        if (!$this->shouldRemoveMethodCall($if->cond, $scope)) {
            return null;
        }
        $if->cond = $this->nodeFactory->createFalse();
        return $if;
    }
    private function refactorExpression(Expression $expression, Scope $scope) : void
    {
        if (!$expression->expr instanceof MethodCall) {
            return;
        }
        $methodCall = $expression->expr;
        if (!$this->shouldRemoveMethodCall($methodCall, $scope)) {
            return;
        }
        $this->removeNode($expression);
    }
}
