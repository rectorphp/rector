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
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\TypeDeclaration\TypeNormalizer;

final class AdvancedArrayAnalyzer
{
    /**
     * @var TypeNormalizer
     */
    private $typeNormalizer;

    public function __construct(TypeNormalizer $typeNormalizer)
    {
        $this->typeNormalizer = $typeNormalizer;
    }

    public function isClassStringArrayByStringArrayOverride(ArrayType $arrayType, ClassMethod $classMethod): bool
    {
        if (! $arrayType instanceof ConstantArrayType) {
            return false;
        }

        $arrayType = $this->typeNormalizer->convertConstantArrayTypeToArrayType($arrayType);
        if ($arrayType === null) {
            return false;
        }

        $currentReturnType = $this->getNodeReturnPhpDocType($classMethod);
        if (! $currentReturnType instanceof ArrayType) {
            return false;
        }

        if (! $currentReturnType->getItemType() instanceof ClassStringType) {
            return false;
        }

        return $arrayType->getItemType() instanceof StringType;
    }

    public function isMixedOfSpecificOverride(ArrayType $arrayType, ClassMethod $classMethod): bool
    {
        if (! $arrayType->getItemType() instanceof MixedType) {
            return false;
        }

        $currentReturnType = $this->getNodeReturnPhpDocType($classMethod);
        return $currentReturnType instanceof ArrayType;
    }

    public function isMoreSpecificArrayTypeOverride(Type $newType, ClassMethod $classMethod): bool
    {
        if (! $newType instanceof ConstantArrayType) {
            return false;
        }

        if (! $newType->getItemType() instanceof NeverType) {
            return false;
        }

        $phpDocReturnType = $this->getNodeReturnPhpDocType($classMethod);
        if (! $phpDocReturnType instanceof ArrayType) {
            return false;
        }

        return ! $phpDocReturnType->getItemType() instanceof VoidType;
    }

    public function isNewAndCurrentTypeBothCallable(ArrayType $newArrayType, ClassMethod $classMethod): bool
    {
        $currentReturnType = $this->getNodeReturnPhpDocType($classMethod);
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

    private function getNodeReturnPhpDocType(ClassMethod $classMethod): ?Type
    {
        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $classMethod->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            return null;
        }

        return $phpDocInfo->getReturnType();
    }
}
