<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use Rector\BetterPhpDocParser\Contract\BasePhpDocNodeVisitorInterface;
use Rector\BetterPhpDocParser\DataProvider\CurrentTokenIteratorProvider;
use Rector\BetterPhpDocParser\ValueObject\Parser\BetterTokenIterator;
use RectorPrefix20210510\Symplify\SimplePhpDocParser\Contract\PhpDocNodeVisitorInterface;
use RectorPrefix20210510\Symplify\SimplePhpDocParser\PhpDocNodeTraverser;
use RectorPrefix20210510\Symplify\SimplePhpDocParser\PhpDocNodeVisitor\CloningPhpDocNodeVisitor;
use RectorPrefix20210510\Symplify\SimplePhpDocParser\PhpDocNodeVisitor\ParentConnectingPhpDocNodeVisitor;
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
    public function __construct(CurrentTokenIteratorProvider $currentTokenIteratorProvider, ParentConnectingPhpDocNodeVisitor $parentConnectingPhpDocNodeVisitor, CloningPhpDocNodeVisitor $cloningPhpDocNodeVisitor, array $phpDocNodeVisitors)
    {
        $this->phpDocNodeVisitors = $phpDocNodeVisitors;
        $this->currentTokenIteratorProvider = $currentTokenIteratorProvider;
        $this->parentConnectingPhpDocNodeVisitor = $parentConnectingPhpDocNodeVisitor;
        $this->cloningPhpDocNodeVisitor = $cloningPhpDocNodeVisitor;
    }
    public function transform(PhpDocNode $phpDocNode, BetterTokenIterator $betterTokenIterator) : void
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
