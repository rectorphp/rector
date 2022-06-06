<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\NodeTypeResolver\ValueObject;

use RectorPrefix20220606\PHPStan\Type\Type;
final class OldToNewType
{
    /**
     * @readonly
     * @var \PHPStan\Type\Type
     */
    private $oldType;
    /**
     * @readonly
     * @var \PHPStan\Type\Type
     */
    private $newType;
    public function __construct(Type $oldType, Type $newType)
    {
        $this->oldType = $oldType;
        $this->newType = $newType;
    }
    public function getOldType() : Type
    {
        return $this->oldType;
    }
    public function getNewType() : Type
    {
        return $this->newType;
    }
}
