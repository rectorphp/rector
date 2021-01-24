<?php

namespace Rector\Php71\Tests\Rector\Assign\AssignArrayToStringRector\Fixture;

final class SkipDynamicVariableName
{
    public $description = '';

    private function addArrayElement(array $elementNames, int $value): bool
    {
        $this->{$elementNames->array}[] = $value;
    }
}
