<?php

namespace Rector\Tests\CodeQuality\Rector\FuncCall\ArrayKeysAndInArrayToArrayKeyExistsRector\Fixture;

final class SkipArrayKeysWithExtraArgument
{
    public function hasMoreThanOneArgument(): bool
    {
        return in_array('key', array_keys([
            'key' => ',',
            1 => ';',
        ], 'key'));
    }
}
