<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\ValueObject;

use PHPStan\Type\Type;

final class NestedArrayTypeValueObject
{
    /**
     * @var int
     */
    private $arrayNestingLevel;

    /**
     * @var Type
     */
    private $type;

    public function __construct(Type $type, int $arrayNestingLevel)
    {
        $this->type = $type;
        $this->arrayNestingLevel = $arrayNestingLevel;
    }

    public function getType(): Type
    {
        return $this->type;
    }

    public function getArrayNestingLevel(): int
    {
        return $this->arrayNestingLevel;
    }
}
