<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Tests\Rule\NoInstanceOfStaticReflectionRule\Fixture;

final class SkipAllowedType
{
    public function check($object)
    {
        return is_a($object, \PhpParser\Node::class);
    }
}
