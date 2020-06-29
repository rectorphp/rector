<?php

namespace Rector\Naming\Tests\Rector\ClassMethod\RenameVariableToMatchNewTypeRector\Fixture;

use Rector\Naming\Tests\Rector\ClassMethod\RenameVariableToMatchNewTypeRector\Source\DreamSearch;

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
