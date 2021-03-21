<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Tests\Rule\NoInstanceOfStaticReflectionRule\Fixture;

use DateTimeInterface;

final class SkipDateTime
{
    public function find($node)
    {
        return is_a($node, DateTimeInterface::class, true);
    }
}
