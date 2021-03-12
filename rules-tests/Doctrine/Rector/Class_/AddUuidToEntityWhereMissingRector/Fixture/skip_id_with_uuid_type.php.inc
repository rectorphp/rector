<?php

namespace Rector\Tests\Doctrine\Rector\Class_\AddUuidToEntityWhereMissingRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class SkipIdWithUuidType
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="uuid")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
}
