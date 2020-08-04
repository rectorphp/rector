<?php

namespace Rector\Naming\Tests\Rector\ClassMethod\RenameParamToMatchTypeRector\Fixture;

use DateTime;

class SkipCreatedAtParam
{
    public function __construct(DateTime $createdAt)
    {
        $localValue = $createdAt;
    }
}
