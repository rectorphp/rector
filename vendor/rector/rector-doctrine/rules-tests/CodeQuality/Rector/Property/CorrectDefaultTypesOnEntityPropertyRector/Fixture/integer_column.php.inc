<?php

namespace Rector\Doctrine\Tests\CodeQuality\Rector\Property\CorrectDefaultTypesOnEntityPropertyRector\Fixture;

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

namespace Rector\Doctrine\Tests\CodeQuality\Rector\Property\CorrectDefaultTypesOnEntityPropertyRector\Fixture;

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
