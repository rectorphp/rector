<?php

namespace Rector\Doctrine\Tests\Rector\Class_\AlwaysInitializeUuidInEntityRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class PreviousStepTest
{
    /**
     * @var \Ramsey\Uuid\UuidInterface
     * @ORM\Column(type="uuid_binary", unique=true, nullable=true)
     */
    private $uuid;
}

?>
-----
<?php

namespace Rector\Doctrine\Tests\Rector\Class_\AlwaysInitializeUuidInEntityRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class PreviousStepTest
{
    /**
     * @var \Ramsey\Uuid\UuidInterface
     * @ORM\Column(type="uuid_binary", unique=true, nullable=true)
     */
    private $uuid;
    public function __construct()
    {
        $this->uuid = \Ramsey\Uuid\Uuid::uuid4();
    }
}

?>
