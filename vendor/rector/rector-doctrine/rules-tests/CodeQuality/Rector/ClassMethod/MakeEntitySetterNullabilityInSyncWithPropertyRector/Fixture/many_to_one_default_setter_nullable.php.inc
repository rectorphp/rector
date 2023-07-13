<?php

namespace Rector\Doctrine\Tests\CodeQuality\Rector\ClassMethod\MakeEntitySetterNullabilityInSyncWithPropertyRector\Fixture;

use Rector\Doctrine\Tests\CodeQuality\Rector\ClassMethod\MakeEntitySetterNullabilityInSyncWithPropertyRector\Fixture\DefaultEntity;

/**
 * @ORM\Entity()
 */
class ManyToOneDefaultSetterNullable
{
    /**
     * @ORM\ManyToOne(targetEntity="DefaultEntity")
     */
    private $defaultEntity;

    public function setDefaultEntity(?DefaultEntity $defaultEntity)
    {
        $this->defaultEntity = $defaultEntity;
    }
}

?>
