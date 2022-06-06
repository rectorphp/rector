<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\TypeDeclaration\TypeAnalyzer;

use RectorPrefix20220606\PhpParser\Node\Stmt\ClassMethod;
use RectorPrefix20220606\PHPStan\Type\ArrayType;
use RectorPrefix20220606\PHPStan\Type\ClassStringType;
use RectorPrefix20220606\PHPStan\Type\Constant\ConstantArrayType;
use RectorPrefix20220606\PHPStan\Type\MixedType;
use RectorPrefix20220606\PHPStan\Type\NeverType;
use RectorPrefix20220606\PHPStan\Type\StringType;
use RectorPrefix20220606\PHPStan\Type\Type;
use RectorPrefix20220606\PHPStan\Type\TypeUtils;
use RectorPrefix20220606\PHPStan\Type\VoidType;
use RectorPrefix20220606\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use RectorPrefix20220606\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use RectorPrefix20220606\Rector\TypeDeclaration\TypeNormalizer;
final class AdvancedArrayAnalyzer
{
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeNormalizer
     */
    private $typeNormalizer;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    public function __construct(TypeNormalizer $typeNormalizer, PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->typeNormalizer = $typeNormalizer;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    public function isClassStringArrayByStringArrayOverride(ArrayType $arrayType, ClassMethod $classMethod) : bool
    {
        if (!$arrayType instanceof ConstantArrayType) {
            return \false;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        $arrayType = $this->typeNormalizer->convertConstantArrayTypeToArrayType($arrayType);
        if (!$arrayType instanceof ArrayType) {
            return \false;
        }
        $currentReturnType = $phpDocInfo->getReturnType();
        if (!$currentReturnType instanceof ArrayType) {
            return \false;
        }
        if (!$currentReturnType->getItemType() instanceof ClassStringType) {
            return \false;
        }
        return $arrayType->getItemType() instanceof StringType;
    }
    public function isMixedOfSpecificOverride(ArrayType $arrayType, PhpDocInfo $phpDocInfo) : bool
    {
        if (!$arrayType->getItemType() instanceof MixedType) {
            return \false;
        }
        $currentReturnType = $phpDocInfo->getReturnType();
        $arrayTypes = TypeUtils::getArrays($currentReturnType);
        return $arrayTypes !== [];
    }
    public function isMoreSpecificArrayTypeOverride(Type $newType, PhpDocInfo $phpDocInfo) : bool
    {
        if (!$newType instanceof ConstantArrayType) {
            return \false;
        }
        if (!$newType->getItemType() instanceof NeverType) {
            return \false;
        }
        $phpDocReturnType = $phpDocInfo->getReturnType();
        if (!$phpDocReturnType instanceof ArrayType) {
            return \false;
        }
        return !$phpDocReturnType->getItemType() instanceof VoidType;
    }
    public function isNewAndCurrentTypeBothCallable(ArrayType $newArrayType, PhpDocInfo $phpDocInfo) : bool
    {
        $currentReturnType = $phpDocInfo->getReturnType();
        if (!$currentReturnType instanceof ArrayType) {
            return \false;
        }
        if (!$newArrayType->getItemType()->isCallable()->yes()) {
            return \false;
        }
        return $currentReturnType->getItemType()->isCallable()->yes();
    }
}
