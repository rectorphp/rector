<?php

namespace Rector\DoctrineCodeQuality\Tests\Rector\Property\CorrectDefaultTypesOnEntityPropertyRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class IntegerColumn
{
    /**
     * @ORM\Column(type="integer")
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
class IntegerColumn
{
    /**
     * @ORM\Column(type="integer")
     */
    private $stav = 1;
}

?>
