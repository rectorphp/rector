<?php

declare(strict_types=1);

namespace Rector\AttributeAwarePhpDoc\Ast\Type;

final class AttributeAwareIntersectionTypeNode extends \PHPStan\PhpDocParser\Ast\Type\IntersectionTypeNode implements \Rector\BetterPhpDocParser\Contract\PhpDocNode\AttributeAwareNodeInterface
{
    use \Rector\BetterPhpDocParser\Attributes\Attribute\AttributeTrait;

    public function __toString(): string
    {
        return implode('&', $this->types);
    }
}
