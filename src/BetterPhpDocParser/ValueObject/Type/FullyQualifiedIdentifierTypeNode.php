<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\ValueObject\Type;

use Override;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Stringable;
final class FullyQualifiedIdentifierTypeNode extends IdentifierTypeNode
{
    #[Override]
    public function __toString(): string
    {
        return '\\' . ltrim($this->name, '\\');
    }
}
