<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use Rector\BetterPhpDocParser\Contract\BasePhpDocNodeVisitorInterface;
use Rector\BetterPhpDocParser\DataProvider\CurrentTokenIteratorProvider;
use Rector\BetterPhpDocParser\PhpDocInfo\TokenIteratorFactory;
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
     * @var TokenIteratorFactory
     */
    private $tokenIteratorFactory;

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
    private $cloningNodeVisitor;

    /**
     * @param BasePhpDocNodeVisitorInterface[] $phpDocNodeVisitors
     */
    public function __construct(
        TokenIteratorFactory $tokenIteratorFactory,
        CurrentTokenIteratorProvider $currentTokenIteratorProvider,
        ParentConnectingPhpDocNodeVisitor $parentConnectingPhpDocNodeVisitor,
        CloningPhpDocNodeVisitor $cloningNodeVisitor,
        array $phpDocNodeVisitors
    ) {
        $this->tokenIteratorFactory = $tokenIteratorFactory;
        $this->phpDocNodeVisitors = $phpDocNodeVisitors;
        $this->currentTokenIteratorProvider = $currentTokenIteratorProvider;
        $this->parentConnectingPhpDocNodeVisitor = $parentConnectingPhpDocNodeVisitor;
        $this->cloningNodeVisitor = $cloningNodeVisitor;
    }

    public function transform(PhpDocNode $phpDocNode, BetterTokenIterator $betterTokenIterator): void
    {
        $this->currentTokenIteratorProvider->setBetterTokenIterator($betterTokenIterator);

        $parentPhpDocNodeTraverser = new PhpDocNodeTraverser();
        $parentPhpDocNodeTraverser->addPhpDocNodeVisitor($this->parentConnectingPhpDocNodeVisitor);
        $parentPhpDocNodeTraverser->traverse($phpDocNode);

        $cloningPhpDocNodeTraverser = new PhpDocNodeTraverser();
        $cloningPhpDocNodeTraverser->addPhpDocNodeVisitor($this->cloningNodeVisitor);
        $cloningPhpDocNodeTraverser->traverse($phpDocNode);

        $phpDocNodeTraverser = new PhpDocNodeTraverser();
        foreach ($this->phpDocNodeVisitors as $phpDocNodeVisitor) {
            $phpDocNodeTraverser->addPhpDocNodeVisitor($phpDocNodeVisitor);
        }

        $phpDocNodeTraverser->traverse($phpDocNode);
    }
}
