<?php

declare (strict_types=1);
namespace Rector\StaticTypeMapper\ValueObject\Type;

use PHPStan\PhpDocParser\Ast\Type\RelativeNameNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
final class NonExistingObjectType extends ObjectType
{
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
    /**
     * printed as resolved by Rector, which keeps names written with a namespace as they are
     */
    public function toPhpDocNode(): TypeNode
    {
        return new RelativeNameNode($this->getClassName());
    }
}
