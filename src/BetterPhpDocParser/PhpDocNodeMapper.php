<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser;

use PHPStan\PhpDocParser\Ast\NodeTraverser;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use Rector\BetterPhpDocParser\Contract\BasePhpDocNodeVisitorInterface;
use Rector\BetterPhpDocParser\DataProvider\CurrentTokenIteratorProvider;
use Rector\BetterPhpDocParser\ValueObject\Parser\BetterTokenIterator;
use Rector\PhpDocParser\PhpDocParser\PhpDocNodeVisitor\CloningPhpDocNodeVisitor;
use Rector\PhpDocParser\PhpDocParser\PhpDocNodeVisitor\ParentConnectingPhpDocNodeVisitor;
use RectorPrefix202610\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\BetterPhpDocParser\PhpDocNodeMapperTest
 */
final class PhpDocNodeMapper
{
    /**
     * @readonly
     */
    private CurrentTokenIteratorProvider $currentTokenIteratorProvider;
    /**
     * @var BasePhpDocNodeVisitorInterface[]
     * @readonly
     */
    private array $phpDocNodeVisitors;
    /**
     * @readonly
     */
    private NodeTraverser $phpDocNodeTraverser;
    /**
     * @param BasePhpDocNodeVisitorInterface[] $phpDocNodeVisitors
     */
    public function __construct(CurrentTokenIteratorProvider $currentTokenIteratorProvider, ParentConnectingPhpDocNodeVisitor $parentConnectingPhpDocNodeVisitor, CloningPhpDocNodeVisitor $cloningPhpDocNodeVisitor, array $phpDocNodeVisitors)
    {
        $this->currentTokenIteratorProvider = $currentTokenIteratorProvider;
        $this->phpDocNodeVisitors = $phpDocNodeVisitors;
        Assert::notEmpty($phpDocNodeVisitors);
        $this->phpDocNodeTraverser = new NodeTraverser(array_merge([$parentConnectingPhpDocNodeVisitor, $cloningPhpDocNodeVisitor], array_values($this->phpDocNodeVisitors)));
    }
    public function transform(PhpDocNode $phpDocNode, BetterTokenIterator $betterTokenIterator): PhpDocNode
    {
        $this->currentTokenIteratorProvider->setBetterTokenIterator($betterTokenIterator);
        [$phpDocNode] = $this->phpDocNodeTraverser->traverse([$phpDocNode]);
        Assert::isInstanceOf($phpDocNode, PhpDocNode::class);
        return $phpDocNode;
    }
}
