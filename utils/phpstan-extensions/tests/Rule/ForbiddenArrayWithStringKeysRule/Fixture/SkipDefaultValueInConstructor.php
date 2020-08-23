<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Tests\Rule\ForbiddenArrayWithStringKeysRule\Fixture;

final class SkipDefaultValueInConstructor
{
    private $values = [];

    public function __construct()
    {
        $this->values = [
            'key' => 'value'
        ];
    }
}
