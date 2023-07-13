<?php

namespace Rector\Doctrine\Tests\CodeQuality\Rector\Class_\MoveCurrentDateTimeDefaultInEntityToConstructorRector\Fixture;

use DateTime;

final class SkipNullalbeProperty
{
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected ?DateTime $date = null;

    public function __construct()
    {
    }
}
