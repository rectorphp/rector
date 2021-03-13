<?php

declare(strict_types=1);

namespace Rector\AttributeAwarePhpDoc\ValueObjectFactory;

use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use Rector\AttributeAwarePhpDoc\Contract\DecoratingNodeFactoryInterface;
use Rector\AttributeAwarePhpDoc\ValueObject\BetterPhpDocNode;

final class BetterPhpDocNodeFactory implements DecoratingNodeFactoryInterface
{
    public function isMatch(Node $node): bool
    {
        return $node instanceof PhpDocNode;
    }

    /**
     * @param PhpDocNode $baseNode
     */
    public function create(Node $baseNode, string $docContent): Node
    {
        return new BetterPhpDocNode($baseNode->children);
    }
}
