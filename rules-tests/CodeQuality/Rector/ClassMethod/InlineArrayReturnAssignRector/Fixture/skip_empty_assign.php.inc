<?php

namespace Rector\Tests\CodeQuality\Rector\ClassMethod\InlineArrayReturnAssignRector\Fixture;

final class SkipVariable
{
    public function run($person2)
    {
        $person = [];

        $person[] = 'Timmy';
        $person['surname'] = 'Back';

        return $person;
    }
}
