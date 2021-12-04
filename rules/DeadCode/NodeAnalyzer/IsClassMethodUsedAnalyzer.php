<?php

declare (strict_types=1);
namespace Rector\DeadCode\NodeAnalyzer;

use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\NodeCollector\NodeAnalyzer\ArrayCallableMethodMatcher;
use Rector\NodeCollector\ValueObject\ArrayCallable;
use Rector\NodeCollector\ValueObject\ArrayCallableDynamicMethod;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class IsClassMethodUsedAnalyzer
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\AstResolver
     */
    private $astResolver;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    /**
     * @readonly
     * @var \Rector\NodeCollector\NodeAnalyzer\ArrayCallableMethodMatcher
     */
    private $arrayCallableMethodMatcher;
    /**
     * @readonly
     * @var \Rector\DeadCode\NodeAnalyzer\CallCollectionAnalyzer
     */
    private $callCollectionAnalyzer;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Core\PhpParser\AstResolver $astResolver, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\Core\PhpParser\Node\Value\ValueResolver $valueResolver, \Rector\NodeCollector\NodeAnalyzer\ArrayCallableMethodMatcher $arrayCallableMethodMatcher, \Rector\DeadCode\NodeAnalyzer\CallCollectionAnalyzer $callCollectionAnalyzer)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->astResolver = $astResolver;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->valueResolver = $valueResolver;
        $this->arrayCallableMethodMatcher = $arrayCallableMethodMatcher;
        $this->callCollectionAnalyzer = $callCollectionAnalyzer;
    }
    public function isClassMethodUsed(\PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        $class = $this->betterNodeFinder->findParentType($classMethod, \PhpParser\Node\Stmt\Class_::class);
        if (!$class instanceof \PhpParser\Node\Stmt\Class_) {
            return \true;
        }
        $classMethodName = $this->nodeNameResolver->getName($classMethod);
        // 1. direct normal calls
        if ($this->isClassMethodCalledInLocalMethodCall($class, $classMethodName)) {
            return \true;
        }
        // 2. direct static calls
        if ($this->isClassMethodUsedInLocalStaticCall($class, $classMethodName)) {
            return \true;
        }
        // 3. magic array calls!
        if ($this->isClassMethodCalledInLocalArrayCall($class, $classMethod)) {
            return \true;
        }
        // 4. private method exists in trait and is overwritten by the class
        return $this->doesMethodExistInTrait($classMethod, $classMethodName);
    }
    private function isClassMethodUsedInLocalStaticCall(\PhpParser\Node\Stmt\Class_ $class, string $classMethodName) : bool
    {
        $className = (string) $this->nodeNameResolver->getName($class);
        /** @var StaticCall[] $staticCalls */
        $staticCalls = $this->betterNodeFinder->findInstanceOf($class, \PhpParser\Node\Expr\StaticCall::class);
        return $this->callCollectionAnalyzer->isExists($staticCalls, $classMethodName, $className);
    }
    private function isClassMethodCalledInLocalMethodCall(\PhpParser\Node\Stmt\Class_ $class, string $classMethodName) : bool
    {
        $className = (string) $this->nodeNameResolver->getName($class);
        /** @var MethodCall[] $methodCalls */
        $methodCalls = $this->betterNodeFinder->findInstanceOf($class, \PhpParser\Node\Expr\MethodCall::class);
        return $this->callCollectionAnalyzer->isExists($methodCalls, $classMethodName, $className);
    }
    private function isInArrayMap(\PhpParser\Node\Stmt\Class_ $class, \PhpParser\Node\Expr\Array_ $array) : bool
    {
        $parentFuncCall = $this->betterNodeFinder->findParentType($array, \PhpParser\Node\Expr\FuncCall::class);
        if (!$parentFuncCall instanceof \PhpParser\Node\Expr\FuncCall) {
            return \false;
        }
        if (!$this->nodeNameResolver->isName($parentFuncCall->name, 'array_map')) {
            return \false;
        }
        if (\count($array->items) !== 2) {
            return \false;
        }
        if (!$array->items[1] instanceof \PhpParser\Node\Expr\ArrayItem) {
            return \false;
        }
        $value = $this->valueResolver->getValue($array->items[1]->value);
        if (!\is_string($value)) {
            return \false;
        }
        return $class->getMethod($value) instanceof \PhpParser\Node\Stmt\ClassMethod;
    }
    private function isClassMethodCalledInLocalArrayCall(\PhpParser\Node\Stmt\Class_ $class, \PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        /** @var Array_[] $arrays */
        $arrays = $this->betterNodeFinder->findInstanceOf($class, \PhpParser\Node\Expr\Array_::class);
        foreach ($arrays as $array) {
            if ($this->isInArrayMap($class, $array)) {
                return \true;
            }
            $arrayCallable = $this->arrayCallableMethodMatcher->match($array);
            if ($arrayCallable instanceof \Rector\NodeCollector\ValueObject\ArrayCallableDynamicMethod) {
                return \true;
            }
            if ($this->shouldSkipArrayCallable($class, $arrayCallable)) {
                continue;
            }
            // the method is used
            /** @var ArrayCallable $arrayCallable */
            if ($this->nodeNameResolver->isName($classMethod->name, $arrayCallable->getMethod())) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param \Rector\NodeCollector\ValueObject\ArrayCallable|null $arrayCallable
     */
    private function shouldSkipArrayCallable(\PhpParser\Node\Stmt\Class_ $class, $arrayCallable) : bool
    {
        if (!$arrayCallable instanceof \Rector\NodeCollector\ValueObject\ArrayCallable) {
            return \true;
        }
        // is current class method?
        return !$this->nodeNameResolver->isName($class, $arrayCallable->getClass());
    }
    private function doesMethodExistInTrait(\PhpParser\Node\Stmt\ClassMethod $classMethod, string $classMethodName) : bool
    {
        $scope = $classMethod->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return \false;
        }
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof \PHPStan\Reflection\ClassReflection) {
            return \false;
        }
        $traits = $this->astResolver->parseClassReflectionTraits($classReflection);
        foreach ($traits as $trait) {
            $method = $trait->getMethod($classMethodName);
            if (!$method instanceof \PhpParser\Node\Stmt\ClassMethod) {
                continue;
            }
            return \true;
        }
        return \false;
    }
}
