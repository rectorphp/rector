<?php

namespace Rector\Tests\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector\FixturePhp80;

use Rector\Tests\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector\Source\EliteManager;

class SkipPromotedPropertyRenameFetch
{
    public function __construct(
        private EliteManager $eventManager
    )
    {
    }

    public function run()
    {
        return $this->eventManager;
    }
}
