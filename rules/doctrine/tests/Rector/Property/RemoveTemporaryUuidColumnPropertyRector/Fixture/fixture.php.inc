<?php

namespace Rector\Doctrine\Tests\Rector\Property\RemoveTemporaryUuidColumnPropertyRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Fixture
{
    /**
     * @ORM\Column
     */
    public $id;

    /**
     * @ORM\Column
     */
    public $uuid;
}

?>
-----
<?php

namespace Rector\Doctrine\Tests\Rector\Property\RemoveTemporaryUuidColumnPropertyRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Fixture
{
    /**
     * @ORM\Column
     */
    public $id;
}

?>
