<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\ValueObject;

use PHPStan\Type\Type;
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
    public function __construct(\PHPStan\Type\Type $oldType, \PHPStan\Type\Type $newType)
    {
        $this->oldType = $oldType;
        $this->newType = $newType;
    }
    public function getOldType() : \PHPStan\Type\Type
    {
        return $this->oldType;
    }
    public function getNewType() : \PHPStan\Type\Type
    {
        return $this->newType;
    }
}
