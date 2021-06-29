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
use PhpParser\Node\Stmt\Property;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeCollector\NodeAnalyzer\ArrayCallableMethodMatcher;
use Rector\NodeCollector\ValueObject\ArrayCallable;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Array_\ArrayThisCallToThisMethodCallRector\ArrayThisCallToThisMethodCallRectorTest
 */
final class ArrayThisCallToThisMethodCallRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \Rector\NodeCollector\NodeAnalyzer\ArrayCallableMethodMatcher
     */
    private $arrayCallableMethodMatcher;
    /**
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(\Rector\NodeCollector\NodeAnalyzer\ArrayCallableMethodMatcher $arrayCallableMethodMatcher, \PHPStan\Reflection\ReflectionProvider $reflectionProvider)
    {
        $this->arrayCallableMethodMatcher = $arrayCallableMethodMatcher;
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
        $arrayCallable = $this->arrayCallableMethodMatcher->match($node);
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
        $nativeReflectionMethod = $nativeReflectionClass->getMethod($arrayCallable->getMethod());
        if ($nativeReflectionMethod->getNumberOfParameters() === 0) {
            return new \PhpParser\Node\Expr\MethodCall(new \PhpParser\Node\Expr\Variable('this'), $arrayCallable->getMethod());
        }
        $methodReflection = $classReflection->getNativeMethod($arrayCallable->getMethod());
        return $this->nodeFactory->createClosureFromMethodReflection($methodReflection);
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
}
