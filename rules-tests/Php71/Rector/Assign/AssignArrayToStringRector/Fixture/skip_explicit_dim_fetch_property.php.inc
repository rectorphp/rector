<?php

namespace Rector\Tests\Php71\Rector\Assign\AssignArrayToStringRector\Fixture;

final class SkipExplicitDimFetchProperty
{
    public $anotherString = '';

    public function run()
    {
        $this->anotherString[1] = 1;
    }
}
