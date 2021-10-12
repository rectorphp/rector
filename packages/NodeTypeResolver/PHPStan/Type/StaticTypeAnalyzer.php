<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\PHPStan\Type;

use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\ConstantScalarType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\PHPStanStaticTypeMapper\TypeAnalyzer\UnionTypeAnalyzer;
final class StaticTypeAnalyzer
{
    /**
     * @var \Rector\PHPStanStaticTypeMapper\TypeAnalyzer\UnionTypeAnalyzer
     */
    private $unionTypeAnalyzer;
    public function __construct(\Rector\PHPStanStaticTypeMapper\TypeAnalyzer\UnionTypeAnalyzer $unionTypeAnalyzer)
    {
        $this->unionTypeAnalyzer = $unionTypeAnalyzer;
    }
    public function isAlwaysTruableType(\PHPStan\Type\Type $type) : bool
    {
        if ($type instanceof \PHPStan\Type\MixedType) {
            return \false;
        }
        if ($type instanceof \PHPStan\Type\Constant\ConstantArrayType) {
            return \true;
        }
        if ($type instanceof \PHPStan\Type\ArrayType) {
            return $this->isAlwaysTruableArrayType($type);
        }
        if ($type instanceof \PHPStan\Type\UnionType && $this->unionTypeAnalyzer->isNullable($type)) {
            return \false;
        }
        // always trueish
        if ($type instanceof \PHPStan\Type\ObjectType) {
            return \true;
        }
        if ($type instanceof \PHPStan\Type\ConstantScalarType && !$type instanceof \PHPStan\Type\NullType) {
            return (bool) $type->getValue();
        }
        if ($this->isScalarType($type)) {
            return \false;
        }
        return $this->isAlwaysTruableUnionType($type);
    }
    private function isScalarType(\PHPStan\Type\Type $type) : bool
    {
        if ($type instanceof \PHPStan\Type\NullType) {
            return \true;
        }
        return $type instanceof \PHPStan\Type\BooleanType || $type instanceof \PHPStan\Type\StringType || $type instanceof \PHPStan\Type\IntegerType || $type instanceof \PHPStan\Type\FloatType;
    }
    private function isAlwaysTruableUnionType(\PHPStan\Type\Type $type) : bool
    {
        if (!$type instanceof \PHPStan\Type\UnionType) {
            return \false;
        }
        foreach ($type->getTypes() as $unionedType) {
            if (!$this->isAlwaysTruableType($unionedType)) {
                return \false;
            }
        }
        return \true;
    }
    private function isAlwaysTruableArrayType(\PHPStan\Type\ArrayType $arrayType) : bool
    {
        $itemType = $arrayType->getItemType();
        return $itemType instanceof \PHPStan\Type\ConstantScalarType && $itemType->getValue();
    }
}
