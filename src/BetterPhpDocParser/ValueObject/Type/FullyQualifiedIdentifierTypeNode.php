<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\ValueObject\Type;

use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Stringable;
final class FullyQualifiedIdentifierTypeNode extends IdentifierTypeNode
{
    public function __toString() : string
    {
        return '\\' . \ltrim($this->name, '\\');
    }
}
