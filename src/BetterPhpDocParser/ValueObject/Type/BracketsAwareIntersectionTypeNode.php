<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\ValueObject\Type;

use Override;
use PHPStan\PhpDocParser\Ast\Type\IntersectionTypeNode;
use Stringable;
final class BracketsAwareIntersectionTypeNode extends IntersectionTypeNode
{
    #[Override]
    public function __toString(): string
    {
        return implode('&', $this->types);
    }
}
