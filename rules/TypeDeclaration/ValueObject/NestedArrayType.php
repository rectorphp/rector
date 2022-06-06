<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\TypeDeclaration\ValueObject;

use RectorPrefix20220606\PHPStan\Type\MixedType;
use RectorPrefix20220606\PHPStan\Type\Type;
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
