<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeVisitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class PhpDocInfoNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @var PhpDocInfoFactory
     */
    private $phpDocInfoFactory;

    public function __construct(PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }

    /**
     * @return int|Node|void|null
     */
    public function enterNode(Node $node)
    {
        if ($node->getDocComment() === null) {
            $node->setAttribute(AttributeKey::PHP_DOC_INFO, null);
            return;
        }

        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
        $node->setAttribute(AttributeKey::PHP_DOC_INFO, $phpDocInfo);

        return $node;
    }
}
