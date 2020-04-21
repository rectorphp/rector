<?php

namespace Rector\DeadCode\Tests\Rector\Class_\RemoveUnusedDoctrineEntityMethodAndPropertyRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class SkipId
{
    /**
     * @ORM\Id()
     */
    private $id;
}
