<?php

namespace Rector\Tests\Naming\Rector\ClassMethod\RenameVariableToMatchNewTypeRector\Fixture;

use Rector\Tests\Naming\Rector\ClassMethod\RenameVariableToMatchNewTypeRector\Source\DreamSearch;

final class SkipIntentionalOverride
{
    public function run($search)
    {
        if (! $search instanceof DreamSearch) {
            $search = new DreamSearch();
        }

        return $search;
    }
}
