<?php

namespace Rector\Doctrine\Tests\CodeQuality\Rector\Class_\MoveCurrentDateTimeDefaultInEntityToConstructorRector\Fixture;

use DateTimeInterface;

/**
 * @ORM\Entity()
 */
class SkipNullDefaults
{
    /**
     * @var DateTimeInterface
     *
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $when;
}
