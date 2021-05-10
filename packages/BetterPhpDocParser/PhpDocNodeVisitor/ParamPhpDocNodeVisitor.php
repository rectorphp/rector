<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeVisitor;

use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use Rector\BetterPhpDocParser\Attributes\AttributeMirrorer;
use Rector\BetterPhpDocParser\Contract\BasePhpDocNodeVisitorInterface;
use Rector\BetterPhpDocParser\ValueObject\PhpDoc\VariadicAwareParamTagValueNode;
use Symplify\SimplePhpDocParser\PhpDocNodeVisitor\AbstractPhpDocNodeVisitor;

final class ParamPhpDocNodeVisitor extends AbstractPhpDocNodeVisitor implements BasePhpDocNodeVisitorInterface
{
    public function __construct(
        private AttributeMirrorer $attributeMirrorer
    ) {
    }

    public function enterNode(Node $node): ?Node
    {
        if (! $node instanceof ParamTagValueNode) {
            return null;
        }

        if ($node instanceof VariadicAwareParamTagValueNode) {
            return null;
        }

        $variadicAwareParamTagValueNode = new VariadicAwareParamTagValueNode(
            $node->type,
            $node->isVariadic,
            $node->parameterName,
            $node->description
        );

        $this->attributeMirrorer->mirror($node, $variadicAwareParamTagValueNode);

        return $variadicAwareParamTagValueNode;
    }
}
