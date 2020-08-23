<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Tests\Rule\ForbiddenArrayWithStringKeysRule\Fixture;

final class SkipNonConstantString
{
    public function run()
    {
        $value = 'key';
        return [
            $value => 'value'
        ];
    }
}
