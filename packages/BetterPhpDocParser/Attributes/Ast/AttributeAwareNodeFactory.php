<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Attributes\Ast;

use PHPStan\PhpDocParser\Ast\BaseNode;
use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocChildNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayShapeItemNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use Rector\AttributeAwarePhpDoc\Ast\Type\AttributeAwareArrayShapeItemNode;
use Rector\AttributeAwarePhpDoc\Ast\Type\AttributeAwareUnionTypeNode;
use Rector\AttributeAwarePhpDoc\Contract\AttributeNodeAwareFactory\AttributeAwareNodeFactoryAwareInterface;
use Rector\AttributeAwarePhpDoc\Contract\AttributeNodeAwareFactory\AttributeNodeAwareFactoryInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\AttributeAwareNodeInterface;
use Symplify\SimplePhpDocParser\PhpDocNodeTraverser;

/**
 * @see \Rector\Tests\BetterPhpDocParser\Attributes\Ast\AttributeAwareNodeFactoryTest
 */
final class AttributeAwareNodeFactory
{
    /**
     * @var AttributeNodeAwareFactoryInterface[]
     */
    private $attributeAwareNodeFactories = [];

    /**
     * @var PhpDocNodeTraverser
     */
    private $phpDocNodeTraverser;

    /**
     * @param AttributeNodeAwareFactoryInterface[] $attributeAwareNodeFactories
     */
    public function __construct(array $attributeAwareNodeFactories, PhpDocNodeTraverser $phpDocNodeTraverser)
    {
        $this->attributeAwareNodeFactories = $attributeAwareNodeFactories;
        $this->phpDocNodeTraverser = $phpDocNodeTraverser;
    }

    /**
     * @return PhpDocNode|PhpDocChildNode|PhpDocTagValueNode|AttributeAwareNodeInterface
     */
    public function createFromNode(Node $node, string $docContent): BaseNode
    {
        $node = $this->phpDocNodeTraverser->traverseWithCallable($node, $docContent, function (
            $node,
            string $docContent
        ) {
            if ($node instanceof UnionTypeNode && ! $node instanceof AttributeAwareUnionTypeNode) {
                return new AttributeAwareUnionTypeNode($node->types, $docContent);
            }

            if ($node instanceof ArrayShapeItemNode && ! $node instanceof AttributeAwareArrayShapeItemNode) {
                return new AttributeAwareArrayShapeItemNode(
                    $node->keyName,
                    $node->optional,
                    $node->valueType,
                    $docContent
                );
            }
            return $node;
        });

        if ($node instanceof AttributeAwareNodeInterface) {
            return $node;
        }

        foreach ($this->attributeAwareNodeFactories as $attributeAwareNodeFactory) {
            if (! $attributeAwareNodeFactory->isMatch($node)) {
                continue;
            }

            // prevents cyclic dependency
            if ($attributeAwareNodeFactory instanceof AttributeAwareNodeFactoryAwareInterface) {
                $attributeAwareNodeFactory->setAttributeAwareNodeFactory($this);
            }

            return $attributeAwareNodeFactory->create($node, $docContent);
        }

        return $node;
    }
}
