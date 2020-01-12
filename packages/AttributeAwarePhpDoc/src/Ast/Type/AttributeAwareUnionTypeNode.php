<?php

declare(strict_types=1);

namespace Rector\AttributeAwarePhpDoc\Ast\Type;

final class AttributeAwareUnionTypeNode extends \PHPStan\PhpDocParser\Ast\Type\UnionTypeNode implements \Rector\BetterPhpDocParser\Contract\PhpDocNode\AttributeAwareNodeInterface
{
    use \Rector\BetterPhpDocParser\Attributes\Attribute\AttributeTrait;

    /**
     * Preserve common format
     */
    public function __toString(): string
    {
        return implode('|', $this->types);
    }
}
