<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObject\Type;

use PHPStan\PhpDocParser\Ast\Type\IntersectionTypeNode;
use Stringable;

final class BracketsAwareIntersectionTypeNode extends IntersectionTypeNode implements Stringable
{
    public function __toString(): string
    {
        return implode('&', $this->types);
    }
}
