<?php

namespace Rector\Doctrine\Tests\Rector\Class_\AddUuidToEntityWhereMissingRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class AlreadyHasConstructor
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    public function __construct()
    {
        $this->id = 5;
    }
}

?>
-----
<?php

namespace Rector\Doctrine\Tests\Rector\Class_\AddUuidToEntityWhereMissingRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class AlreadyHasConstructor
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

    public function __construct()
    {
        $this->id = 5;
    }
}

?>
