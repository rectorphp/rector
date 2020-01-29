<?php

namespace Rector\Doctrine\Tests\Rector\Class_\AddUuidMirrorForRelationPropertyRector\Fixture;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity
 * @ORM\Table(name="wohoo")
 */
class ManyToManyWithExtraName
{
    /**
     * @ORM\ManyToOne(targetEntity="Rector\Doctrine\Tests\Rector\Class_\AddUuidMirrorForRelationPropertyRector\Fixture\BarEntity")
     * @ORM\JoinColumn(name="role_id", referencedColumnName="id", nullable=false)
     * @Serializer\Type("Rector\Doctrine\Tests\Rector\Class_\AddUuidMirrorForRelationPropertyRector\Fixture\BarEntity")
     */
    private $itemRole;
}

/**
 * @ORM\Entity
 */
class BarEntity
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
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity
 * @ORM\Table(name="wohoo")
 */
class ManyToManyWithExtraName
{
    /**
     * @ORM\ManyToOne(targetEntity="Rector\Doctrine\Tests\Rector\Class_\AddUuidMirrorForRelationPropertyRector\Fixture\BarEntity")
     * @ORM\JoinColumn(name="role_id", referencedColumnName="id", nullable=false)
     * @Serializer\Type("Rector\Doctrine\Tests\Rector\Class_\AddUuidMirrorForRelationPropertyRector\Fixture\BarEntity")
     */
    private $itemRole;
    /**
     * @ORM\ManyToOne(targetEntity="Rector\Doctrine\Tests\Rector\Class_\AddUuidMirrorForRelationPropertyRector\Fixture\BarEntity")
     * @Serializer\Type("Rector\Doctrine\Tests\Rector\Class_\AddUuidMirrorForRelationPropertyRector\Fixture\BarEntity")
     * @ORM\JoinColumn(referencedColumnName="uuid", nullable=true)
     */
    private $itemRoleUuid;
}

/**
 * @ORM\Entity
 */
class BarEntity
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
