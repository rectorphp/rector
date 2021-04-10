<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Tests\Rule\NoClassReflectionStaticReflectionRule\Fixture;

use ReflectionClass;

final class SkipAllowedType
{
    public function check()
    {
        return new ReflectionClass(\PhpParser\Node::class);
    }
}
