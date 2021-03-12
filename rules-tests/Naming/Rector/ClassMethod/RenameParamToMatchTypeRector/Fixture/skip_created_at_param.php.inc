<?php

namespace Rector\Tests\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector\Fixture;

use DateTime;

class SkipCreatedAtParam
{
    public function __construct(DateTime $createdAt)
    {
        $localValue = $createdAt;
    }
}
