<?php

namespace Rector\Doctrine\Tests\Rector\Class_\AddUuidMirrorForRelationPropertyRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class SkipIfTargetEntityHasNotUuidProperty
{
    /**
     * @ORM\ManyToOne(targetEntity="Rector\Doctrine\Tests\Rector\Class_\AddUuidMirrorForRelationPropertyRector\Source\EntityWithoutUuidProperty", cascade={"persist", "merge"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $amenity;
}
