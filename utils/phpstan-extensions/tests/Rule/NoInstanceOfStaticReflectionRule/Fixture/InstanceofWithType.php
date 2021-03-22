<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Tests\Rule\NoInstanceOfStaticReflectionRule\Fixture;

use Symfony\Component\Console\Command\Command;

final class InstanceofWithType
{
    public function check($object)
    {
        if ($object instanceof Command) {
            return true;
        }
    }
}
