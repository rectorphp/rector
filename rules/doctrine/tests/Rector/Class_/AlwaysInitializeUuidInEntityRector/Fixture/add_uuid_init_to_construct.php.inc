<?php

namespace Rector\Doctrine\Tests\Rector\Class_\AlwaysInitializeUuidInEntityRector\Fixture;

use Doctrine\ORM\Mapping as ORM;
use Rector\Doctrine\Tests\Rector\Class_\AlwaysInitializeUuidInEntityRector\Source\UuidInterface;

/**
 * @ORM\Entity
 */
class AddUuidInitToConstruct
{
    /**
     * @ORM\Id
     * @var UuidInterface
     */
    private $superUuid;

    public function __construct()
    {
        $one = 2;
    }
}

?>
-----
<?php

namespace Rector\Doctrine\Tests\Rector\Class_\AlwaysInitializeUuidInEntityRector\Fixture;

use Doctrine\ORM\Mapping as ORM;
use Rector\Doctrine\Tests\Rector\Class_\AlwaysInitializeUuidInEntityRector\Source\UuidInterface;

/**
 * @ORM\Entity
 */
class AddUuidInitToConstruct
{
    /**
     * @ORM\Id
     * @var UuidInterface
     */
    private $superUuid;

    public function __construct()
    {
        $this->superUuid = \Ramsey\Uuid\Uuid::uuid4();
        $one = 2;
    }
}

?>
