<?php

declare(strict_types=1);

namespace Rector\AttributeAwarePhpDoc\ValueObjectFactory;

use PHPStan\PhpDocParser\Ast\PhpDoc\TemplateTagValueNode;
use Rector\AttributeAwarePhpDoc\Contract\DecoratingNodeFactoryInterface;
use Rector\AttributeAwarePhpDoc\ValueObject\BetterTemplateTagValueNode;

final class BetterTemplateTagValueNodeFactory implements DecoratingNodeFactoryInterface
{
    public function isMatch(\PHPStan\PhpDocParser\Ast\Node $baseNode): bool
    {
        return $baseNode instanceof TemplateTagValueNode;
    }

    /**
     * @param TemplateTagValueNode $baseNode
     */
    public function create(\PHPStan\PhpDocParser\Ast\Node $baseNode, string $docContent): \PHPStan\PhpDocParser\Ast\Node
    {
        return new BetterTemplateTagValueNode(
            $baseNode->name,
            $baseNode->bound,
            $baseNode->description,
            $docContent
        );
    }
}
