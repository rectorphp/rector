<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\ValueObject\Type;

use PHPStan\PhpDocParser\Ast\Type\IntersectionTypeNode;
use Stringable;
/**
 * @api used in rector-phpunit
 */
final class BracketsAwareIntersectionTypeNode extends IntersectionTypeNode
{
    public function __toString(): string
    {
        return implode('&', $this->types);
    }
}
