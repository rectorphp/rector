<?php

declare(strict_types=1);

namespace Rector\Restoration\Tests\Rector\New_\CompleteMissingDependencyInNewRector\Source;

final class RandomValueObject
{
    public function __construct(RandomDependency $randomDependency)
    {
    }
}
