<?php

declare(strict_types=1);

namespace Rector\Php80\Tests\Rector\ClassMethod\OptionalParametersAfterRequiredRector\Fixture;

final class SkipCorrectOrder
{
    public function run($required, $optional = 1)
    {
    }
}
