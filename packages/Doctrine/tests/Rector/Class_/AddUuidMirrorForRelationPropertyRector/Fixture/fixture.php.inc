<?php

namespace Rector\Doctrine\Tests\Rector\Class_\AddUuidMirrorForRelationPropertyRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class SomeEntity
{
    /**
     * @ORM\ManyToOne(targetEntity="Rector\Doctrine\Tests\Rector\Class_\AddUuidMirrorForRelationPropertyRector\Fixture\AnotherEntity", cascade={"persist", "merge"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $amenity;
}

/**
 * @ORM\Entity
 */
class AnotherEntity
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
}

?>
-----
<?php

namespace Rector\Doctrine\Tests\Rector\Class_\AddUuidMirrorForRelationPropertyRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class SomeEntity
{
    /**
     * @ORM\ManyToOne(targetEntity="Rector\Doctrine\Tests\Rector\Class_\AddUuidMirrorForRelationPropertyRector\Fixture\AnotherEntity", cascade={"persist", "merge"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $amenity;
    /**
     * @ORM\ManyToOne(targetEntity="Rector\Doctrine\Tests\Rector\Class_\AddUuidMirrorForRelationPropertyRector\Fixture\AnotherEntity", cascade={"persist", "merge"})
     * @ORM\JoinColumn(nullable=true, referencedColumnName="uuid")
     */
    private $amenityUuid;
}

/**
 * @ORM\Entity
 */
class AnotherEntity
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
}

?>
