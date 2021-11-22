<?php

namespace Rector\Tests\Privatization\Rector\Class_\ChangeReadOnlyVariableWithDefaultValueToConstantRector\Fixture;

final class SkipStringWithEncapsedString
{
    public function run(int $id): array
    {
        $data = "$id";
        return $data;
    }
}
