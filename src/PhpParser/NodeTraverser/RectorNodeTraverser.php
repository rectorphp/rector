<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\NodeTraverser;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use Rector\Caching\Contract\Rector\ZeroCacheRectorInterface;
use Rector\Core\Configuration\Configuration;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Contract\Rector\PhpRectorInterface;
use Rector\Core\Exception\ShouldNotHappenException;
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
     * @param PhpRectorInterface[] $phpRectors
     */
    public function __construct(
        EnabledRectorsProvider $enabledRectorsProvider,
        Configuration $configuration,
        array $phpRectors = []
    ) {
        $this->allPhpRectors = $phpRectors;
        $this->enabledRectorsProvider = $enabledRectorsProvider;

        foreach ($phpRectors as $phpRector) {
            if ($configuration->isCacheEnabled() && ! $configuration->shouldClearCache() && $phpRector instanceof ZeroCacheRectorInterface) {
                continue;
            }

            $this->addVisitor($phpRector);
        }
    }

    /**
     * @return PhpRectorInterface[]
     */
    public function getAllPhpRectors(): array
    {
        return $this->allPhpRectors;
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
