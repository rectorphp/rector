<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\NodeTraverser;

use PhpParser\Node;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use Rector\Caching\Contract\Rector\ZeroCacheRectorInterface;
use Rector\Core\Application\ActiveRectorsProvider;
use Rector\Core\Configuration\Configuration;
use Rector\Core\Contract\Rector\PhpRectorInterface;
use Rector\Core\PhpParser\Node\CustomNode\FileNode;
use Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace;
use Rector\NodeTypeResolver\FileSystem\CurrentFileInfoProvider;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class RectorNodeTraverser extends NodeTraverser
{
    /**
     * @var PhpRectorInterface[]
     */
    private $phpRectors = [];

    /**
     * @var NodeFinder
     */
    private $nodeFinder;

    /**
     * @var CurrentFileInfoProvider
     */
    private $currentFileInfoProvider;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var bool
     */
    private $areNodeVisitorsPrepared = false;

    /**
     * @var ActiveRectorsProvider
     */
    private $activeRectorsProvider;

    public function __construct(
        Configuration $configuration,
        ActiveRectorsProvider $activeRectorsProvider,
        NodeFinder $nodeFinder,
        CurrentFileInfoProvider $currentFileInfoProvider,
    ) {
        $this->nodeFinder = $nodeFinder;
        $this->currentFileInfoProvider = $currentFileInfoProvider;
        $this->configuration = $configuration;
        $this->activeRectorsProvider = $activeRectorsProvider;
    }

    /**
     * @return Node[]
     */
    public function traverseFileNode(FileNode $fileNode): array
    {
        $this->prepareNodeVisitors();

        if (! $this->hasFileNodeRectorsEnabled()) {
            return [];
        }

        // here we only traverse file node without children, to prevent duplicatd traversion
        foreach ($this->visitors as $rector) {
            $rector->enterNode($fileNode);
        }

        return [];
    }

    /**
     * @param Node[] $nodes
     * @return Node[]
     */
    public function traverse(array $nodes): array
    {
        $this->prepareNodeVisitors();

        $hasNamespace = (bool) $this->nodeFinder->findFirstInstanceOf($nodes, Namespace_::class);
        if (! $hasNamespace && $nodes !== []) {
            $fileWithoutNamespace = new FileWithoutNamespace($nodes);
            $fileWithoutNamespace->setAttribute(
                AttributeKey::FILE_INFO,
                $this->currentFileInfoProvider->getSmartFileInfo()
            );

            return parent::traverse([$fileWithoutNamespace]);
        }

        return parent::traverse($nodes);
    }

    public function getPhpRectorCount(): int
    {
        $this->prepareNodeVisitors();
        return count($this->visitors);
    }

    public function hasZeroCacheRectors(): bool
    {
        $this->prepareNodeVisitors();
        return (bool) $this->getZeroCacheRectorCount();
    }

    public function getZeroCacheRectorCount(): int
    {
        $this->prepareNodeVisitors();

        $zeroCacheRectors = array_filter($this->phpRectors, function (PhpRectorInterface $phpRector): bool {
            return $phpRector instanceof ZeroCacheRectorInterface;
        });

        return count($zeroCacheRectors);
    }

    private function hasFileNodeRectorsEnabled(): bool
    {
        foreach ($this->visitors as $visitor) {
            if (! $visitor instanceof PhpRectorInterface) {
                continue;
            }

            if (in_array(FileNode::class, $visitor->getNodeTypes(), true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * This must happen after $this->configuration is set after ProcessCommand::execute() is run,
     * otherwise we get default false positives.
     *
     * This hack should be removed after https://github.com/rectorphp/rector/issues/5584 is resolved
     */
    private function prepareNodeVisitors(): void
    {
        if ($this->areNodeVisitorsPrepared) {
            return;
        }

        foreach ($this->activeRectorsProvider->provide() as $rector) {
            if (! $rector instanceof PhpRectorInterface) {
                continue;
            }

            if ($this->configuration->isCacheEnabled() && ! $this->configuration->shouldClearCache() && $rector instanceof ZeroCacheRectorInterface) {
                continue;
            }

            $this->addVisitor($rector);
        }

        $this->areNodeVisitorsPrepared = true;
    }
}
