<?php

declare (strict_types=1);
namespace Rector\StaticTypeMapper\ValueObject\Type;

use PhpParser\Node\Name;
use PHPStan\Type\NativeTypePosition;
use PHPStan\Type\StaticType;
final class ParentStaticType extends StaticType
{
    public function toNativePhpTypeNode(NativeTypePosition $position): ?Name
    {
        return $position->isClassNameAllowed() ? new Name('parent') : null;
    }
}
