<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Tests\Rule\RequireStringArgumentInMethodCallRule\Fixture;

use Rector\PHPStanExtensions\Tests\Rule\RequireStringArgumentInMethodCallRule\Source\AlwaysCallMeWithString;

final class WithString
{
    public function run(): void
    {
        $alwaysCallMeWithString = new AlwaysCallMeWithString();
        $alwaysCallMeWithString->callMe(0, 'type');
    }
}
