<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Tests\Rule\NoClassReflectionStaticReflectionRule\Fixture;

use PHPStan\ShouldNotHappenException;

final class SkipNonReflectionNew
{
    public function check()
    {
        return new ShouldNotHappenException('Something');
    }
}
