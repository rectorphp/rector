<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeTraverser;
use PHPStan\Reflection\ClassReflection;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\Privatization\NodeManipulator\VisibilityManipulator;
use Rector\Privatization\VisibilityGuard\ClassMethodVisibilityGuard;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\ClassMethod\LocallyCalledStaticMethodToNonStaticRector\LocallyCalledStaticMethodToNonStaticRectorTest
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
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Class_
    {
        $hasChanged = \false;
        foreach ($node->getMethods() as $classMethod) {
            if (!$classMethod->isPrivate()) {
                continue;
            }
            $changedClassMethod = $this->refactorClassMethod($node, $classMethod);
            if ($changedClassMethod instanceof ClassMethod) {
                $hasChanged = \true;
            }
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function refactorClassMethod(Class_ $class, ClassMethod $classMethod) : ?ClassMethod
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
        if ($this->isClassMethodCalledInAnotherStaticClassMethod($class, $classMethod)) {
            return null;
        }
        // replace all the calls
        $classMethodName = $this->getName($classMethod);
        $this->traverseNodesWithCallable($class, function (Node $node) use($classMethodName) : ?MethodCall {
            if (!$node instanceof StaticCall) {
                return null;
            }
            if (!$this->isName($node->class, 'self')) {
                return null;
            }
            if (!$this->isName($node->name, $classMethodName)) {
                return null;
            }
            return new MethodCall(new Variable('this'), $classMethodName, $node->args);
        });
        // change static calls to non-static ones, but only if in non-static method!!!
        $this->visibilityManipulator->makeNonStatic($classMethod);
        return $classMethod;
    }
    /**
     * If the static class method is called in another static class method,
     * we should keep it to avoid calling $this in static
     */
    private function isClassMethodCalledInAnotherStaticClassMethod(Class_ $class, ClassMethod $classMethod) : bool
    {
        $currentClassMethodName = $this->getName($classMethod);
        $isInsideStaticClassMethod = \false;
        // check if called stati call somewhere in class, but only in static methods
        foreach ($class->getMethods() as $checkedClassMethod) {
            // not a problem
            if (!$checkedClassMethod->isStatic()) {
                continue;
            }
            $this->traverseNodesWithCallable($checkedClassMethod, function (Node $node) use($currentClassMethodName, &$isInsideStaticClassMethod) : ?int {
                if (!$node instanceof StaticCall) {
                    return null;
                }
                if (!$this->isName($node->class, 'self')) {
                    return null;
                }
                if (!$this->isName($node->name, $currentClassMethodName)) {
                    return null;
                }
                $isInsideStaticClassMethod = \true;
                return NodeTraverser::STOP_TRAVERSAL;
            });
            if ($isInsideStaticClassMethod) {
                return $isInsideStaticClassMethod;
            }
        }
        return \false;
    }
}
