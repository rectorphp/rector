<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Tests\Rule\NoClassReflectionStaticReflectionRule\Fixture;

use ReflectionClass;

final class NewOnExternal
{
    public function run()
    {
        return new ReflectionClass('SomeType');
    }
}
