<?php declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc;

use PHPStan\PhpDocParser\Ast\PhpDoc\ThrowsTagValueNode;
use Rector\BetterPhpDocParser\Attributes\Attribute\AttributeTrait;
use Rector\BetterPhpDocParser\Attributes\Contract\Ast\AttributeAwareNodeInterface;

final class AttributeAwareThrowsTagValueNode extends ThrowsTagValueNode implements AttributeAwareNodeInterface
{
    use AttributeTrait;
}
