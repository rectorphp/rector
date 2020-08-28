<?php

declare(strict_types=1);

namespace Rector\AttributeAwarePhpDoc\Ast\Type;

use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\CallableTypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use Rector\BetterPhpDocParser\Attributes\Attribute\AttributeTrait;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\AttributeAwareNodeInterface;

final class AttributeAwareArrayTypeNode extends ArrayTypeNode implements AttributeAwareNodeInterface
{
    use AttributeTrait;

    public function __toString(): string
    {
        $typeAsString = (string) $this->type;

        if ($this->type instanceof CallableTypeNode) {
            return sprintf('(%s)[]', $typeAsString);
        }

        if ($this->type instanceof UnionTypeNode || $this->type instanceof ArrayTypeNode) {
            return sprintf('array<%s>', $typeAsString);
        }

        return $typeAsString . '[]';
    }
}
