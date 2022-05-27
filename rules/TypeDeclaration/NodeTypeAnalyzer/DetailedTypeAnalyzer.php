<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\NodeTypeAnalyzer;

use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
final class DetailedTypeAnalyzer
{
    /**
     * Use this constant to avoid overly detailed long-dragging union types across whole universe
     * @var int
     */
    private const MAX_NUMBER_OF_TYPES = 3;
    public function isTooDetailed(Type $type) : bool
    {
        if ($type instanceof UnionType) {
            return \count($type->getTypes()) > self::MAX_NUMBER_OF_TYPES;
        }
        if ($type instanceof ConstantArrayType) {
            return \count($type->getValueTypes()) > self::MAX_NUMBER_OF_TYPES;
        }
        if ($type instanceof GenericObjectType) {
            return $this->isTooDetailedGenericObjectType($type);
        }
        return \false;
    }
    private function isTooDetailedGenericObjectType(GenericObjectType $genericObjectType) : bool
    {
        if (\count($genericObjectType->getTypes()) !== 1) {
            return \false;
        }
        $genericType = $genericObjectType->getTypes()[0];
        return $this->isTooDetailed($genericType);
    }
}
