<?php

namespace Rector\Doctrine\Tests\CodeQuality\Rector\Class_\MoveCurrentDateTimeDefaultInEntityToConstructorRector\Fixture;

use DateTimeInterface;

/**
 * @ORM\Entity()
 */
class SkipAlreadyDefaultConstructor
{
    /**
     * @var DateTimeInterface
     *
     * @ORM\Column(type="datetime", nullable=false, options={"default"="now()"})
     */
    private $when = 'now()';

    public function __construct()
    {
        $this->when = \Nette\Utils\DateTime::from('now');
    }
}
