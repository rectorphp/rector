<?php

declare(strict_types=1);

namespace Rector\AttributeAwarePhpDoc\Ast\Type;

use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use Rector\BetterPhpDocParser\Attributes\Attribute\AttributeTrait;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\AttributeAwareNodeInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\TypeAwareTagValueNodeInterface;

final class AttributeAwareGenericTypeNode extends GenericTypeNode implements AttributeAwareNodeInterface, TypeAwareTagValueNodeInterface
{
    use AttributeTrait;

    public function __toString(): string
    {
        if ($this->genericTypes !== []) {
            return parent::__toString();
        }

        return (string) $this->type;
    }
}
