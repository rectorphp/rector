<?php

namespace Rector\Tests\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector\Fixture;

use Rector\Tests\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector\Source\EliteManager;

final class SkipPromotedPropertyRenameFetch
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
