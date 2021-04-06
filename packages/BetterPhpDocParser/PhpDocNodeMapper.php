<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use Rector\BetterPhpDocParser\Contract\BasePhpDocNodeVisitorInterface;
use Rector\BetterPhpDocParser\DataProvider\CurrentTokenIteratorProvider;
use Rector\BetterPhpDocParser\PhpDocNodeVisitor\CloningPhpDocNodeVisitor;
use Rector\BetterPhpDocParser\ValueObject\Parser\BetterTokenIterator;
use Symplify\SimplePhpDocParser\Contract\PhpDocNodeVisitorInterface;
use Symplify\SimplePhpDocParser\PhpDocNodeTraverser;
use Symplify\SimplePhpDocParser\PhpDocNodeVisitor\ParentConnectingPhpDocNodeVisitor;

/**
 * @see \Rector\Tests\BetterPhpDocParser\PhpDocNodeMapperTest
 */
final class PhpDocNodeMapper
{
    /**
     * @var PhpDocNodeVisitorInterface[]
     */
    private $phpDocNodeVisitors = [];

    /**
     * @var CurrentTokenIteratorProvider
     */
    private $currentTokenIteratorProvider;

    /**
     * @var ParentConnectingPhpDocNodeVisitor
     */
    private $parentConnectingPhpDocNodeVisitor;

    /**
     * @var CloningPhpDocNodeVisitor
     */
    private $cloningPhpDocNodeVisitor;

    /**
     * @param BasePhpDocNodeVisitorInterface[] $phpDocNodeVisitors
     */
    public function __construct(
        CurrentTokenIteratorProvider $currentTokenIteratorProvider,
        ParentConnectingPhpDocNodeVisitor $parentConnectingPhpDocNodeVisitor,
        CloningPhpDocNodeVisitor $cloningPhpDocNodeVisitor,
        array $phpDocNodeVisitors
    ) {
        $this->phpDocNodeVisitors = $phpDocNodeVisitors;
        $this->currentTokenIteratorProvider = $currentTokenIteratorProvider;
        $this->parentConnectingPhpDocNodeVisitor = $parentConnectingPhpDocNodeVisitor;
        $this->cloningPhpDocNodeVisitor = $cloningPhpDocNodeVisitor;
    }

    public function transform(PhpDocNode $phpDocNode, BetterTokenIterator $betterTokenIterator): void
    {
        $this->currentTokenIteratorProvider->setBetterTokenIterator($betterTokenIterator);

        $parentPhpDocNodeTraverser = new PhpDocNodeTraverser();
        $parentPhpDocNodeTraverser->addPhpDocNodeVisitor($this->parentConnectingPhpDocNodeVisitor);
        $parentPhpDocNodeTraverser->traverse($phpDocNode);

        $cloningPhpDocNodeTraverser = new PhpDocNodeTraverser();
        $cloningPhpDocNodeTraverser->addPhpDocNodeVisitor($this->cloningPhpDocNodeVisitor);
        $cloningPhpDocNodeTraverser->traverse($phpDocNode);

        $phpDocNodeTraverser = new PhpDocNodeTraverser();
        foreach ($this->phpDocNodeVisitors as $phpDocNodeVisitor) {
            $phpDocNodeTraverser->addPhpDocNodeVisitor($phpDocNodeVisitor);
        }

        $phpDocNodeTraverser->traverse($phpDocNode);
    }
}
