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
use Rector\Core\Reflection\ReflectionResolver;
use Rector\Privatization\NodeManipulator\VisibilityManipulator;
use Rector\Privatization\VisibilityGuard\ClassMethodVisibilityGuard;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\RemovingStatic\Rector\ClassMethod\LocallyCalledStaticMethodToNonStaticRector\LocallyCalledStaticMethodToNonStaticRectorTest
 */
final class LocallyCalledStaticMethodToNonStaticRector extends AbstractRector
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
    /**
     * @readonly
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    public function __construct(ClassMethodVisibilityGuard $classMethodVisibilityGuard, VisibilityManipulator $visibilityManipulator, ReflectionResolver $reflectionResolver)
    {
        $this->classMethodVisibilityGuard = $classMethodVisibilityGuard;
        $this->visibilityManipulator = $visibilityManipulator;
        $this->reflectionResolver = $reflectionResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change static method and local-only calls to non-static', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [ClassMethod::class, StaticCall::class];
    }
    /**
     * @param ClassMethod|StaticCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node instanceof ClassMethod) {
            if (!$node->isPrivate()) {
                return null;
            }
            return $this->refactorClassMethod($node);
        }
        return $this->refactorStaticCall($node);
    }
    private function refactorClassMethod(ClassMethod $classMethod) : ?ClassMethod
    {
        if (!$classMethod->isStatic()) {
            return null;
        }
        $classReflection = $this->reflectionResolver->resolveClassReflection($classMethod);
        if (!$classReflection instanceof ClassReflection) {
            return null;
        }
        if ($this->classMethodVisibilityGuard->isClassMethodVisibilityGuardedByParent($classMethod, $classReflection)) {
            return null;
        }
        // change static calls to non-static ones, but only if in non-static method!!!
        $this->visibilityManipulator->makeNonStatic($classMethod);
        return $classMethod;
    }
    private function refactorStaticCall(StaticCall $staticCall) : ?MethodCall
    {
        $classLike = $this->betterNodeFinder->findParentType($staticCall, ClassLike::class);
        if (!$classLike instanceof ClassLike) {
            return null;
        }
        /** @var ClassMethod[] $classMethods */
        $classMethods = $this->betterNodeFinder->findInstanceOf($classLike, ClassMethod::class);
        foreach ($classMethods as $classMethod) {
            if (!$this->isClassMethodMatchingStaticCall($classMethod, $staticCall)) {
                continue;
            }
            if ($this->isInStaticClassMethod($staticCall)) {
                continue;
            }
            $thisVariable = new Variable('this');
            return new MethodCall($thisVariable, $staticCall->name, $staticCall->args);
        }
        return null;
    }
    private function isInStaticClassMethod(StaticCall $staticCall) : bool
    {
        $locationClassMethod = $this->betterNodeFinder->findParentType($staticCall, ClassMethod::class);
        if (!$locationClassMethod instanceof ClassMethod) {
            return \false;
        }
        return $locationClassMethod->isStatic();
    }
    private function isClassMethodMatchingStaticCall(ClassMethod $classMethod, StaticCall $staticCall) : bool
    {
        $classLike = $this->betterNodeFinder->findParentType($classMethod, ClassLike::class);
        if (!$classLike instanceof ClassLike) {
            return \false;
        }
        $className = (string) $this->nodeNameResolver->getName($classLike);
        $objectType = new ObjectType($className);
        $callerType = $this->nodeTypeResolver->getType($staticCall->class);
        return $objectType->equals($callerType);
    }
}
