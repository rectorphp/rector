<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Tests\Rule\ForbiddenArrayWithStringKeysRule\Fixture;

final class SkipDataInTest
{
    public function run()
    {
        return [
            'key' => 'value'
        ];
    }
}
