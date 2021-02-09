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
<<<<<<< HEAD
use Rector\Testing\Application\EnabledRectorProvider;
=======
use Rector\Testing\Application\EnabledRectorsProvider;
>>>>>>> 47c6a521f... [Tests] Split main test case and community test case

final class RectorNodeTraverser extends NodeTraverser
{
    /**
     * @var PhpRectorInterface[]
     */
    private $allPhpRectors = [];

    /**
     * @var EnabledRectorProvider
     */
    private $enabledRectorProvider;

    /**
     * @var NodeFinder
     */
    private $nodeFinder;

    /**
     * @var CurrentFileInfoProvider
     */
    private $currentFileInfoProvider;

    public function __construct(
        EnabledRectorProvider $enabledRectorProvider,
        Configuration $configuration,
        ActiveRectorsProvider $activeRectorsProvider,
        NodeFinder $nodeFinder,
        CurrentFileInfoProvider $currentFileInfoProvider
    ) {
        /** @var PhpRectorInterface[] $phpRectors */
        $phpRectors = $activeRectorsProvider->provideByType(PhpRectorInterface::class);

        $this->allPhpRectors = $phpRectors;
        $this->enabledRectorProvider = $enabledRectorProvider;

        foreach ($phpRectors as $phpRector) {
            if ($configuration->isCacheEnabled() && ! $configuration->shouldClearCache() && $phpRector instanceof ZeroCacheRectorInterface) {
                continue;
            }

            $this->addVisitor($phpRector);
        }

        $this->nodeFinder = $nodeFinder;
        $this->currentFileInfoProvider = $currentFileInfoProvider;
    }

    /**
     * @return Node[]
     */
    public function traverseFileNode(FileNode $fileNode): array
    {
<<<<<<< HEAD
        if ($this->enabledRectorProvider->isConfigured()) {
            $this->configureEnabledRectorOnly();
=======
        if ($this->enabledRectorsProvider->isConfigured()) {
            $this->activateEnabledRectorOnly();
>>>>>>> 47c6a521f... [Tests] Split main test case and community test case
        }

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
<<<<<<< HEAD
        if ($this->enabledRectorProvider->isConfigured()) {
            $this->configureEnabledRectorOnly();
=======
        if ($this->enabledRectorsProvider->isConfigured()) {
            $this->activateEnabledRectorOnly();
>>>>>>> 47c6a521f... [Tests] Split main test case and community test case
        }

        $hasNamespace = (bool) $this->nodeFinder->findFirstInstanceOf($nodes, Namespace_::class);
        if (! $hasNamespace && $nodes !== []) {
            $fileWithoutNamespace = new FileWithoutNamespace($nodes);
            $fileWithoutNamespace->setAttribute(
                AttributeKey::FILE_INFO,
                $this->currentFileInfoProvider->getSmartFileInfo()
            );

            $firstNode = $nodes[0];
            $fileWithoutNamespace->setAttribute(
                AttributeKey::DECLARES,
                $firstNode->getAttribute(AttributeKey::DECLARES)
            );

            return parent::traverse([$fileWithoutNamespace]);
        }

        return parent::traverse($nodes);
    }

    public function getPhpRectorCount(): int
    {
        return count($this->visitors);
    }

    public function hasZeroCacheRectors(): bool
    {
        return (bool) $this->getZeroCacheRectorCount();
    }

    public function getZeroCacheRectorCount(): int
    {
        $zeroCacheRectors = array_filter($this->allPhpRectors, function (PhpRectorInterface $phpRector): bool {
            return $phpRector instanceof ZeroCacheRectorInterface;
        });

        return count($zeroCacheRectors);
    }

    /**
     * Mostly used for testing
     */
<<<<<<< HEAD
    private function configureEnabledRectorOnly(): void
    {
        $this->visitors = [];
        $enabledRector = $this->enabledRectorProvider->getEnabledRector();

=======
    private function activateEnabledRectorOnly(): void
    {
        $this->visitors = [];

        $enabledRector = $this->enabledRectorsProvider->getEnabledRector();

>>>>>>> 47c6a521f... [Tests] Split main test case and community test case
        foreach ($this->allPhpRectors as $phpRector) {
            if (! is_a($phpRector, $enabledRector, true)) {
                continue;
            }

            $this->addVisitor($phpRector);
            break;
        }
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
}
