<?php

namespace Rector\Doctrine\Tests\CodeQuality\Rector\Property\CorrectDefaultTypesOnEntityPropertyRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class PointedToConstantValue
{
    public const ONE = 1;

    /**
     * @ORM\Column(type="integer")
     */
    private $stav = self::ONE;

    /**
     * @ORM\Column(type="integer")
     */
    private $stav2 = SOME_CONSTANT_VALUE;
}
