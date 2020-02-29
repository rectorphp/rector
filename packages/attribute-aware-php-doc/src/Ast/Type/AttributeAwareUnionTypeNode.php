<?php

declare(strict_types=1);

namespace Rector\AttributeAwarePhpDoc\Ast\Type;

use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use Rector\BetterPhpDocParser\Attributes\Attribute\AttributeTrait;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\AttributeAwareNodeInterface;

final class AttributeAwareUnionTypeNode extends UnionTypeNode implements AttributeAwareNodeInterface
{
    use AttributeTrait;

    /**
     * Preserve common format
     */
    public function __toString(): string
    {
        // @todo keep original format
        dump(parent::__toString());
        die;

        return implode('|', $this->types);
    }
}
