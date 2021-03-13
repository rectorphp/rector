<?php

declare(strict_types=1);

namespace Rector\AttributeAwarePhpDoc\ValueObjectFactory\Type;

use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use Rector\AttributeAwarePhpDoc\Contract\DecoratingNodeFactoryInterface;

final class BetterUnionTypeNodeFactory implements DecoratingNodeFactoryInterface
{
    public function isMatch(\PHPStan\PhpDocParser\Ast\Node $baseNode): bool
    {
        return $baseNode instanceof UnionTypeNode;
    }

    /**
     * @param UnionTypeNode $baseNode
     */
    public function create(\PHPStan\PhpDocParser\Ast\Node $baseNode, string $docContent): \PHPStan\PhpDocParser\Ast\Node
    {
        return new UnionTypeNode($baseNode->types);
    }
}
