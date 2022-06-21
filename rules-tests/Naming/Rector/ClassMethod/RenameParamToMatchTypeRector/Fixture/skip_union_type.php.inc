<?php

namespace Rector\Tests\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector\Fixture;

use Rector\Tests\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector\Source\EliteManager;

class SkipUnionType
{
    public function __construct(EliteManager|int $eventManager)
    {
        $this->eventManager = $eventManager;
    }
}

