<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\ValueObject;

use PHPStan\Type\MixedType;
use PHPStan\Type\Type;

final class NestedArrayType
{
    public function __construct(
        private Type $type,
        private int $arrayNestingLevel,
        private ?Type $keyType = null
    ) {
    }

    public function getType(): Type
    {
        return $this->type;
    }

    public function getArrayNestingLevel(): int
    {
        return $this->arrayNestingLevel;
    }

    public function getKeyType(): Type
    {
        return $this->keyType ?: new MixedType();
    }
}
