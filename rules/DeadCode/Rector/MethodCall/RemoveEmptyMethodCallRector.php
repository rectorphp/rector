<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\MethodCall;

use PhpParser\Node;
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
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\ThisType;
use PHPStan\Type\TypeWithClassName;
use Rector\Core\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\Core\NodeAnalyzer\CallAnalyzer;
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Reflection\ReflectionResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\MethodCall\RemoveEmptyMethodCallRector\RemoveEmptyMethodCallRectorTest
 */
final class RemoveEmptyMethodCallRector extends AbstractRector
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
        return [StmtsAwareInterface::class];
    }
    /**
     * @param StmtsAwareInterface $node
     */
    public function refactor(Node $node)
    {
        if ($node->stmts === null) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->stmts as $key => $stmt) {
            if ($stmt instanceof If_) {
                $if = $this->refactorIf($stmt);
                if ($if instanceof If_) {
                    $hasChanged = \true;
                    continue;
                }
            }
            if (!$stmt instanceof Expression) {
                continue;
            }
            if ($stmt->expr instanceof Assign && $stmt->expr->expr instanceof MethodCall) {
                $methodCall = $stmt->expr->expr;
                if (!$this->shouldRemoveMethodCall($methodCall)) {
                    continue;
                }
                $stmt->expr->expr = $this->nodeFactory->createFalse();
                $hasChanged = \true;
                continue;
            }
            if ($stmt->expr instanceof MethodCall) {
                $methodCall = $stmt->expr;
                if (!$this->shouldRemoveMethodCall($methodCall)) {
                    continue;
                }
                unset($node->stmts[$key]);
                $hasChanged = \true;
            }
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function resolveClassLike(MethodCall $methodCall) : ?ClassLike
    {
        $classReflection = $this->reflectionResolver->resolveClassReflectionSourceObject($methodCall);
        if (!$classReflection instanceof ClassReflection) {
            return null;
        }
        return $this->reflectionAstResolver->resolveClassFromName($classReflection->getName());
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
    private function shouldRemoveMethodCall(MethodCall $methodCall) : bool
    {
        if ($this->callAnalyzer->isObjectCall($methodCall->var)) {
            return \false;
        }
        $callerType = $this->getType($methodCall->var);
        if (!$callerType instanceof TypeWithClassName) {
            return \false;
        }
        $classLike = $this->resolveClassLike($methodCall);
        if (!$classLike instanceof ClassLike) {
            return \false;
        }
        /** @var Class_|Trait_|Interface_|Enum_ $classLike */
        return !$this->shouldSkipClassMethod($classLike, $methodCall, $callerType);
    }
    /**
     * If->cond cannot removed,
     * it has to be replaced with false, see https://3v4l.org/U9S9i
     */
    private function refactorIf(If_ $if) : ?If_
    {
        if (!$if->cond instanceof MethodCall) {
            return null;
        }
        if (!$this->shouldRemoveMethodCall($if->cond)) {
            return null;
        }
        $if->cond = $this->nodeFactory->createFalse();
        return $if;
    }
}
