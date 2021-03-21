<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Tests\Rule\NoInstanceOfStaticReflectionRule\Fixture;

use ReflectionClass;

final class SkipReflection
{
    public function find($node)
    {
        if ($node instanceof ReflectionClass) {
            return true;
        }

        return false;
    }
}
