<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\ValueObject;

use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
final class NestedArrayType
{
    /**
     * @readonly
     */
    private Type $type;
    /**
     * @readonly
     */
    private int $arrayNestingLevel;
    /**
     * @readonly
     */
    private ?Type $keyType = null;
    public function __construct(Type $type, int $arrayNestingLevel, ?Type $keyType = null)
    {
        $this->type = $type;
        $this->arrayNestingLevel = $arrayNestingLevel;
        $this->keyType = $keyType;
    }
    public function getType() : Type
    {
        return $this->type;
    }
    public function getArrayNestingLevel() : int
    {
        return $this->arrayNestingLevel;
    }
    public function getKeyType() : Type
    {
        if ($this->keyType instanceof Type) {
            return $this->keyType;
        }
        return new MixedType();
    }
}
