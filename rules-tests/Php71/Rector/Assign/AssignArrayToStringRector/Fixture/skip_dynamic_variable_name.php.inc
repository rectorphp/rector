<?php

namespace Rector\Tests\Php71\Rector\Assign\AssignArrayToStringRector\Fixture;

final class SkipDynamicVariableName
{
    public $description = '';

    private function addArrayElement(array $elementNames, int $value): bool
    {
        $this->{$elementNames->array}[] = $value;
    }
}
