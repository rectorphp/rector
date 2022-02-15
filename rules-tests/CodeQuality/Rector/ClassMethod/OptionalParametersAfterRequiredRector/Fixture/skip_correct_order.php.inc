<?php

declare(strict_types=1);

namespace Rector\Tests\CodeQuality\Rector\ClassMethod\OptionalParametersAfterRequiredRector\Fixture;

final class SkipCorrectOrder
{
    public function run($required, $optional = 1)
    {
    }
}
