<?php

declare (strict_types=1);
namespace Rector\VendorLocker\NodeVendorLocker;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\ClassMethod;
//use PHPStan\Analyser\Scope;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Generic\GenericClassStringType;
use PHPStan\Type\MixedType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\FamilyTree\Reflection\FamilyRelationsAnalyzer;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class ClassMethodReturnTypeOverrideGuard
{
    /**
     * @var array<class-string, array<string>>
     */
    private const CHAOTIC_CLASS_METHOD_NAMES = ['PhpParser\\NodeVisitor' => ['enterNode', 'leaveNode', 'beforeTraverse', 'afterTraverse']];
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @var \Rector\FamilyTree\Reflection\FamilyRelationsAnalyzer
     */
    private $familyRelationsAnalyzer;
    /**
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @var \Rector\Core\PhpParser\AstResolver
     */
    private $astResolver;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \PHPStan\Reflection\ReflectionProvider $reflectionProvider, \Rector\FamilyTree\Reflection\FamilyRelationsAnalyzer $familyRelationsAnalyzer, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\Core\PhpParser\AstResolver $astResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->reflectionProvider = $reflectionProvider;
        $this->familyRelationsAnalyzer = $familyRelationsAnalyzer;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->astResolver = $astResolver;
    }
    public function shouldSkipClassMethod(\PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        // 1. skip magic methods
        if ($classMethod->isMagic()) {
            return \true;
        }
        // 2. skip chaotic contract class methods
        if ($this->shouldSkipChaoticClassMethods($classMethod)) {
            return \true;
        }
        $scope = $classMethod->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return \false;
        }
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof \PHPStan\Reflection\ClassReflection) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        $childrenClassReflections = $this->familyRelationsAnalyzer->getChildrenOfClassReflection($classReflection);
        if ($childrenClassReflections === []) {
            return \false;
        }
        if ($classMethod->returnType instanceof \PhpParser\Node) {
            return \true;
        }
        if ($this->shouldSkipHasChildNoReturn($childrenClassReflections, $classMethod, $scope)) {
            return \true;
        }
        return $this->hasClassMethodExprReturn($classMethod);
    }
    public function shouldSkipClassMethodOldTypeWithNewType(\PHPStan\Type\Type $oldType, \PHPStan\Type\Type $newType) : bool
    {
        if ($oldType instanceof \PHPStan\Type\MixedType) {
            return \false;
        }
        // new generic string type is more advanced than old array type
        if ($this->isFirstArrayTypeMoreAdvanced($oldType, $newType)) {
            return \false;
        }
        return $oldType->isSuperTypeOf($newType)->yes();
    }
    /**
     * @param ClassReflection[] $childrenClassReflections
     */
    private function shouldSkipHasChildNoReturn(array $childrenClassReflections, \PhpParser\Node\Stmt\ClassMethod $classMethod, \PHPStan\Analyser\Scope $scope) : bool
    {
        $methodName = $this->nodeNameResolver->getName($classMethod);
        foreach ($childrenClassReflections as $childClassReflection) {
            if (!$childClassReflection->hasMethod($methodName)) {
                continue;
            }
            $methodReflection = $childClassReflection->getMethod($methodName, $scope);
            $method = $this->astResolver->resolveClassMethodFromMethodReflection($methodReflection);
            if (!$method instanceof \PhpParser\Node\Stmt\ClassMethod) {
                continue;
            }
            if ($method->returnType === null) {
                return \true;
            }
        }
        return \false;
    }
    private function shouldSkipChaoticClassMethods(\PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        /** @var string|null $className */
        $className = $classMethod->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NAME);
        if ($className === null) {
            return \false;
        }
        $scope = $classMethod->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return \false;
        }
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof \PHPStan\Reflection\ClassReflection) {
            return \false;
        }
        foreach (self::CHAOTIC_CLASS_METHOD_NAMES as $chaoticClass => $chaoticMethodNames) {
            if (!$this->reflectionProvider->hasClass($chaoticClass)) {
                continue;
            }
            $chaoticClassReflection = $this->reflectionProvider->getClass($chaoticClass);
            if (!$classReflection->isSubclassOf($chaoticClassReflection->getName())) {
                continue;
            }
            return $this->nodeNameResolver->isNames($classMethod, $chaoticMethodNames);
        }
        return \false;
    }
    private function hasClassMethodExprReturn(\PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        return (bool) $this->betterNodeFinder->findFirst((array) $classMethod->stmts, function (\PhpParser\Node $node) : bool {
            if (!$node instanceof \PhpParser\Node\Stmt\Return_) {
                return \false;
            }
            return $node->expr instanceof \PhpParser\Node\Expr;
        });
    }
    private function isFirstArrayTypeMoreAdvanced(\PHPStan\Type\Type $oldType, \PHPStan\Type\Type $newType) : bool
    {
        if (!$oldType instanceof \PHPStan\Type\ArrayType) {
            return \false;
        }
        if (!$newType instanceof \PHPStan\Type\ArrayType) {
            return \false;
        }
        if (!$oldType->getItemType() instanceof \PHPStan\Type\StringType) {
            return \false;
        }
        return $newType->getItemType() instanceof \PHPStan\Type\Generic\GenericClassStringType;
    }
}
