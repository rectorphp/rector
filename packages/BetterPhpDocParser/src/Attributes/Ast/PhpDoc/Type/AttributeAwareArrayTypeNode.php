<?php declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\Type;

use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use Rector\BetterPhpDocParser\Attributes\Attribute\AttributeTrait;
use Rector\BetterPhpDocParser\Attributes\Contract\Ast\AttributeAwareNodeInterface;

final class AttributeAwareArrayTypeNode extends ArrayTypeNode implements AttributeAwareNodeInterface
{
    use AttributeTrait;
}
