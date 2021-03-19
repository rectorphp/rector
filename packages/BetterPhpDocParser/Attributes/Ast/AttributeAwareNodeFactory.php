<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Attributes\Ast;

use PHPStan\PhpDocParser\Ast\BaseNode;
use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocChildNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use Rector\AttributeAwarePhpDoc\AttributeAwareNodeFactoryCollector;
use Rector\AttributeAwarePhpDoc\Contract\AttributeNodeAwareFactory\AttributeAwareNodeFactoryAwareInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\AttributeAwareNodeInterface;

/**
 * @see \Rector\Tests\BetterPhpDocParser\Attributes\Ast\AttributeAwareNodeFactoryTest
 */
final class AttributeAwareNodeFactory
{
    /**
     * @var AttributeAwareNodeFactoryCollector
     */
    private $attributeAwareNodeFactoryCollector;

    public function __construct(AttributeAwareNodeFactoryCollector $attributeAwareNodeFactoryCollector)
    {
        $this->attributeAwareNodeFactoryCollector = $attributeAwareNodeFactoryCollector;
    }

    /**
     * @return PhpDocNode|PhpDocChildNode|PhpDocTagValueNode|AttributeAwareNodeInterface
     */
    public function createFromNode(Node $node, string $docContent): BaseNode
    {
        if ($node instanceof AttributeAwareNodeInterface) {
            return $node;
        }

        foreach ($this->attributeAwareNodeFactoryCollector->provide() as $attributeNodeAwareFactory) {
            if (! $attributeNodeAwareFactory->isMatch($node)) {
                continue;
            }

            // prevents cyclic dependency
            if ($attributeNodeAwareFactory instanceof AttributeAwareNodeFactoryAwareInterface) {
                $attributeNodeAwareFactory->setAttributeAwareNodeFactory($this);
            }

            return $attributeNodeAwareFactory->create($node, $docContent);
        }

        return $node;
    }
}
