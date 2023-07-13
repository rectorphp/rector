<?php

namespace Rector\Doctrine\Tests\CodeQuality\Rector\ClassMethod\MakeEntitySetterNullabilityInSyncWithPropertyRector\Fixture;

use Rector\Doctrine\Tests\CodeQuality\Rector\ClassMethod\MakeEntitySetterNullabilityInSyncWithPropertyRector\Fixture\NullableEntity;

/**
 * @ORM\Entity()
 */
class ManyToOneNullable
{
    /**
     * @ORM\ManyToOne(targetEntity="NullableEntity")
     * @ORM\JoinColumn(nullable=true)
     */
    private $nullableEntity;

    public function setNullableEntity(NullableEntity $nullableEntity)
    {
        $this->nullableEntity = $nullableEntity;
    }
}

?>
