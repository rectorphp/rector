<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\ValueObject\Type;

use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Stringable;
final class EmptyGenericTypeNode extends \PHPStan\PhpDocParser\Ast\Type\GenericTypeNode
{
    public function __construct(\PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode $identifierTypeNode)
    {
        parent::__construct($identifierTypeNode, []);
    }
    public function __toString() : string
    {
        return (string) $this->type;
    }
}
