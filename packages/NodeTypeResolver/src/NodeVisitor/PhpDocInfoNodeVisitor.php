<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeVisitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;

final class PhpDocInfoNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @var DocBlockManipulator
     */
    private $docBlockManipulator;

    public function __construct(DocBlockManipulator $docBlockManipulator)
    {
        $this->docBlockManipulator = $docBlockManipulator;
    }

    /**
     * @return int|Node|void|null
     */
    public function enterNode(Node $node)
    {
        if (empty($node->getComments())) {
            $node->setAttribute(AttributeKey::PHP_DOC_INFO, null);
            return;
        }

        $phpDocInfo = $this->docBlockManipulator->createPhpDocInfoFromNode($node);
        $node->setAttribute(AttributeKey::PHP_DOC_INFO, $phpDocInfo);

        return $node;
    }
}
