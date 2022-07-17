<?php

declare (strict_types=1);
namespace Rector\VendorLocker\NodeVendorLocker;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Generic\GenericClassStringType;
use PHPStan\Type\MixedType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\FamilyTree\Reflection\FamilyRelationsAnalyzer;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\StaticTypeMapper\PhpDoc\CustomPHPStanDetector;
final class ClassMethodReturnTypeOverrideGuard
{
    /**
     * @var array<class-string, array<string>>
     */
    private const CHAOTIC_CLASS_METHOD_NAMES = ['PhpParser\\NodeVisitor' => ['enterNode', 'leaveNode', 'beforeTraverse', 'afterTraverse']];
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @readonly
     * @var \Rector\FamilyTree\Reflection\FamilyRelationsAnalyzer
     */
    private $familyRelationsAnalyzer;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\AstResolver
     */
    private $astResolver;
    /**
     * @readonly
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\PhpDoc\CustomPHPStanDetector
     */
    private $customPHPStanDetector;
    public function __construct(NodeNameResolver $nodeNameResolver, ReflectionProvider $reflectionProvider, FamilyRelationsAnalyzer $familyRelationsAnalyzer, BetterNodeFinder $betterNodeFinder, AstResolver $astResolver, ReflectionResolver $reflectionResolver, CustomPHPStanDetector $customPHPStanDetector)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->reflectionProvider = $reflectionProvider;
        $this->familyRelationsAnalyzer = $familyRelationsAnalyzer;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->astResolver = $astResolver;
        $this->reflectionResolver = $reflectionResolver;
        $this->customPHPStanDetector = $customPHPStanDetector;
    }
    public function shouldSkipClassMethod(ClassMethod $classMethod) : bool
    {
        // 1. skip magic methods
        if ($classMethod->isMagic()) {
            return \true;
        }
        // 2. skip chaotic contract class methods
        if ($this->shouldSkipChaoticClassMethods($classMethod)) {
            return \true;
        }
        $classReflection = $this->reflectionResolver->resolveClassReflection($classMethod);
        if (!$classReflection instanceof ClassReflection) {
            return \true;
        }
        $childrenClassReflections = $this->familyRelationsAnalyzer->getChildrenOfClassReflection($classReflection);
        if ($childrenClassReflections === []) {
            return \false;
        }
        if ($classMethod->returnType instanceof Node) {
            return \true;
        }
        if ($this->shouldSkipHasChildHasReturnType($childrenClassReflections, $classMethod)) {
            return \true;
        }
        return $this->hasClassMethodExprReturn($classMethod);
    }
    public function shouldSkipClassMethodOldTypeWithNewType(Type $oldType, Type $newType, ClassMethod $classMethod) : bool
    {
        if ($this->customPHPStanDetector->isCustomType($oldType, $classMethod)) {
            return \true;
        }
        if ($oldType instanceof MixedType) {
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
    private function shouldSkipHasChildHasReturnType(array $childrenClassReflections, ClassMethod $classMethod) : bool
    {
        $methodName = $this->nodeNameResolver->getName($classMethod);
        foreach ($childrenClassReflections as $childClassReflection) {
            if (!$childClassReflection->hasNativeMethod($methodName)) {
                continue;
            }
            $methodReflection = $childClassReflection->getNativeMethod($methodName);
            $method = $this->astResolver->resolveClassMethodFromMethodReflection($methodReflection);
            if (!$method instanceof ClassMethod) {
                continue;
            }
            if ($method->returnType instanceof Node) {
                return \true;
            }
        }
        return \false;
    }
    private function shouldSkipChaoticClassMethods(ClassMethod $classMethod) : bool
    {
        $classReflection = $this->reflectionResolver->resolveClassReflection($classMethod);
        if (!$classReflection instanceof ClassReflection) {
            return \true;
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
    private function hasClassMethodExprReturn(ClassMethod $classMethod) : bool
    {
        return (bool) $this->betterNodeFinder->findFirst((array) $classMethod->stmts, static function (Node $node) : bool {
            if (!$node instanceof Return_) {
                return \false;
            }
            return $node->expr instanceof Expr;
        });
    }
    private function isFirstArrayTypeMoreAdvanced(Type $oldType, Type $newType) : bool
    {
        if (!$oldType instanceof ArrayType) {
            return \false;
        }
        if (!$newType instanceof ArrayType) {
            return \false;
        }
        if (!$oldType->getItemType() instanceof StringType) {
            return \false;
        }
        return $newType->getItemType() instanceof GenericClassStringType;
    }
}
