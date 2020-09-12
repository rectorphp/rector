<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\ValueObject;

use PHPStan\Type\MixedType;
use PHPStan\Type\Type;

final class NestedArrayType
{
    /**
     * @var int
     */
    private $arrayNestingLevel;

    /**
     * @var Type
     */
    private $type;

    /**
     * @var Type|null
     */
    private $keyType;

    public function __construct(Type $valueType, int $arrayNestingLevel, ?Type $keyType = null)
    {
        $this->type = $valueType;
        $this->arrayNestingLevel = $arrayNestingLevel;
        $this->keyType = $keyType;
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
