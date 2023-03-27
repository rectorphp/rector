<?php

declare (strict_types=1);
namespace Rector\Privatization\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\Privatization\NodeManipulator\VisibilityManipulator;
use Rector\Privatization\VisibilityGuard\ClassMethodVisibilityGuard;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Privatization\Rector\ClassMethod\PrivatizeFinalClassMethodRector\PrivatizeFinalClassMethodRectorTest
 */
final class PrivatizeFinalClassMethodRector extends AbstractRector
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
        return new RuleDefinition('Change protected class method to private if possible', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    protected function someMethod()
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    private function someMethod()
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
    public function refactor(Node $node) : ?Node
    {
        if (!$node->isFinal()) {
            return null;
        }
        $classReflection = $this->reflectionResolver->resolveClassAndAnonymousClass($node);
        $hasChanged = \false;
        foreach ($node->getMethods() as $classMethod) {
            if ($this->shouldSkipClassMethod($classMethod)) {
                continue;
            }
            if ($this->classMethodVisibilityGuard->isClassMethodVisibilityGuardedByParent($classMethod, $classReflection)) {
                continue;
            }
            if ($this->classMethodVisibilityGuard->isClassMethodVisibilityGuardedByTrait($classMethod, $classReflection)) {
                continue;
            }
            $this->visibilityManipulator->makePrivate($classMethod);
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function shouldSkipClassMethod(ClassMethod $classMethod) : bool
    {
        if ($this->isName($classMethod, 'createComponent*')) {
            return \true;
        }
        return !$classMethod->isProtected();
    }
}
