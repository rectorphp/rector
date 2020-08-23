<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Tests\Rule\ForbiddenArrayWithStringKeysRule\Fixture;

final class ArrayWithStrings
{
    public function run()
    {
        return [
            'key' => 'value'
        ];
    }
}
