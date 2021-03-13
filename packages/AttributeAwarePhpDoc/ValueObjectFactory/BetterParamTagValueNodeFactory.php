<?php

declare(strict_types=1);

namespace Rector\AttributeAwarePhpDoc\ValueObjectFactory;

use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use Rector\AttributeAwarePhpDoc\Contract\DecoratingNodeFactoryInterface;
use Rector\AttributeAwarePhpDoc\ValueObject\BetterParamTagValueNode;

final class BetterParamTagValueNodeFactory implements DecoratingNodeFactoryInterface
{
    public function isMatch(Node $baseNode): bool
    {
        return $baseNode instanceof ParamTagValueNode;
    }

    /**
     * @param ParamTagValueNode $node
     */
    public function create(Node $node, string $docContent): Node
    {
        return new BetterParamTagValueNode($node->type, $node->isVariadic, $node->parameterName, $node->description);
    }
}
