<?php

declare(strict_types=1);

namespace Rector\PhpAttribute;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpAttribute\Contract\PhpAttributableTagNodeInterface;
use Rector\PhpAttribute\Printer\PhpAttributteGroupFactory;

final class AnnotationToAttributeConverter
{
    /**
     * @var PhpAttributteGroupFactory
     */
    private $phpAttributteGroupFactory;

    public function __construct(PhpAttributteGroupFactory $phpAttributteGroupFactory)
    {
        $this->phpAttributteGroupFactory = $phpAttributteGroupFactory;
    }

    /**
     * @param Class_|ClassMethod|Property $node
     */
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
        $node->attrGroups = $this->phpAttributteGroupFactory->create($phpAttributableTagNodes);

        return $node;
    }
}
