<?php

namespace Rector\Tests\Privatization\Rector\Class_\ChangeReadOnlyVariableWithDefaultValueToConstantRector\Fixture;

final class SkipWithEncapsedString
{
    public function run(int $id): array
    {
        $data                = [
            'url' => "$id",
        ];

        return $data;
    }
}
