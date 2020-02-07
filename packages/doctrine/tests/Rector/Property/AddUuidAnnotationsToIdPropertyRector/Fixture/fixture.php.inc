<?php

namespace Rector\Doctrine\Tests\Rector\Property\AddUuidAnnotationsToIdPropertyRector\Fixture;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Table
 * @ORM\Entity
 */
class SomeFixture
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Type("int")
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
class SomeFixture
{
    /**
     * @var \Ramsey\Uuid\UuidInterface
     * @ORM\Id
     * @ORM\Column(type="uuid_binary")
     * @Serializer\Type("string")
     */
    private $id;
}

?>
