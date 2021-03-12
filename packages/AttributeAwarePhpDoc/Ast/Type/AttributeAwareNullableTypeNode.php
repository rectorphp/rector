<?php

declare(strict_types=1);

namespace Rector\AttributeAwarePhpDoc\Ast\Type;

use PHPStan\PhpDocParser\Ast\Type\NullableTypeNode;
use Rector\BetterPhpDocParser\Attributes\Attribute\AttributeTrait;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\AttributeAwareNodeInterface;

final class AttributeAwareNullableTypeNode extends NullableTypeNode implements AttributeAwareNodeInterface
{
    use AttributeTrait;
}
