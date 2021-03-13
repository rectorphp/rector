<?php

declare(strict_types=1);

namespace Rector\AttributeAwarePhpDoc\ValueObjectFactory\Type;

use PHPStan\PhpDocParser\Ast\Type\IntersectionTypeNode;
use Rector\AttributeAwarePhpDoc\Contract\DecoratingNodeFactoryInterface;
use Rector\AttributeAwarePhpDoc\ValueObject\Type\BetterIntersectionTypeNode;

final class BetterIntersectionTypeNodeFactory implements DecoratingNodeFactoryInterface
{
    public function isMatch(\PHPStan\PhpDocParser\Ast\Node $baseNode): bool
    {
        return $baseNode instanceof IntersectionTypeNode;
    }

    /**
     * @param IntersectionTypeNode $baseNode
     */
    public function create(\PHPStan\PhpDocParser\Ast\Node $baseNode, string $docContent): \PHPStan\PhpDocParser\Ast\Node
    {
        return new BetterIntersectionTypeNode($baseNode->types);
    }
}
