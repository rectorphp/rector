<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Tests\Rule\RequireStringArgumentInMethodCallRule\Source;

final class AlwaysCallMeWithString
{
    public function callMe($object, $type)
    {
    }
}
