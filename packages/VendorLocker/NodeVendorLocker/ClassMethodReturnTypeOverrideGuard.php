<?php

declare (strict_types=1);
namespace Rector\VendorLocker\NodeVendorLocker;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\FunctionVariantWithPhpDocs;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\MixedType;
use Rector\Core\FileSystem\FilePathHelper;
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\FamilyTree\Reflection\FamilyRelationsAnalyzer;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PHPStan\ParametersAcceptorSelectorVariantsWrapper;
use Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;
use Rector\VendorLocker\ParentClassMethodTypeOverrideGuard;
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
     * @var \Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer
     */
    private $returnTypeInferer;
    /**
     * @readonly
     * @var \Rector\VendorLocker\ParentClassMethodTypeOverrideGuard
     */
    private $parentClassMethodTypeOverrideGuard;
    /**
     * @readonly
     * @var \Rector\Core\FileSystem\FilePathHelper
     */
    private $filePathHelper;
    public function __construct(NodeNameResolver $nodeNameResolver, ReflectionProvider $reflectionProvider, FamilyRelationsAnalyzer $familyRelationsAnalyzer, BetterNodeFinder $betterNodeFinder, AstResolver $astResolver, ReflectionResolver $reflectionResolver, ReturnTypeInferer $returnTypeInferer, ParentClassMethodTypeOverrideGuard $parentClassMethodTypeOverrideGuard, FilePathHelper $filePathHelper)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->reflectionProvider = $reflectionProvider;
        $this->familyRelationsAnalyzer = $familyRelationsAnalyzer;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->astResolver = $astResolver;
        $this->reflectionResolver = $reflectionResolver;
        $this->returnTypeInferer = $returnTypeInferer;
        $this->parentClassMethodTypeOverrideGuard = $parentClassMethodTypeOverrideGuard;
        $this->filePathHelper = $filePathHelper;
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
        if ($classReflection->isAbstract()) {
            return \true;
        }
        if ($classReflection->isInterface()) {
            return \true;
        }
        if (!$this->isReturnTypeChangeAllowed($classMethod)) {
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
    private function isReturnTypeChangeAllowed(ClassMethod $classMethod) : bool
    {
        // make sure return type is not protected by parent contract
        $parentClassMethodReflection = $this->parentClassMethodTypeOverrideGuard->getParentClassMethod($classMethod);
        // nothing to check
        if (!$parentClassMethodReflection instanceof MethodReflection) {
            return \true;
        }
        $scope = $classMethod->getAttribute(AttributeKey::SCOPE);
        if (!$scope instanceof Scope) {
            return \false;
        }
        $parametersAcceptor = ParametersAcceptorSelectorVariantsWrapper::select($parentClassMethodReflection, $classMethod, $scope);
        if ($parametersAcceptor instanceof FunctionVariantWithPhpDocs && !$parametersAcceptor->getNativeReturnType() instanceof MixedType) {
            return \false;
        }
        $classReflection = $parentClassMethodReflection->getDeclaringClass();
        $fileName = $classReflection->getFileName();
        // probably internal
        if ($fileName === null) {
            return \false;
        }
        /*
         * Below verify that both current file name and parent file name is not in the /vendor/, if yes, then allowed.
         * This can happen when rector run into /vendor/ directory while child and parent both are there.
         *
         *  @see https://3v4l.org/Rc0RF#v8.0.13
         *
         *     - both in /vendor/ -> allowed
         *     - one of them in /vendor/ -> not allowed
         *     - both not in /vendor/ -> allowed
         */
        /** @var ClassReflection $currentClassReflection */
        $currentClassReflection = $this->reflectionResolver->resolveClassReflection($classMethod);
        /** @var string $currentFileName */
        $currentFileName = $currentClassReflection->getFileName();
        // child (current)
        $normalizedCurrentFileName = $this->filePathHelper->normalizePathAndSchema($currentFileName);
        $isCurrentInVendor = \strpos($normalizedCurrentFileName, '/vendor/') !== \false;
        // parent
        $normalizedFileName = $this->filePathHelper->normalizePathAndSchema($fileName);
        $isParentInVendor = \strpos($normalizedFileName, '/vendor/') !== \false;
        return $isCurrentInVendor && $isParentInVendor || !$isCurrentInVendor && !$isParentInVendor;
    }
    /**
     * @param ClassReflection[] $childrenClassReflections
     */
    private function shouldSkipHasChildHasReturnType(array $childrenClassReflections, ClassMethod $classMethod) : bool
    {
        $returnType = $this->returnTypeInferer->inferFunctionLike($classMethod);
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
            $childReturnType = $this->returnTypeInferer->inferFunctionLike($method);
            if (!$returnType->isVoid()->yes()) {
                continue;
            }
            if ($childReturnType->isVoid()->yes()) {
                continue;
            }
            return \true;
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
}
