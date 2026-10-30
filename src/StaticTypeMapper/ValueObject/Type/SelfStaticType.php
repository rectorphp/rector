<?php

declare (strict_types=1);
namespace Rector\StaticTypeMapper\ValueObject\Type;

use PhpParser\Node\Name;
use PHPStan\Type\NativeTypePosition;
use PHPStan\Type\ThisType;
final class SelfStaticType extends ThisType
{
    public function toNativePhpTypeNode(NativeTypePosition $position): ?Name
    {
        return $position->isClassNameAllowed() ? new Name('self') : null;
    }
}
