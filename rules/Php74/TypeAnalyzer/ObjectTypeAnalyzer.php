<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Php74\TypeAnalyzer;

use RectorPrefix20220606\PHPStan\Type\Type;
use RectorPrefix20220606\PHPStan\Type\UnionType;
use RectorPrefix20220606\Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use RectorPrefix20220606\Rector\StaticTypeMapper\ValueObject\Type\NonExistingObjectType;
final class ObjectTypeAnalyzer
{
    public function isSpecial(Type $varType) : bool
    {
        // we are not sure what object type this is
        if ($varType instanceof NonExistingObjectType) {
            return \true;
        }
        $types = $varType instanceof UnionType ? $varType->getTypes() : [$varType];
        foreach ($types as $type) {
            if (!$type instanceof FullyQualifiedObjectType) {
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
