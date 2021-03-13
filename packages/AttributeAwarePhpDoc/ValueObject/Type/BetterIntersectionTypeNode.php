<?php

declare(strict_types=1);

namespace Rector\AttributeAwarePhpDoc\ValueObject\Type;

use PHPStan\PhpDocParser\Ast\Type\IntersectionTypeNode;

final class BetterIntersectionTypeNode extends IntersectionTypeNode
{
    public function __toString(): string
    {
        return implode('&', $this->types);
    }
}
