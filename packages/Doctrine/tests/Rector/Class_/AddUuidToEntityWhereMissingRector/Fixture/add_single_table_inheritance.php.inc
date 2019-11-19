<?php

namespace Rector\Doctrine\Tests\Rector\Class_\AddUuidToEntityWhereMissingRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\InheritanceType(value="SINGLE_TABLE")
 */
abstract class ParentSingleTableInheritance
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
}

/**
 * @ORM\Entity
 */
class AddSingleTableInheritance extends ParentSingleTableInheritance
{
}

?>
-----
<?php

namespace Rector\Doctrine\Tests\Rector\Class_\AddUuidToEntityWhereMissingRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\InheritanceType(value="SINGLE_TABLE")
 */
abstract class ParentSingleTableInheritance
{
    /**
     * @var \Ramsey\Uuid\UuidInterface
     * @ORM\Column(type="uuid_binary", unique=true, nullable=true)
     */
    private $uuid;
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
}

/**
 * @ORM\Entity
 */
class AddSingleTableInheritance extends ParentSingleTableInheritance
{
}

?>
