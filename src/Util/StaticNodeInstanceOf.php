<?php

declare(strict_types=1);

namespace Rector\Core\Util;

use PhpParser\Node;
use Rector\Core\Exception\ShouldNotHappenException;

/**
 * @see \Rector\Core\Tests\Util\StaticNodeInstanceOfTest
 */
final class StaticNodeInstanceOf
{
    /**
     * @param string|object|null $element
     * @param array<class-string<Node>> $nodeTypes
     */
    public static function isOneOf($element, array $nodeTypes): bool
    {
        if ($element === null) {
            return false;
        }

        // at least 2 types; use instanceof otherwise
        if (count($nodeTypes) < 2) {
            throw new ShouldNotHappenException();
        }

        foreach ($nodeTypes as $nodeType) {
            if (is_a($element, $nodeType, true)) {
                return true;
            }
        }

        return false;
    }
}
