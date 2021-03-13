<?php

declare(strict_types=1);

namespace Rector\AttributeAwarePhpDoc\ValueObjectFactory\Type;

use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use Rector\AttributeAwarePhpDoc\Contract\DecoratingNodeFactoryInterface;
use Rector\AttributeAwarePhpDoc\ValueObject\Type\BetterArrayTypeNode;

final class BetterArrayTypeNodeFactory implements DecoratingNodeFactoryInterface
{
    public function isMatch(\PHPStan\PhpDocParser\Ast\Node $baseNode): bool
    {
        return $baseNode instanceof ArrayTypeNode;
    }

    /**
     * @param ArrayTypeNode $baseNode
     */
    public function create(\PHPStan\PhpDocParser\Ast\Node $baseNode, string $docContent): \PHPStan\PhpDocParser\Ast\Node
    {
        return new BetterArrayTypeNode($baseNode->type);
    }
}
