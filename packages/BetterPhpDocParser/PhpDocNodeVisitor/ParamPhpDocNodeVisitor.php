<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeVisitor;

use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDoc\VariadicAwareParamTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
use Symplify\SimplePhpDocParser\PhpDocNodeVisitor\AbstractPhpDocNodeVisitor;

final class ParamPhpDocNodeVisitor extends AbstractPhpDocNodeVisitor
{
    public function enterNode(Node $node): ?Node
    {
        if (! $node instanceof ParamTagValueNode) {
            return null;
        }

        if ($node instanceof VariadicAwareParamTagValueNode) {
            return null;
        }

        $variadicAwareParamTagValueNode = new VariadicAwareParamTagValueNode(
            $node->type, $node->isVariadic, $node->parameterName, $node->description
        );

        $parent = $node->getAttribute(PhpDocAttributeKey::PARENT);
        $variadicAwareParamTagValueNode->setAttribute(PhpDocAttributeKey::PARENT, $parent);

        $startAndEnd = $node->getAttribute(PhpDocAttributeKey::START_AND_END);
        $variadicAwareParamTagValueNode->setAttribute(PhpDocAttributeKey::START_AND_END, $startAndEnd);

        return $variadicAwareParamTagValueNode;
    }
}
