<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObject\PhpDocNode;

use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\NodeAttributes;
use Rector\Core\Exception\ShouldNotHappenException;
use Stringable;

/**
 * @deprecated
 * Just for back compatibility
 */
abstract class AbstractTagValueNode implements Node, Stringable
{
    use NodeAttributes;

    public function __toString(): string
    {
        throw new ShouldNotHappenException('Implement in child class');
    }
}
