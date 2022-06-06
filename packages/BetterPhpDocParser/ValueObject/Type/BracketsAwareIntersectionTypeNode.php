<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\BetterPhpDocParser\ValueObject\Type;

use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\Type\IntersectionTypeNode;
use Stringable;
final class BracketsAwareIntersectionTypeNode extends IntersectionTypeNode
{
    public function __toString() : string
    {
        return \implode('&', $this->types);
    }
}
