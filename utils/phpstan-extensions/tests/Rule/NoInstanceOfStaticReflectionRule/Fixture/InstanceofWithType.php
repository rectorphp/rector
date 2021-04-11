<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Tests\Rule\NoInstanceOfStaticReflectionRule\Fixture;

use Hoa\Math\Sampler\Random;

final class InstanceofWithType
{
    public function check($object)
    {
        if ($object instanceof Random) {
            return true;
        }
    }
}
