<?php

declare(strict_types=1);

namespace Rector\PhpAttribute;

use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpAttribute\Contract\PhpAttributableTagNodeInterface;
use Rector\PhpAttribute\Printer\PlaceholderNodeFactory;
use Rector\PostRector\Collector\NodesToAddCollector;

final class AnnotationToAttributeConverter
{
    /**
     * @var NodesToAddCollector
     */
    private $nodesToAddCollector;

    /**
     * @var PlaceholderNodeFactory
     */
    private $placeholderNodeFactory;

    public function __construct(
        NodesToAddCollector $nodesToAddCollector,
        PlaceholderNodeFactory $placeholderNodeFactory
    ) {
        $this->nodesToAddCollector = $nodesToAddCollector;
        $this->placeholderNodeFactory = $placeholderNodeFactory;
    }

    public function convertNode(Node $node): ?Node
    {
        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            return null;
        }

        // 0. has 0 nodes, nothing to change
        /** @var PhpAttributableTagNodeInterface[]&PhpDocTagValueNode[] $phpAttributableTagNodes */

        $phpAttributableTagNodes = $phpDocInfo->findAllByType(PhpAttributableTagNodeInterface::class);
        if ($phpAttributableTagNodes === []) {
            return null;
        }

        // 1. remove tags
        foreach ($phpAttributableTagNodes as $phpAttributableTagNode) {
            $phpDocInfo->removeTagValueNodeFromNode($phpAttributableTagNode);
        }

        // 2. convert annotations to attributes
        $placeholderNop = $this->placeholderNodeFactory->create($phpAttributableTagNodes);
        $this->nodesToAddCollector->addNodeBeforeNode($placeholderNop, $node);

        return $node;
    }
}
