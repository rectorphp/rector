<?php

declare(strict_types=1);

namespace Rector\AttributeAwarePhpDoc\AttributeAwareNodeFactory\PhpDoc;

use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwarePhpDocNode;
use Rector\AttributeAwarePhpDoc\Contract\AttributeNodeAwareFactory\AttributeAwareNodeFactoryAwareInterface;
use Rector\AttributeAwarePhpDoc\Contract\AttributeNodeAwareFactory\AttributeNodeAwareFactoryInterface;
use Rector\BetterPhpDocParser\Attributes\Ast\AttributeAwareNodeFactory;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\AttributeAwareNodeInterface;
use Symplify\SimplePhpDocParser\PhpDocNodeTraverser;

final class AttributeAwarePhpDocNodeFactory implements AttributeNodeAwareFactoryInterface, AttributeAwareNodeFactoryAwareInterface
{
    /**
     * @var AttributeAwareNodeFactory
     */
    private $attributeAwareNodeFactory;

    /**
     * @var PhpDocNodeTraverser
     */
    private $phpDocNodeTraverser;

    public function __construct(PhpDocNodeTraverser $phpDocNodeTraverser)
    {
        $this->phpDocNodeTraverser = $phpDocNodeTraverser;
    }

    public function getOriginalNodeClass(): string
    {
        return PhpDocNode::class;
    }

    public function isMatch(Node $node): bool
    {
        return is_a($node, PhpDocNode::class, true);
    }

    /**
     * @param PhpDocNode $node
     */
    public function create(Node $node, string $docContent): AttributeAwareNodeInterface
    {
        $this->phpDocNodeTraverser->traverseWithCallable($node, $docContent, function (Node $node) use (
            $docContent
        ): AttributeAwareNodeInterface {
            if ($node instanceof AttributeAwareNodeInterface) {
                return $node;
            }

            return $this->attributeAwareNodeFactory->createFromNode($node, $docContent);
        });

        return new AttributeAwarePhpDocNode($node->children);
    }

    public function setAttributeAwareNodeFactory(AttributeAwareNodeFactory $attributeAwareNodeFactory): void
    {
        $this->attributeAwareNodeFactory = $attributeAwareNodeFactory;
    }
}
