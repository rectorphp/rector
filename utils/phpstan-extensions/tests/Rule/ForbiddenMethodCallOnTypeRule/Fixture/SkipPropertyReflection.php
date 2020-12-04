<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Tests\Rule\ForbiddenMethodCallOnTypeRule\Fixture;

use ReflectionProperty;

final class SkipPropertyReflection
{
    public function test(ReflectionProperty $reflectionProperty): void
    {
        $comments = $reflectionProperty->getDocComment();
    }
}
