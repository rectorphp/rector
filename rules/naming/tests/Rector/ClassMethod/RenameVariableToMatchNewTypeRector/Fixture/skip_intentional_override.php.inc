<?php

namespace Rector\Naming\Tests\Rector\ClassMethod\RenameVariableToMatchNewTypeRector\Fixture;

use Rector\Naming\Tests\Rector\ClassMethod\RenameVariableToMatchNewTypeRector\Source\DreamSearch;

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
