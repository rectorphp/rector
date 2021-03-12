<?php

namespace Rector\Naming\Tests\Rector\ClassMethod\RenameVariableToMatchNewTypeRector\Fixture;

use Rector\Naming\Tests\Rector\ClassMethod\RenameVariableToMatchNewTypeRector\Source\DreamSearch;

final class SkipTwoDreams
{
    public function run()
    {
        $search = new DreamSearch();
        $search->reachIt();

        $anotherDream = new DreamSearch();
        $anotherDream->reachIt();
    }
}
