<?php

declare(strict_types=1);

namespace Rector\Tests\DeadCode\Rector\Class_\RemoveUnusedDoctrineEntityMethodAndPropertyRector\Source;

use Rector\Tests\DeadCode\Rector\Class_\RemoveUnusedDoctrineEntityMethodAndPropertyRector\Fixture\SomeEntity;

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
