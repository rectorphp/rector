<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use Rector\BetterPhpDocParser\DataProvider\CurrentTokenIteratorProvider;
use Rector\BetterPhpDocParser\PhpDocInfo\TokenIteratorFactory;
use Rector\BetterPhpDocParser\PhpDocParser\ParentNodeTraverser;
use Rector\BetterPhpDocParser\ValueObject\Parser\BetterTokenIterator;
use Symplify\SimplePhpDocParser\Contract\PhpDocNodeVisitorInterface;
use Symplify\SimplePhpDocParser\PhpDocNodeTraverser;

/**
 * @see \Rector\Tests\BetterPhpDocParser\PhpDocNodeMapperTest
 */
final class PhpDocNodeMapper
{
    /**
     * @var PhpDocNodeTraverser
     */
    private $phpDocNodeTraverser;

    /**
     * @var TokenIteratorFactory
     */
    private $tokenIteratorFactory;

    /**
     * @var ParentNodeTraverser
     */
    private $parentNodeTraverser;

    /**
     * @var PhpDocNodeVisitorInterface[]
     */
    private $phpDocNodeVisitors = [];

    /**
     * @var CurrentTokenIteratorProvider
     */
    private $currentTokenIteratorProvider;

    /**
     * @param PhpDocNodeVisitorInterface[] $phpDocNodeVisitors
     */
    public function __construct(
        PhpDocNodeTraverser $phpDocNodeTraverser,
        TokenIteratorFactory $tokenIteratorFactory,
        ParentNodeTraverser $parentNodeTraverser,
        CurrentTokenIteratorProvider $currentTokenIteratorProvider,
        array $phpDocNodeVisitors
    ) {
        $this->phpDocNodeTraverser = $phpDocNodeTraverser;
        $this->tokenIteratorFactory = $tokenIteratorFactory;
        $this->parentNodeTraverser = $parentNodeTraverser;
        $this->phpDocNodeVisitors = $phpDocNodeVisitors;
        $this->currentTokenIteratorProvider = $currentTokenIteratorProvider;
    }

    /**
     * @param mixed[] $tokens
     */
    public function transform(PhpDocNode $phpDocNode, array $tokens): void
    {
        $this->currentTokenIteratorProvider->setBetterTokenIterator(new BetterTokenIterator($tokens));

        $this->parentNodeTraverser->transform($phpDocNode);

        $phpDocNodeTraverser = new PhpDocNodeTraverser();
        foreach ($this->phpDocNodeVisitors as $phpDocNodeVisitor) {
            $phpDocNodeTraverser->addPhpDocNodeVisitor($phpDocNodeVisitor);
        }

        $phpDocNodeTraverser->traverse($phpDocNode);
    }
}
