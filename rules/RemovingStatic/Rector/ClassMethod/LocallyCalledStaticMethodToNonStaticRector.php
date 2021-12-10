<?php

declare (strict_types=1);
namespace Rector\RemovingStatic\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Privatization\NodeManipulator\VisibilityManipulator;
use Rector\Privatization\VisibilityGuard\ClassMethodVisibilityGuard;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\RemovingStatic\Rector\ClassMethod\LocallyCalledStaticMethodToNonStaticRector\LocallyCalledStaticMethodToNonStaticRectorTest
 */
final class LocallyCalledStaticMethodToNonStaticRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Privatization\VisibilityGuard\ClassMethodVisibilityGuard
     */
    private $classMethodVisibilityGuard;
    /**
     * @readonly
     * @var \Rector\Privatization\NodeManipulator\VisibilityManipulator
     */
    private $visibilityManipulator;
    public function __construct(\Rector\Privatization\VisibilityGuard\ClassMethodVisibilityGuard $classMethodVisibilityGuard, \Rector\Privatization\NodeManipulator\VisibilityManipulator $visibilityManipulator)
    {
        $this->classMethodVisibilityGuard = $classMethodVisibilityGuard;
        $this->visibilityManipulator = $visibilityManipulator;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change static method and local-only calls to non-static', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        self::someStatic();
    }

    private static function someStatic()
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $this->someStatic();
    }

    private function someStatic()
    {
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
        return [\PhpParser\Node\Stmt\ClassMethod::class, \PhpParser\Node\Expr\StaticCall::class];
    }
    /**
     * @param ClassMethod|StaticCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($node instanceof \PhpParser\Node\Stmt\ClassMethod) {
            if (!$node->isPrivate()) {
                return null;
            }
            return $this->refactorClassMethod($node);
        }
        return $this->refactorStaticCall($node);
    }
    private function refactorClassMethod(\PhpParser\Node\Stmt\ClassMethod $classMethod) : ?\PhpParser\Node\Stmt\ClassMethod
    {
        if (!$classMethod->isStatic()) {
            return null;
        }
        $scope = $classMethod->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof \PHPStan\Reflection\ClassReflection) {
            return null;
        }
        if ($this->classMethodVisibilityGuard->isClassMethodVisibilityGuardedByParent($classMethod, $classReflection)) {
            return null;
        }
        // change static calls to non-static ones, but only if in non-static method!!!
        $this->visibilityManipulator->makeNonStatic($classMethod);
        return $classMethod;
    }
    private function refactorStaticCall(\PhpParser\Node\Expr\StaticCall $staticCall) : ?\PhpParser\Node\Expr\MethodCall
    {
        $classLike = $this->betterNodeFinder->findParentType($staticCall, \PhpParser\Node\Stmt\ClassLike::class);
        if (!$classLike instanceof \PhpParser\Node\Stmt\ClassLike) {
            return null;
        }
        /** @var ClassMethod[] $classMethods */
        $classMethods = $this->betterNodeFinder->findInstanceOf($classLike, \PhpParser\Node\Stmt\ClassMethod::class);
        foreach ($classMethods as $classMethod) {
            if (!$this->isClassMethodMatchingStaticCall($classMethod, $staticCall)) {
                continue;
            }
            if ($this->isInStaticClassMethod($staticCall)) {
                continue;
            }
            $thisVariable = new \PhpParser\Node\Expr\Variable('this');
            return new \PhpParser\Node\Expr\MethodCall($thisVariable, $staticCall->name, $staticCall->args);
        }
        return null;
    }
    private function isInStaticClassMethod(\PhpParser\Node\Expr\StaticCall $staticCall) : bool
    {
        $locationClassMethod = $this->betterNodeFinder->findParentType($staticCall, \PhpParser\Node\Stmt\ClassMethod::class);
        if (!$locationClassMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return \false;
        }
        return $locationClassMethod->isStatic();
    }
    private function isClassMethodMatchingStaticCall(\PhpParser\Node\Stmt\ClassMethod $classMethod, \PhpParser\Node\Expr\StaticCall $staticCall) : bool
    {
        $classLike = $this->betterNodeFinder->findParentType($classMethod, \PhpParser\Node\Stmt\ClassLike::class);
        if (!$classLike instanceof \PhpParser\Node\Stmt\ClassLike) {
            return \false;
        }
        $className = (string) $this->nodeNameResolver->getName($classLike);
        $objectType = new \PHPStan\Type\ObjectType($className);
        $callerType = $this->nodeTypeResolver->getType($staticCall->class);
        return $objectType->equals($callerType);
    }
}
