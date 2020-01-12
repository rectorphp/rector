<?php

declare(strict_types=1);

namespace Rector\AttributeAwarePhpDoc\Ast\Type;

final class AttributeAwareNullableTypeNode extends \PHPStan\PhpDocParser\Ast\Type\NullableTypeNode implements \Rector\BetterPhpDocParser\Contract\PhpDocNode\AttributeAwareNodeInterface
{
    use \Rector\BetterPhpDocParser\Attributes\Attribute\AttributeTrait;

    public function __toString(): string
    {
        return $this->type . '|null';
    }
}
