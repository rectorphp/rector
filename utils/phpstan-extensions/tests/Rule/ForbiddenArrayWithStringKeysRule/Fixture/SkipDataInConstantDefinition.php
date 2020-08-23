<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Tests\Rule\ForbiddenArrayWithStringKeysRule\Fixture;

final class SkipDataInConstantDefinition
{
    public const DEFAULT_DATA = [
        'key' => 'value'
    ];
}
