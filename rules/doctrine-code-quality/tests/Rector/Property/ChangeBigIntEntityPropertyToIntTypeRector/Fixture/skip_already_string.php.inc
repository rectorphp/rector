<?php

namespace Rector\DoctrineCodeQuality\Tests\Rector\Property\ChangeBigIntEntityPropertyToIntTypeRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class SkipAlreadyString
{
    /**
     * @var string
     * @ORM\Column(type="bigint", nullable=true)
     */
    private $bigNumber;
}
