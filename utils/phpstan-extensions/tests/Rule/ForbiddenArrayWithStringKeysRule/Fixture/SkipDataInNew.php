<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Tests\Rule\ForbiddenArrayWithStringKeysRule\Fixture;

use Rector\PHPStanExtensions\Tests\Rule\ForbiddenArrayWithStringKeysRule\Source\ObjectWithOptions;

final class SkipDataInNew
{
    public function someConfiguration()
    {
        $value = new ObjectWithOptions([
            'key' => 'value'
        ]);
    }
}
