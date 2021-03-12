<?php

namespace Rector\Tests\Naming\Rector\ClassMethod\RenameVariableToMatchNewTypeRector\Fixture;

use Rector\Tests\Naming\Rector\ClassMethod\RenameVariableToMatchNewTypeRector\Source\DreamSearch;

final class SkipSuffix
{
    public function run($firstDreamSearch)
    {
        if (! $firstDreamSearch instanceof DreamSearch) {
            $firstDreamSearch = new DreamSearch();
        }

        return $firstDreamSearch;
    }
}
