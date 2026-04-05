<?php

declare (strict_types=1);
namespace Rector\StaticTypeMapper\ValueObject\Type;

use Override;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
final class NonExistingObjectType extends ObjectType
{
    #[Override]
    public function equals(Type $type): bool
    {
        $isEqual = parent::equals($type);
        if ($isEqual) {
            return \true;
        }
        if ($type instanceof self || get_class($type) === ObjectType::class) {
            return $type->getClassName() === $this->getClassName();
        }
        return \false;
    }
}
