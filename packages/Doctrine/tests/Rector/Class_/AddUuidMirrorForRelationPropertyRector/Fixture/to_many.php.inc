<?php

namespace Rector\Doctrine\Tests\Rector\Class_\AddUuidMirrorForRelationPropertyRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="wohoo")
 */
class ToMany
{
    /**
     * @ORM\ManyToMany(targetEntity="Rector\Doctrine\Tests\Rector\Class_\AddUuidMirrorForRelationPropertyRector\Fixture\FooEntity", cascade={"persist", "merge"})
     */
    private $amenity;
}

/**
 * @ORM\Entity
 */
class FooEntity
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    private $uuid;
}

?>
-----
<?php

namespace Rector\Doctrine\Tests\Rector\Class_\AddUuidMirrorForRelationPropertyRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="wohoo")
 */
class ToMany
{
    /**
     * @ORM\ManyToMany(targetEntity="Rector\Doctrine\Tests\Rector\Class_\AddUuidMirrorForRelationPropertyRector\Fixture\FooEntity", cascade={"persist", "merge"})
     */
    private $amenity;
    /**
     * @ORM\ManyToMany(targetEntity="Rector\Doctrine\Tests\Rector\Class_\AddUuidMirrorForRelationPropertyRector\Fixture\FooEntity", cascade={"persist", "merge"})
     * @ORM\JoinTable(name="tomany_fooentity_uuid", joinColumns={
     *     @ORM\JoinColumn(referencedColumnName="uuid")
     * }, inverseJoinColumns={
     *     @ORM\JoinColumn(referencedColumnName="uuid")
     * })
     */
    private $amenityUuid;
}

/**
 * @ORM\Entity
 */
class FooEntity
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    private $uuid;
}

?>
