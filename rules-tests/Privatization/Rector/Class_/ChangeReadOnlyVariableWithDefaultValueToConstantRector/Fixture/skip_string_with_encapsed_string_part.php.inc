<?php

namespace Rector\Tests\Privatization\Rector\Class_\ChangeReadOnlyVariableWithDefaultValueToConstantRector\Fixture;

final class SkipStringWithEncapsedStringPart
{
    public function run(int $id): array
    {
        $data = "/$id.php";
        return $data;
    }
}
