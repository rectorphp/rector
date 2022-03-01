<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;

class DoNotNullifyDoctrineId
{
    /**
     * @var int
     */
    #[Id, Column(type: "integer")]
    private $id;

    public function getId()
    {
        return $this->id;
    }
}

?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;

class DoNotNullifyDoctrineId
{
    #[Id, Column(type: "integer")]
    private int $id;

    public function getId()
    {
        return $this->id;
    }
}

?>
