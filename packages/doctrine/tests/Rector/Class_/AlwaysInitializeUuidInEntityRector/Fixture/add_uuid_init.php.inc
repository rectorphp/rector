<?php

namespace Rector\Doctrine\Tests\Rector\Class_\AlwaysInitializeUuidInEntityRector\Fixture;

use Doctrine\ORM\Mapping as ORM;
use Rector\Doctrine\Tests\Rector\Class_\AlwaysInitializeUuidInEntityRector\Source\UuidInterface;

/**
 * @ORM\Entity
 */
class AddUuidInit
{
    /**
     * @ORM\Id
     * @var UuidInterface
     */
    private $superUuid;
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
class AddUuidInit
{
    /**
     * @ORM\Id
     * @var UuidInterface
     */
    private $superUuid;
    public function __construct()
    {
        $this->superUuid = \Ramsey\Uuid\Uuid::uuid4();
    }
}

?>
