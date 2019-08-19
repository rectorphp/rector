<?php

namespace Rector\DeadCode\Tests\Rector\Class_\RemoveUnusedDoctrineEntityMethodAndPropertyRector\Fixture;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 */
class WithNamedConstructor
{
    /**
     * @ORM\Column
     */
    private $name;

    public static function create($name): self
    {
        $instance = new self();
        $instance->name = $name;

        return $instance;
    }
}

WithNamedConstructor::create('name');
