<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Tests\Rule\NoInstanceOfStaticReflectionRule\Fixture;

final class SkipGenericNodeType
{
    /**
     * @template T of \PhpParser\Node
     * @param T $type
     */
    public function find($node, $type)
    {
        if (is_a($node, $type, true)) {
            return true;
        }
    }
}
