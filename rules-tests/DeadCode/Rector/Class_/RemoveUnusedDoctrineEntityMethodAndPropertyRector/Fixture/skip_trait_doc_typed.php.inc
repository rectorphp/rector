<?php

namespace Rector\Tests\DeadCode\Rector\Class_\RemoveUnusedDoctrineEntityMethodAndPropertyRector\Fixture;

use Doctrine\ORM\Mapping as ORM;
use Rector\Tests\DeadCode\Rector\Class_\RemoveUnusedDoctrineEntityMethodAndPropertyRector\Source\SomeEntityProvider;

class SkipTraitDocTypedAddict
{
    use SkipTraitDocTyped;
}

trait SkipTraitDocTyped
{
    /**
     * @param SomeEntityProvider $someEntityProvider
     */
    public function run($someEntityProvider): void
    {
        foreach ($someEntityProvider->provide() as $someEntity) {
            $test = $someEntity->getStatus();
        }
    }
}

/**
 * @ORM\Entity
 */
class SomeEntity
{
    /**
     * @var string
     */
    private $status;

    public function getStatus(): string
    {
        return $this->status;
    }
}
