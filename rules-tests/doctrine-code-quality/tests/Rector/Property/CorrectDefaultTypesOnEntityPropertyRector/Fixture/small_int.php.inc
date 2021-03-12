<?php

namespace Rector\DoctrineCodeQuality\Tests\Rector\Property\CorrectDefaultTypesOnEntityPropertyRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class SmallInt
{
    /**
     * @ORM\Column(type="smallint")
     */
    private $stav = '1';
}

?>
-----
<?php

namespace Rector\DoctrineCodeQuality\Tests\Rector\Property\CorrectDefaultTypesOnEntityPropertyRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class SmallInt
{
    /**
     * @ORM\Column(type="smallint")
     */
    private $stav = 1;
}

?>
