<?php

declare(strict_types=1);

namespace Rector\Tests\Restoration\Rector\New_\CompleteMissingDependencyInNewRector\Source;

final class RandomValueObject
{
    public function __construct(RandomDependency $randomDependency)
    {
    }
}
