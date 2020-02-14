<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Class_\RemoveUnusedDoctrineEntityMethodAndPropertyRector\Source;

use Rector\DeadCode\Tests\Rector\Class_\RemoveUnusedDoctrineEntityMethodAndPropertyRector\Fixture\SomeEntity;

class SomeEntityProvider
{
    /**
     * @return SomeEntity[]
     */
    public function provide(): array
    {
        return [new SomeEntity()];
    }
}
