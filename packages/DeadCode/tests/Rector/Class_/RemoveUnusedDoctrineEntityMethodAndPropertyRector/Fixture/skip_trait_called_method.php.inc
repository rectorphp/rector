<?php

namespace Rector\DeadCode\Tests\Rector\Class_\RemoveUnusedDoctrineEntityMethodAndPropertyRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Insider
{
    /**
     * @ORM\Column
     */
    private $name;

    public function getName()
    {
        return $this->name;
    }
}

trait SkipTraitCalledMethod
{
    public function callOnMe(Insider $entity)
    {
        $entity->getName();
    }
}

class SkipTraitCalledMethodAddict
{
    use SkipTraitCalledMethod;
}
