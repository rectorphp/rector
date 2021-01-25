<?php

declare(strict_types=1);

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
     * @var TypeNormalizer
     */
    private $typeNormalizer;

    /**
     * @var PhpDocInfoFactory
     */
    private $phpDocInfoFactory;

    public function __construct(TypeNormalizer $typeNormalizer, PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->typeNormalizer = $typeNormalizer;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }

    public function isClassStringArrayByStringArrayOverride(ArrayType $arrayType, ClassMethod $classMethod): bool
    {
        if (! $arrayType instanceof ConstantArrayType) {
            return false;
        }

        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);

        $arrayType = $this->typeNormalizer->convertConstantArrayTypeToArrayType($arrayType);
        if (! $arrayType instanceof ArrayType) {
            return false;
        }

        $currentReturnType = $phpDocInfo->getReturnType();
        if (! $currentReturnType instanceof ArrayType) {
            return false;
        }

        if (! $currentReturnType->getItemType() instanceof ClassStringType) {
            return false;
        }

        return $arrayType->getItemType() instanceof StringType;
    }

    public function isMixedOfSpecificOverride(ArrayType $arrayType, PhpDocInfo $phpDocInfo): bool
    {
        if (! $arrayType->getItemType() instanceof MixedType) {
            return false;
        }

        $currentReturnType = $phpDocInfo->getReturnType();
        return $currentReturnType instanceof ArrayType;
    }

    public function isMoreSpecificArrayTypeOverride(
        Type $newType,
        ClassMethod $classMethod,
        PhpDocInfo $phpDocInfo
    ): bool {
        if (! $newType instanceof ConstantArrayType) {
            return false;
        }

        if (! $newType->getItemType() instanceof NeverType) {
            return false;
        }

        $phpDocReturnType = $phpDocInfo->getReturnType();
        if (! $phpDocReturnType instanceof ArrayType) {
            return false;
        }

        return ! $phpDocReturnType->getItemType() instanceof VoidType;
    }

    public function isNewAndCurrentTypeBothCallable(ArrayType $newArrayType, PhpDocInfo $phpDocInfo): bool
    {
        $currentReturnType = $phpDocInfo->getReturnType();
        if (! $currentReturnType instanceof ArrayType) {
            return false;
        }

        if (! $newArrayType->getItemType()->isCallable()->yes()) {
            return false;
        }

        return $currentReturnType->getItemType()
            ->isCallable()
            ->yes();
    }
}
