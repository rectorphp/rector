<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeVisitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;

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

    public function enterNode(Node $node): ?Node
    {
        // also binds to the node
        $this->phpDocInfoFactory->createFromNode($node);

        return $node;
    }
}
