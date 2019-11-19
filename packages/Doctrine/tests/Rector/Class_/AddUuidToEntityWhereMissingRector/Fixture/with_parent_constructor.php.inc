<?php

namespace Rector\Doctrine\Tests\Rector\Class_\AddUuidToEntityWhereMissingRector\Fixture;

use Doctrine\ORM\Mapping as ORM;
use Rector\Doctrine\Tests\Rector\Class_\AddUuidToEntityWhereMissingRector\Source\BaseEntityWithConstructor;

/**
 * @ORM\Entity
 */
class WithParentConstructor extends BaseEntityWithConstructor
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="string")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
}

?>
-----
<?php

namespace Rector\Doctrine\Tests\Rector\Class_\AddUuidToEntityWhereMissingRector\Fixture;

use Doctrine\ORM\Mapping as ORM;
use Rector\Doctrine\Tests\Rector\Class_\AddUuidToEntityWhereMissingRector\Source\BaseEntityWithConstructor;

/**
 * @ORM\Entity
 */
class WithParentConstructor extends BaseEntityWithConstructor
{
    /**
     * @var \Ramsey\Uuid\UuidInterface
     * @ORM\Column(type="uuid_binary", unique=true, nullable=true)
     */
    private $uuid;
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="string")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
}

?>
