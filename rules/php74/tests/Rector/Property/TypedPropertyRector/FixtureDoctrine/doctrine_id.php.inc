<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class DoctrineId
{
    /**
     * @ORM\Id()
     * @var int
     */
    private $id;
}

?>
-----
<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class DoctrineId
{
    /**
     * @ORM\Id()
     */
    private int $id;
}

?>
