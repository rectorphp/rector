<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocParser;

use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use Symplify\SimplePhpDocParser\PhpDocNodeTraverser;
use Symplify\SimplePhpDocParser\PhpDocNodeVisitor\ParentConnectingPhpDocNodeVisitor;
use Symplify\SimplePhpDocParser\ValueObject\PhpDocAttributeKey;

final class ParentNodeTraverser
{
    /**
     * @var PhpDocNodeTraverser
     */
    private $phpDocNodeTraverser;

    /**
     * @var ParentConnectingPhpDocNodeVisitor
     */
    private $parentConnectingPhpDocNodeVisitor;

    public function __construct(
        PhpDocNodeTraverser $phpDocNodeTraverser,
        ParentConnectingPhpDocNodeVisitor $parentConnectingPhpDocNodeVisitor
    ) {
        $this->phpDocNodeTraverser = $phpDocNodeTraverser;
        $this->parentConnectingPhpDocNodeVisitor = $parentConnectingPhpDocNodeVisitor;
    }

    /**
     * Connects parent types with children types
     */
    public function transform(PhpDocNode $phpDocNode): Node
    {
        $this->phpDocNodeTraverser->addPhpDocNodeVisitor($this->parentConnectingPhpDocNodeVisitor);

        $phpDocNode = $this->phpDocNodeTraverser->traverse($phpDocNode);

        dump($phpDocNode->children[0]->getAttribute(PhpDocAttributeKey::PARENT));
        die;

        return $node;
    }
}
