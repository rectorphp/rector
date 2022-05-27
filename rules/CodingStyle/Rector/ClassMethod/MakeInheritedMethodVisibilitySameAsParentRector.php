<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\ClassReflection;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\Privatization\NodeManipulator\VisibilityManipulator;
use ReflectionMethod;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://3v4l.org/RFYmn
 *
 * @see \Rector\Tests\CodingStyle\Rector\ClassMethod\MakeInheritedMethodVisibilitySameAsParentRector\MakeInheritedMethodVisibilitySameAsParentRectorTest
 */
final class MakeInheritedMethodVisibilitySameAsParentRector extends \Rector\Core\Rector\AbstractRector
{
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
    public function __construct(\Rector\Privatization\NodeManipulator\VisibilityManipulator $visibilityManipulator, \Rector\Core\Reflection\ReflectionResolver $reflectionResolver)
    {
        $this->visibilityManipulator = $visibilityManipulator;
        $this->reflectionResolver = $reflectionResolver;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Make method visibility same as parent one', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class ChildClass extends ParentClass
{
    public function run()
    {
    }
}

class ParentClass
{
    protected function run()
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class ChildClass extends ParentClass
{
    protected function run()
    {
    }
}

class ParentClass
{
    protected function run()
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
        return [\PhpParser\Node\Stmt\Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $classReflection = $this->reflectionResolver->resolveClassReflection($node);
        if (!$classReflection instanceof \PHPStan\Reflection\ClassReflection) {
            return null;
        }
        $parentClassReflections = $classReflection->getParents();
        if ($parentClassReflections === []) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->getMethods() as $classMethod) {
            if ($classMethod->isMagic()) {
                continue;
            }
            /** @var string $methodName */
            $methodName = $this->getName($classMethod->name);
            foreach ($parentClassReflections as $parentClassReflection) {
                $nativeClassReflection = $parentClassReflection->getNativeReflection();
                // the class reflection above takes also @method annotations into an account
                if (!$nativeClassReflection->hasMethod($methodName)) {
                    continue;
                }
                /** @var ReflectionMethod $parentReflectionMethod */
                $parentReflectionMethod = $nativeClassReflection->getMethod($methodName);
                if ($this->isClassMethodCompatibleWithParentReflectionMethod($classMethod, $parentReflectionMethod)) {
                    continue;
                }
                $this->changeClassMethodVisibilityBasedOnReflectionMethod($classMethod, $parentReflectionMethod);
                $hasChanged = \true;
            }
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function isClassMethodCompatibleWithParentReflectionMethod(\PhpParser\Node\Stmt\ClassMethod $classMethod, \ReflectionMethod $reflectionMethod) : bool
    {
        if ($reflectionMethod->isPublic() && $classMethod->isPublic()) {
            return \true;
        }
        if ($reflectionMethod->isProtected() && $classMethod->isProtected()) {
            return \true;
        }
        if (!$reflectionMethod->isPrivate()) {
            return \false;
        }
        return $classMethod->isPrivate();
    }
    private function changeClassMethodVisibilityBasedOnReflectionMethod(\PhpParser\Node\Stmt\ClassMethod $classMethod, \ReflectionMethod $reflectionMethod) : void
    {
        if ($reflectionMethod->isPublic()) {
            $this->visibilityManipulator->makePublic($classMethod);
            return;
        }
        if ($reflectionMethod->isProtected()) {
            $this->visibilityManipulator->makeProtected($classMethod);
            return;
        }
        if ($reflectionMethod->isPrivate()) {
            $this->visibilityManipulator->makePrivate($classMethod);
        }
    }
}
