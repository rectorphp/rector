<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Tests\Rule\NoInstanceOfStaticReflectionRule\Fixture;

use Hoa\Math\Sampler\Random;

final class IsAWithType
{
    public function check($object)
    {
        return is_a($object, Random::class);
    }
}
