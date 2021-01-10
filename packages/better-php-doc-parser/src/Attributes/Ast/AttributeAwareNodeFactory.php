<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Attributes\Ast;

use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocChildNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use Rector\AttributeAwarePhpDoc\AttributeAwareNodeFactoryCollector;
use Rector\AttributeAwarePhpDoc\Contract\AttributeNodeAwareFactory\AttributeAwareNodeFactoryAwareInterface;
use Rector\PhpdocParserPrinter\Contract\AttributeAwareInterface;
use Rector\PhpdocParserPrinter\Mapper\NodeMapper;

/**
 * @see \Rector\BetterPhpDocParser\Tests\Attributes\Ast\AttributeAwareNodeFactoryTest
 */
final class AttributeAwareNodeFactory
{
    /**
     * @var AttributeAwareNodeFactoryCollector
     */
    private $attributeAwareNodeFactoryCollector;

    /**
     * @var NodeMapper
     */
    private $nodeMapper;

    public function __construct(
        AttributeAwareNodeFactoryCollector $attributeAwareNodeFactoryCollector,
        NodeMapper $nodeMapper
    ) {
        $this->attributeAwareNodeFactoryCollector = $attributeAwareNodeFactoryCollector;
        $this->nodeMapper = $nodeMapper;
    }

    /**
     * @return PhpDocNode|PhpDocChildNode|PhpDocTagValueNode|AttributeAwareInterface
     */
    public function createFromNode(Node $node, string $docContent): AttributeAwareInterface
    {
        if ($node instanceof AttributeAwareInterface) {
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

        // new package fallback
        return $this->nodeMapper->mapNode($node);
    }
}
