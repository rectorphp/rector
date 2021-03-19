<?php

declare(strict_types=1);

namespace Rector\AttributeAwarePhpDoc\Ast\Type;

use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;

final class FullyQualifiedIdentifierTypeNode extends IdentifierTypeNode
{
    public function __toString(): string
    {
        return '\\' . $this->name;
    }
}
