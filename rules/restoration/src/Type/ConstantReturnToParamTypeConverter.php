<?php

declare(strict_types=1);

namespace Rector\Restoration\Type;

use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;

final class ConstantReturnToParamTypeConverter
{
    /**
     * @var TypeFactory
     */
    private $typeFactory;

    public function __construct(TypeFactory $typeFactory)
    {
        $this->typeFactory = $typeFactory;
    }

    public function convert(Type $type): ?Type
    {
        if (! $type instanceof ConstantStringType && ! $type instanceof ConstantArrayType) {
            return null;
        }

        return $this->unwrapConstantTypeToObjectType($type);
    }

    private function unwrapConstantTypeToObjectType(Type $type): ?Type
    {
        if ($type instanceof ConstantArrayType) {
            return $this->unwrapConstantTypeToObjectType($type->getItemType());
        }
        if ($type instanceof ConstantStringType) {
            return new ObjectType($type->getValue());
        }
        if ($type instanceof UnionType) {
            $types = [];
            foreach ($type->getTypes() as $unionedType) {
                $type = $this->unwrapConstantTypeToObjectType($unionedType);
                if ($type !== null) {
                    $types[] = $type;
                }
            }
            return $this->typeFactory->createMixedPassedOrUnionType($types);
        }

        return null;
    }
}
