<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Array_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeCollector\NodeAnalyzer\ArrayCallableMethodReferenceAnalyzer;
use Rector\NodeCollector\ValueObject\ArrayCallable;
use Rector\NodeTypeResolver\Node\AttributeKey;
use ReflectionMethod;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Array_\ArrayThisCallToThisMethodCallRector\ArrayThisCallToThisMethodCallRectorTest
 */
final class ArrayThisCallToThisMethodCallRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var ArrayCallableMethodReferenceAnalyzer
     */
    private $arrayCallableMethodReferenceAnalyzer;
    /**
     * @var ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(\Rector\NodeCollector\NodeAnalyzer\ArrayCallableMethodReferenceAnalyzer $arrayCallableMethodReferenceAnalyzer, \PHPStan\Reflection\ReflectionProvider $reflectionProvider)
    {
        $this->arrayCallableMethodReferenceAnalyzer = $arrayCallableMethodReferenceAnalyzer;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change `[$this, someMethod]` without any args to $this->someMethod()', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $values = [$this, 'giveMeMore'];
    }

    public function giveMeMore()
    {
        return 'more';
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $values = $this->giveMeMore();
    }

    public function giveMeMore()
    {
        return 'more';
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
        return [\PhpParser\Node\Expr\Array_::class];
    }
    /**
     * @param Array_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $arrayCallable = $this->arrayCallableMethodReferenceAnalyzer->match($node);
        if (!$arrayCallable instanceof \Rector\NodeCollector\ValueObject\ArrayCallable) {
            return null;
        }
        if ($this->isAssignedToNetteMagicOnProperty($node)) {
            return null;
        }
        if ($this->isInsideProperty($node)) {
            return null;
        }
        $parentNode = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        // skip if part of method
        if ($parentNode instanceof \PhpParser\Node\Arg) {
            return null;
        }
        if (!$this->reflectionProvider->hasClass($arrayCallable->getClass())) {
            return null;
        }
        $classReflection = $this->reflectionProvider->getClass($arrayCallable->getClass());
        if (!$classReflection->hasMethod($arrayCallable->getMethod())) {
            return null;
        }
        $nativeReflectionClass = $classReflection->getNativeReflection();
        $reflectionMethod = $nativeReflectionClass->getMethod($arrayCallable->getMethod());
        $this->privatizeClassMethod($reflectionMethod);
        if ($reflectionMethod->getNumberOfParameters() > 0) {
            $classMethod = $this->nodeRepository->findClassMethod($arrayCallable->getClass(), $arrayCallable->getMethod());
            if ($classMethod !== null) {
                return $this->nodeFactory->createClosureFromClassMethod($classMethod);
            }
            return null;
        }
        return new \PhpParser\Node\Expr\MethodCall(new \PhpParser\Node\Expr\Variable('this'), $arrayCallable->getMethod());
    }
    private function isAssignedToNetteMagicOnProperty(\PhpParser\Node\Expr\Array_ $array) : bool
    {
        $parent = $array->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$parent instanceof \PhpParser\Node\Expr\Assign) {
            return \false;
        }
        if (!$parent->var instanceof \PhpParser\Node\Expr\ArrayDimFetch) {
            return \false;
        }
        if (!$parent->var->var instanceof \PhpParser\Node\Expr\PropertyFetch) {
            return \false;
        }
        /** @var PropertyFetch $propertyFetch */
        $propertyFetch = $parent->var->var;
        return $this->isName($propertyFetch->name, 'on*');
    }
    private function isInsideProperty(\PhpParser\Node\Expr\Array_ $array) : bool
    {
        $parentProperty = $this->betterNodeFinder->findParentType($array, \PhpParser\Node\Stmt\Property::class);
        return $parentProperty !== null;
    }
    private function privatizeClassMethod(\ReflectionMethod $reflectionMethod) : void
    {
        $classMethod = $this->nodeRepository->findClassMethodByMethodReflection($reflectionMethod);
        if (!$classMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return;
        }
        if ($classMethod->isPrivate()) {
            return;
        }
        $this->visibilityManipulator->makePrivate($classMethod);
    }
}
