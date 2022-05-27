<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\ValueObject;

use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
final class NestedArrayType
{
    /**
     * @readonly
     * @var \PHPStan\Type\Type
     */
    private $type;
    /**
     * @readonly
     * @var int
     */
    private $arrayNestingLevel;
    /**
     * @readonly
     * @var \PHPStan\Type\Type|null
     */
    private $keyType;
    public function __construct(\PHPStan\Type\Type $type, int $arrayNestingLevel, ?\PHPStan\Type\Type $keyType = null)
    {
        $this->type = $type;
        $this->arrayNestingLevel = $arrayNestingLevel;
        $this->keyType = $keyType;
    }
    public function getType() : \PHPStan\Type\Type
    {
        return $this->type;
    }
    public function getArrayNestingLevel() : int
    {
        return $this->arrayNestingLevel;
    }
    public function getKeyType() : \PHPStan\Type\Type
    {
        if ($this->keyType instanceof \PHPStan\Type\Type) {
            return $this->keyType;
        }
        return new \PHPStan\Type\MixedType();
    }
}
