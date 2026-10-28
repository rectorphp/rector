<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\ValueObject\Type;

use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
final class FullyQualifiedIdentifierTypeNode extends IdentifierTypeNode
{
    public function __construct(string $name)
    {
        parent::__construct('\\' . ltrim($name, '\\'));
    }
}
