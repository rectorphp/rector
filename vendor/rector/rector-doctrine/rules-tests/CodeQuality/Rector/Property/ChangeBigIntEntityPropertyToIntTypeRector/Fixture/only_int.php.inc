<?php

namespace Rector\Doctrine\Tests\CodeQuality\Rector\Property\ChangeBigIntEntityPropertyToIntTypeRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class OnlyInt
{
    /**
     * @var int
     * @ORM\Column(type="bigint", nullable=true)
     */
    private $bigNumber;
}

?>
-----
<?php

namespace Rector\Doctrine\Tests\CodeQuality\Rector\Property\ChangeBigIntEntityPropertyToIntTypeRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class OnlyInt
{
    /**
     * @var string
     * @ORM\Column(type="bigint", nullable=true)
     */
    private $bigNumber;
}

?>
