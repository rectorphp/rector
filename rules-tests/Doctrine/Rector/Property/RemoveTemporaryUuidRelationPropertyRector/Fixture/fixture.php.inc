<?php

namespace Rector\Doctrine\Tests\Rector\Property\RemoveTemporaryUuidRelationPropertyRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Column
{
    /**
     * @ORM\ManyToMany(targetEntity="Phonenumber")
     */
    private $apple;

    /**
     * @ORM\ManyToMany(targetEntity="Phonenumber")
     */
    private $appleUuid;
}

?>
-----
<?php

namespace Rector\Doctrine\Tests\Rector\Property\RemoveTemporaryUuidRelationPropertyRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Column
{
    /**
     * @ORM\ManyToMany(targetEntity="Phonenumber")
     */
    private $apple;
}

?>
