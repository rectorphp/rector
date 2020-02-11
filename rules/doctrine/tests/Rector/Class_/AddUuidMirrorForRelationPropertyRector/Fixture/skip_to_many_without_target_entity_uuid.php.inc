<?php

namespace Rector\Doctrine\Tests\Rector\Class_\AddUuidMirrorForRelationPropertyRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="wohoo")
 */
class SkipTooManyWithoutTargetEntityUuid
{
    /**
     * @ORM\ManyToMany(targetEntity="Rector\Doctrine\Tests\Rector\Class_\AddUuidMirrorForRelationPropertyRector\Fixture\FooEntityWithoutUuid", cascade={"persist", "merge"})
     */
    private $amenity;
}

/**
 * @ORM\Entity
 */
class FooEntityWithoutUuid
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
}
