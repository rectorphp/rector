<?php

namespace Rector\Tests\CodeQuality\Rector\ClassMethod\InlineArrayReturnAssignRector\Fixture;

final class SkipDifferentVariableSameKey
{
    public function run($person2)
    {
        $person = [];

        $person2['name'] = 'Timmy';
        $person['name'] = 'Back';

        return $person;
    }
}
