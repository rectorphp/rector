<?php

declare (strict_types=1);
namespace Rector\Php74\TypeAnalyzer;

use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\NonExistingObjectType;
final class ObjectTypeAnalyzer
{
    public function isSpecial(\PHPStan\Type\Type $varType) : bool
    {
        // we are not sure what object type this is
        if ($varType instanceof \Rector\StaticTypeMapper\ValueObject\Type\NonExistingObjectType) {
            return \true;
        }
        $types = $varType instanceof \PHPStan\Type\UnionType ? $varType->getTypes() : [$varType];
        foreach ($types as $type) {
            if (!$type instanceof \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType) {
                continue;
            }
            if ($type->getClassName() !== 'Prophecy\\Prophecy\\ObjectProphecy') {
                continue;
            }
            return \true;
        }
        return \false;
    }
}
