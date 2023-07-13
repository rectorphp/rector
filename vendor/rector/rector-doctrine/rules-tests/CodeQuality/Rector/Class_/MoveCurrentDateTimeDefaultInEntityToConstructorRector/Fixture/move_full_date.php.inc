<?php

namespace Rector\Doctrine\Tests\CodeQuality\Rector\Class_\MoveCurrentDateTimeDefaultInEntityToConstructorRector\Fixture;

/**
 * @ORM\Entity()
 */
class MoveFullDate
{
    /**
     * @ORM\Column(type="datetime", nullable=false, options={"default"="1900-01-01 00=00=00"})
     */
    private $when = '1900-01-01 00:00:00';
}

?>
-----
<?php

namespace Rector\Doctrine\Tests\CodeQuality\Rector\Class_\MoveCurrentDateTimeDefaultInEntityToConstructorRector\Fixture;

/**
 * @ORM\Entity()
 */
class MoveFullDate
{
    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $when;
    public function __construct()
    {
        $this->when = new \DateTime('1900-01-01 00:00:00');
    }
}

?>
