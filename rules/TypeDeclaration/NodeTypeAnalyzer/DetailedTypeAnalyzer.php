<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\TypeDeclaration\NodeTypeAnalyzer;

use RectorPrefix20220606\PHPStan\Type\Constant\ConstantArrayType;
use RectorPrefix20220606\PHPStan\Type\Generic\GenericObjectType;
use RectorPrefix20220606\PHPStan\Type\Type;
use RectorPrefix20220606\PHPStan\Type\UnionType;
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
