<?php

namespace Rector\Doctrine\Tests\Rector\Class_\AddUuidToEntityWhereMissingRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\InheritanceType(value="SINGLE_TABLE")
 */
abstract class ParentSingleTableInheritanceWithIdentifier
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $identifier;
}

/**
 * @ORM\Entity
 */
class AddSingleTableInheritanceAgain extends ParentSingleTableInheritanceWithIdentifier
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
abstract class ParentSingleTableInheritanceWithIdentifier
{
    public function __construct()
    {
        $this->uuid = \Ramsey\Uuid\Uuid::uuid4();
    }
    /**
     * @var \Ramsey\Uuid\UuidInterface
     * @ORM\Column (type="uuid_binary", unique=true, nullable=true)
     */
    private $uuid;
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $identifier;
}

/**
 * @ORM\Entity
 */
class AddSingleTableInheritanceAgain extends ParentSingleTableInheritanceWithIdentifier
{
}

?>
