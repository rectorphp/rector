<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\ValueObject;

use PHPStan\Type\Type;
final class TernaryIfElseTypes
{
    /**
     * @readonly
     * @var \PHPStan\Type\Type
     */
    private $firstType;
    /**
     * @readonly
     * @var \PHPStan\Type\Type
     */
    private $secondType;
    public function __construct(Type $firstType, Type $secondType)
    {
        $this->firstType = $firstType;
        $this->secondType = $secondType;
    }
    public function getFirstType() : Type
    {
        return $this->firstType;
    }
    public function getSecondType() : Type
    {
        return $this->secondType;
    }
}
