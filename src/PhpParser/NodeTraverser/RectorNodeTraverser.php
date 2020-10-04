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
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Contract\Rector\PhpRectorInterface;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace;
use Rector\Core\Testing\Application\EnabledRectorsProvider;
use Rector\Core\Testing\PHPUnit\StaticPHPUnitEnvironment;

final class RectorNodeTraverser extends NodeTraverser
{
    /**
     * @var PhpRectorInterface[]
     */
    private $allPhpRectors = [];

    /**
     * @var EnabledRectorsProvider
     */
    private $enabledRectorsProvider;

    /**
     * @var NodeFinder
     */
    private $nodeFinder;

    public function __construct(
        EnabledRectorsProvider $enabledRectorsProvider,
        Configuration $configuration,
        ActiveRectorsProvider $activeRectorsProvider,
        NodeFinder $nodeFinder
    ) {
        /** @var PhpRectorInterface[] $phpRectors */
        $phpRectors = $activeRectorsProvider->provideByType(PhpRectorInterface::class);

        $this->allPhpRectors = $phpRectors;
        $this->enabledRectorsProvider = $enabledRectorsProvider;

        foreach ($phpRectors as $phpRector) {
            if ($configuration->isCacheEnabled() && ! $configuration->shouldClearCache() && $phpRector instanceof ZeroCacheRectorInterface) {
                continue;
            }

            $this->addVisitor($phpRector);
        }
        $this->nodeFinder = $nodeFinder;
    }

    /**
     * @param Node[] $nodes
     * @return Node[]
     */
    public function traverse(array $nodes): array
    {
        if ($this->enabledRectorsProvider->isConfigured()) {
            $this->configureEnabledRectorsOnly();
        }

        $hasNamespace = (bool) $this->nodeFinder->findFirstInstanceOf($nodes, Namespace_::class);
        if (! $hasNamespace) {
            $fileWithoutNamespace = new FileWithoutNamespace($nodes);
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
    private function configureEnabledRectorsOnly(): void
    {
        $this->visitors = [];
        $enabledRectors = $this->enabledRectorsProvider->getEnabledRectors();

        foreach ($enabledRectors as $enabledRector => $configuration) {
            foreach ($this->allPhpRectors as $phpRector) {
                if (! is_a($phpRector, $enabledRector, true)) {
                    continue;
                }

                $this->configureTestedRector($phpRector, $configuration);

                $this->addVisitor($phpRector);
                continue 2;
            }
        }
    }

    /**
     * @param mixed[] $configuration
     */
    private function configureTestedRector(PhpRectorInterface $phpRector, array $configuration): void
    {
        if (! StaticPHPUnitEnvironment::isPHPUnitRun()) {
            return;
        }

        if ($phpRector instanceof ConfigurableRectorInterface) {
            $phpRector->configure($configuration);
        } elseif ($configuration !== []) {
            $message = sprintf(
                'Rule "%s" with configuration must implement "%s"',
                get_class($phpRector),
                ConfigurableRectorInterface::class
            );
            throw new ShouldNotHappenException($message);
        }
    }
}
