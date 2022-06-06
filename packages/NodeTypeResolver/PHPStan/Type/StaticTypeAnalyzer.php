<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\NodeTypeResolver\PHPStan\Type;

use RectorPrefix20220606\PHPStan\Type\ArrayType;
use RectorPrefix20220606\PHPStan\Type\BooleanType;
use RectorPrefix20220606\PHPStan\Type\Constant\ConstantArrayType;
use RectorPrefix20220606\PHPStan\Type\ConstantScalarType;
use RectorPrefix20220606\PHPStan\Type\FloatType;
use RectorPrefix20220606\PHPStan\Type\IntegerType;
use RectorPrefix20220606\PHPStan\Type\MixedType;
use RectorPrefix20220606\PHPStan\Type\NullType;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\PHPStan\Type\StringType;
use RectorPrefix20220606\PHPStan\Type\Type;
use RectorPrefix20220606\PHPStan\Type\UnionType;
use RectorPrefix20220606\Rector\PHPStanStaticTypeMapper\TypeAnalyzer\UnionTypeAnalyzer;
final class StaticTypeAnalyzer
{
    /**
     * @readonly
     * @var \Rector\PHPStanStaticTypeMapper\TypeAnalyzer\UnionTypeAnalyzer
     */
    private $unionTypeAnalyzer;
    public function __construct(UnionTypeAnalyzer $unionTypeAnalyzer)
    {
        $this->unionTypeAnalyzer = $unionTypeAnalyzer;
    }
    public function isAlwaysTruableType(Type $type) : bool
    {
        if ($type instanceof MixedType) {
            return \false;
        }
        if ($type instanceof ConstantArrayType) {
            return \true;
        }
        if ($type instanceof ArrayType) {
            return $this->isAlwaysTruableArrayType($type);
        }
        if ($type instanceof UnionType && $this->unionTypeAnalyzer->isNullable($type)) {
            return \false;
        }
        // always trueish
        if ($type instanceof ObjectType) {
            return \true;
        }
        if ($type instanceof ConstantScalarType && !$type instanceof NullType) {
            return (bool) $type->getValue();
        }
        if ($this->isScalarType($type)) {
            return \false;
        }
        return $this->isAlwaysTruableUnionType($type);
    }
    private function isScalarType(Type $type) : bool
    {
        if ($type instanceof NullType) {
            return \true;
        }
        return $type instanceof BooleanType || $type instanceof StringType || $type instanceof IntegerType || $type instanceof FloatType;
    }
    private function isAlwaysTruableUnionType(Type $type) : bool
    {
        if (!$type instanceof UnionType) {
            return \false;
        }
        foreach ($type->getTypes() as $unionedType) {
            if (!$this->isAlwaysTruableType($unionedType)) {
                return \false;
            }
        }
        return \true;
    }
    private function isAlwaysTruableArrayType(ArrayType $arrayType) : bool
    {
        $itemType = $arrayType->getItemType();
        if (!$itemType instanceof ConstantScalarType) {
            return \false;
        }
        return (bool) $itemType->getValue();
    }
}
