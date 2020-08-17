<?php

namespace Rector\DoctrineCodeQuality\Tests\Rector\Property\ChangeBigIntEntityPropertyToIntTypeRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class RandomFloat
{
    /**
     * @var float
     * @ORM\Column(type="bigint", nullable=true)
     */
    private $bigNumber;
}

?>
-----
<?php

namespace Rector\DoctrineCodeQuality\Tests\Rector\Property\ChangeBigIntEntityPropertyToIntTypeRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class RandomFloat
{
    /**
     * @var string
     * @ORM\Column(type="bigint", nullable=true)
     */
    private $bigNumber;
}

?>
