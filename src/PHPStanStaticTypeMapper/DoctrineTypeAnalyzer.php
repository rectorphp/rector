<?php

declare (strict_types=1);
namespace Rector\PHPStanStaticTypeMapper;

use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
final class DoctrineTypeAnalyzer
{
    public function isDoctrineCollectionWithIterableUnionType(Type $type) : bool
    {
        if (!$type instanceof UnionType) {
            return \false;
        }
        $isArrayType = \false;
        $hasDoctrineCollectionType = \false;
        foreach ($type->getTypes() as $unionedType) {
            if ($this->isInstanceOfCollectionType($unionedType)) {
                $hasDoctrineCollectionType = \true;
            }
            if ($unionedType->isArray()->yes()) {
                $isArrayType = \true;
            }
        }
        if (!$hasDoctrineCollectionType) {
            return \false;
        }
        return $isArrayType;
    }
    public function isInstanceOfCollectionType(Type $type) : bool
    {
        if (!$type instanceof ObjectType) {
            return \false;
        }
        return $type->isInstanceOf('Doctrine\\Common\\Collections\\Collection')->yes();
    }
}
