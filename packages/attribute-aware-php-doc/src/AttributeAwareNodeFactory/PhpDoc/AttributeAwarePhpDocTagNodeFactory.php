<?php

declare(strict_types=1);

namespace Rector\AttributeAwarePhpDoc\AttributeAwareNodeFactory\PhpDoc;

use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\AttributeAwarePhpDoc\Contract\AttributeNodeAwareFactory\AttributeNodeAwareFactoryInterface;
use Rector\PhpdocParserPrinter\Contract\AttributeAwareInterface;
use Rector\PhpdocParserPrinter\ValueObject\PhpDocNode\AttributeAwarePhpDocTagNode;

final class AttributeAwarePhpDocTagNodeFactory implements AttributeNodeAwareFactoryInterface
{
    public function getOriginalNodeClass(): string
    {
        return PhpDocTagNode::class;
    }

    public function isMatch(Node $node): bool
    {
        return is_a($node, PhpDocTagNode::class, true);
    }

    /**
     * @param PhpDocTagNode $node
     */
    public function create(Node $node, string $docContent): AttributeAwareInterface
    {
        return new AttributeAwarePhpDocTagNode($node->name, $node->value);
    }
}
