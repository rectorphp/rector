<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\TypeAnalyzer;

use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ArrayType;
use PHPStan\Type\ClassStringType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NeverType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\VoidType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\TypeDeclaration\TypeNormalizer;
final class AdvancedArrayAnalyzer
{
    /**
     * @var \Rector\TypeDeclaration\TypeNormalizer
     */
    private $typeNormalizer;
    /**
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    public function __construct(\Rector\TypeDeclaration\TypeNormalizer $typeNormalizer, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->typeNormalizer = $typeNormalizer;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    public function isClassStringArrayByStringArrayOverride(\PHPStan\Type\ArrayType $arrayType, \PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        if (!$arrayType instanceof \PHPStan\Type\Constant\ConstantArrayType) {
            return \false;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        $arrayType = $this->typeNormalizer->convertConstantArrayTypeToArrayType($arrayType);
        if (!$arrayType instanceof \PHPStan\Type\ArrayType) {
            return \false;
        }
        $currentReturnType = $phpDocInfo->getReturnType();
        if (!$currentReturnType instanceof \PHPStan\Type\ArrayType) {
            return \false;
        }
        if (!$currentReturnType->getItemType() instanceof \PHPStan\Type\ClassStringType) {
            return \false;
        }
        return $arrayType->getItemType() instanceof \PHPStan\Type\StringType;
    }
    public function isMixedOfSpecificOverride(\PHPStan\Type\ArrayType $arrayType, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo) : bool
    {
        if (!$arrayType->getItemType() instanceof \PHPStan\Type\MixedType) {
            return \false;
        }
        $currentReturnType = $phpDocInfo->getReturnType();
        return $currentReturnType instanceof \PHPStan\Type\ArrayType;
    }
    public function isMoreSpecificArrayTypeOverride(\PHPStan\Type\Type $newType, \PhpParser\Node\Stmt\ClassMethod $classMethod, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo) : bool
    {
        if (!$newType instanceof \PHPStan\Type\Constant\ConstantArrayType) {
            return \false;
        }
        if (!$newType->getItemType() instanceof \PHPStan\Type\NeverType) {
            return \false;
        }
        $phpDocReturnType = $phpDocInfo->getReturnType();
        if (!$phpDocReturnType instanceof \PHPStan\Type\ArrayType) {
            return \false;
        }
        return !$phpDocReturnType->getItemType() instanceof \PHPStan\Type\VoidType;
    }
    public function isNewAndCurrentTypeBothCallable(\PHPStan\Type\ArrayType $newArrayType, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo) : bool
    {
        $currentReturnType = $phpDocInfo->getReturnType();
        if (!$currentReturnType instanceof \PHPStan\Type\ArrayType) {
            return \false;
        }
        if (!$newArrayType->getItemType()->isCallable()->yes()) {
            return \false;
        }
        return $currentReturnType->getItemType()->isCallable()->yes();
    }
}
