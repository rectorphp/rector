<?php

namespace Rector\Tests\DoctrineCodeQuality\Rector\Property\CorrectDefaultTypesOnEntityPropertyRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class SkipAlready
{
    /**
     * @ORM\Column(type="integer")
     */
    private $stav = 1;

    /**
     * @ORM\Column(type="boolean")
     */
    private $can = true;
}
