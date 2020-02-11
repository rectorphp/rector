<?php

namespace Rector\Doctrine\Tests\Rector\Class_\AddUuidToEntityWhereMissingRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class SkipIdWithUuidBinaryType
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="uuid_binary")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
}
