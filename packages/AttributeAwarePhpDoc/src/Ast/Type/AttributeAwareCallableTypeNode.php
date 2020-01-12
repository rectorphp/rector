<?php

declare(strict_types=1);

namespace Rector\AttributeAwarePhpDoc\Ast\Type;

final class AttributeAwareCallableTypeNode extends \PHPStan\PhpDocParser\Ast\Type\CallableTypeNode implements \Rector\BetterPhpDocParser\Contract\PhpDocNode\AttributeAwareNodeInterface
{
    use \Rector\BetterPhpDocParser\Attributes\Attribute\AttributeTrait;

    public function __toString(): string
    {
        return 'callable';
    }
}
