<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\TypeComparator;

use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\UnionType;
use Rector\NodeTypeResolver\NodeTypeCorrector\GenericClassStringTypeCorrector;

final class ArrayTypeComparator
{
    /**
     * @var GenericClassStringTypeCorrector
     */
    private $genericClassStringTypeCorrector;

    public function __construct(GenericClassStringTypeCorrector $genericClassStringTypeCorrector)
    {
        $this->genericClassStringTypeCorrector = $genericClassStringTypeCorrector;
    }

    public function isSubtype(ArrayType $checkedType, ArrayType $mainType): bool
    {
        if (! $checkedType instanceof ConstantArrayType && ! $mainType instanceof ConstantArrayType) {
            return $mainType->isSuperTypeOf($checkedType)
                ->yes();
        }

        // one of them is constant here

//        $checkedKeyType = $this->genericClassStringTypeCorrector->correct($checkedType->getKeyType());
//        $mainKeyType = $this->genericClassStringTypeCorrector->correct($mainType->getKeyType());

//        dump($checkedType->getKeyType());
//        dump($mainType->getKeyType());

//        dump($checkedKeyType);
//        dump($mainKeyType);
//        die;

        if ($checkedType->getItemType() instanceof UnionType && ! $mainType->getItemType() instanceof UnionType) {
            return false;
        }

//        dump($checkedType->getItemType());
//        dump($mainType->getItemType());
//        dump($mainType->getItemType()->isSuperTypeOf($checkedType->getItemType()));
//
//        dump($mainType->isSuperTypeOf($checkedType)->yes());
//        die;

//        $checkedKeyType = $checkedType->getKeyType();
//        $mainKeyType = $mainType->getKeyType();

        return $mainType->isSuperTypeOf($checkedType)
            ->yes();

        if (! $checkedKeyType->isSuperTypeOf($mainKeyType)->yes()) {
            return false;
        }

        dump($mainKeyType);
        dump($checkedKeyType);
        die;

        return false;
    }
}
