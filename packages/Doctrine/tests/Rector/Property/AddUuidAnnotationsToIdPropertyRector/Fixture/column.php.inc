<?php

namespace Rector\Doctrine\Tests\Rector\Property\AddUuidAnnotationsToIdPropertyRector\Fixture;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Table
 * @ORM\Entity
 */
class Column
{
    /**
     * @ORM\Column(type="integer")
     */
    private $id;
}

?>
-----
<?php

namespace Rector\Doctrine\Tests\Rector\Property\AddUuidAnnotationsToIdPropertyRector\Fixture;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Table
 * @ORM\Entity
 */
class Column
{
    /**
     * @ORM\Column(type="uuid_binary")
     * @var \Ramsey\Uuid\UuidInterface
     */
    private $id;
}

?>
