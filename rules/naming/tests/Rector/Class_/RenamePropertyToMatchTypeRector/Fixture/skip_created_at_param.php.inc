<?php

namespace Rector\Naming\Tests\Rector\Class_\RenamePropertyToMatchTypeRector\Fixture;

use DateTime;

class SkipCreatedAtParam
{
    public function __construct(DateTime $createdAt)
    {
        $localValue = $createdAt;
    }
}
