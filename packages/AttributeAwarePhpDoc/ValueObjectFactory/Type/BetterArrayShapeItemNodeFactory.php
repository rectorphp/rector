<?php

declare(strict_types=1);

namespace Rector\AttributeAwarePhpDoc\ValueObjectFactory\Type;

use PHPStan\PhpDocParser\Ast\Type\ArrayShapeItemNode;
use Rector\AttributeAwarePhpDoc\Contract\DecoratingNodeFactoryInterface;
use Rector\AttributeAwarePhpDoc\ValueObject\Type\BetterArrayShapeItemNode;

final class BetterArrayShapeItemNodeFactory implements DecoratingNodeFactoryInterface
{
    public function isMatch(\PHPStan\PhpDocParser\Ast\Node $baseNode): bool
    {
        return $baseNode instanceof ArrayShapeItemNode;
    }

    /**
     * @param ArrayShapeItemNode $baseNode
     */
    public function create(\PHPStan\PhpDocParser\Ast\Node $baseNode, string $docContent): \PHPStan\PhpDocParser\Ast\Node
    {
        return new BetterArrayShapeItemNode(
            $baseNode->keyName,
            $baseNode->optional,
            $baseNode->valueType,
            $docContent
        );
    }
}
