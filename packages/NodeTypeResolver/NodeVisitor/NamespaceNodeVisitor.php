<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Stmt\Use_;
use PhpParser\NodeVisitorAbstract;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeTypeResolver\DataCollector\UsePerFileInfoDataCollector;
use Rector\NodeTypeResolver\FileSystem\CurrentFileInfoProvider;
use Symplify\SmartFileSystem\SmartFileInfo;

final class NamespaceNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var UsePerFileInfoDataCollector
     */
    private $usePerFileInfoDataCollector;

    /**
     * @var CurrentFileInfoProvider
     */
    private $currentFileInfoProvider;

    public function __construct(
        BetterNodeFinder $betterNodeFinder,
        UsePerFileInfoDataCollector $usePerFileInfoDataCollector,
        CurrentFileInfoProvider $currentFileInfoProvider
    ) {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->usePerFileInfoDataCollector = $usePerFileInfoDataCollector;
        $this->currentFileInfoProvider = $currentFileInfoProvider;
    }

    /**
     * @param Node[] $nodes
     * @return Node[]|null
     */
    public function beforeTraverse(array $nodes): ?array
    {
        /** @var Use_[] $uses */
        $uses = $this->betterNodeFinder->findInstanceOf($nodes, Use_::class);
        if ($uses === []) {
            return null;
        }

        $firstNode = $nodes[0];

        /** @var SmartFileInfo $fileInfo */
        $fileInfo = $this->currentFileInfoProvider->getSmartFileInfo();
        $this->usePerFileInfoDataCollector->addUsesPerFileInfo($uses, $fileInfo);

        return null;
    }
}
