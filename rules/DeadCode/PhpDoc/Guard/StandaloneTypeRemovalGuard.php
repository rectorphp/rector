<?php

declare (strict_types=1);
namespace Rector\DeadCode\PhpDoc\Guard;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
final class StandaloneTypeRemovalGuard
{
    /**
     * @var string[]
     */
    private const ALLOWED_TYPES = ['false', 'true'];
    public function isLegal(TypeNode $typeNode, Node $node) : bool
    {
        if (!$typeNode instanceof IdentifierTypeNode) {
            return \true;
        }
        if (!$node instanceof Identifier) {
            return \true;
        }
        if ($node->toString() !== 'bool') {
            return \true;
        }
        return !\in_array($typeNode->name, self::ALLOWED_TYPES, \true);
    }
}
