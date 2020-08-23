<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Tests\Rule\ForbiddenArrayWithStringKeysRule\Fixture;

final class SkipDataInGetDefinition
{
    public function getDefinition()
    {
        return [
            'key' => 'value'
        ];
    }
}
