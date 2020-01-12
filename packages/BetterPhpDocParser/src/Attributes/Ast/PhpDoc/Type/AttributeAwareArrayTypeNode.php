<?php

declare(strict_types=1);

namespace Rector\AttributeAwarePhpDoc\Ast\PhpDoc\Type;

use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use Rector\BetterPhpDocParser\Attributes\Attribute\AttributeTrait;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\AttributeAwareNodeInterface;

final class AttributeAwareArrayTypeNode extends ArrayTypeNode implements AttributeAwareNodeInterface
{
    use AttributeTrait;
}
