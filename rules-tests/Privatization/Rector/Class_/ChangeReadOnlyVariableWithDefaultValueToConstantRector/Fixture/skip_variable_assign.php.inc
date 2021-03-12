<?php

namespace Rector\Privatization\Tests\Rector\Class_\ChangeReadOnlyVariableWithDefaultValueToConstantRector\Fixture;

class SkipVariableAssign
{
    public static function createProbeItem($value, string $method, int $argumentPosition): string
    {
        $type = self::resolveValueTypeToString($value);
        $data = [$type, $method, $argumentPosition];

        return implode(';', $data) . PHP_EOL;
    }
}
