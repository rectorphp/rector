<?php

declare (strict_types=1);
namespace Rector\VendorLocker\NodeVendorLocker;

use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ExtendedFunctionVariant;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\MixedType;
use Rector\FileSystem\FilePathHelper;
use Rector\NodeAnalyzer\MagicClassMethodAnalyzer;
use Rector\NodeTypeResolver\PHPStan\ParametersAcceptorSelectorVariantsWrapper;
use Rector\Reflection\ReflectionResolver;
use Rector\VendorLocker\ParentClassMethodTypeOverrideGuard;
final class ClassMethodReturnTypeOverrideGuard
{
    /**
     * @readonly
     */
    private ReflectionResolver $reflectionResolver;
    /**
     * @readonly
     */
    private ParentClassMethodTypeOverrideGuard $parentClassMethodTypeOverrideGuard;
    /**
     * @readonly
     */
    private FilePathHelper $filePathHelper;
    /**
     * @readonly
     */
    private MagicClassMethodAnalyzer $magicClassMethodAnalyzer;
    public function __construct(ReflectionResolver $reflectionResolver, ParentClassMethodTypeOverrideGuard $parentClassMethodTypeOverrideGuard, FilePathHelper $filePathHelper, MagicClassMethodAnalyzer $magicClassMethodAnalyzer)
    {
        $this->reflectionResolver = $reflectionResolver;
        $this->parentClassMethodTypeOverrideGuard = $parentClassMethodTypeOverrideGuard;
        $this->filePathHelper = $filePathHelper;
        $this->magicClassMethodAnalyzer = $magicClassMethodAnalyzer;
    }
    public function shouldSkipClassMethod(ClassMethod $classMethod, Scope $scope) : bool
    {
        if ($this->magicClassMethodAnalyzer->isUnsafeOverridden($classMethod)) {
            return \true;
        }
        // except magic check on above, early allow add return type on private method
        if ($classMethod->isPrivate()) {
            return \false;
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
        return !$this->isReturnTypeChangeAllowed($classMethod, $scope);
    }
    private function isReturnTypeChangeAllowed(ClassMethod $classMethod, Scope $scope) : bool
    {
        // make sure return type is not protected by parent contract
        $parentClassMethodReflection = $this->parentClassMethodTypeOverrideGuard->getParentClassMethod($classMethod);
        // nothing to check
        if (!$parentClassMethodReflection instanceof MethodReflection) {
            return !$this->parentClassMethodTypeOverrideGuard->hasParentClassMethod($classMethod);
        }
        $parametersAcceptor = ParametersAcceptorSelectorVariantsWrapper::select($parentClassMethodReflection, $classMethod, $scope);
        if ($parametersAcceptor instanceof ExtendedFunctionVariant && !$parametersAcceptor->getNativeReturnType() instanceof MixedType) {
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
}
