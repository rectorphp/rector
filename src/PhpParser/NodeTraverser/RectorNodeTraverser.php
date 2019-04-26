<?php declare(strict_types=1);

namespace Rector\PhpParser\NodeTraverser;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use Rector\Contract\Rector\PhpRectorInterface;
use Rector\Testing\Application\EnabledRectorsProvider;
use Symplify\PackageBuilder\Reflection\PrivatesAccessor;

final class RectorNodeTraverser extends NodeTraverser
{
    /**
     * @var EnabledRectorsProvider
     */
    private $enabledRectorsProvider;

    /**
     * @var PhpRectorInterface[]
     */
    private $allPhpRectors = [];

    /**
     * @var PrivatesAccessor
     */
    private $privatesAccessor;

    /**
     * @param PhpRectorInterface[] $phpRectors
     */
    public function __construct(EnabledRectorsProvider $enabledRectorsProvider, array $phpRectors = [])
    {
        $this->allPhpRectors = $phpRectors;
        foreach ($phpRectors as $phpRector) {
            $this->addVisitor($phpRector);
        }

        $this->enabledRectorsProvider = $enabledRectorsProvider;
        $this->privatesAccessor = new PrivatesAccessor();
    }

    /**
     * @param Node[] $nodes
     * @return Node[]
     */
    public function traverse(array $nodes): array
    {
        if ($this->enabledRectorsProvider->isEnabled()) {
            $this->configureEnabledRectorsOnly();
        }

        return parent::traverse($nodes);
    }

    public function getRectorCount(): int
    {
        return count($this->visitors);
    }

    /**
     * @return PhpRectorInterface[]
     */
    public function getRectors(): array
    {
        return $this->visitors;
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

                $this->addRectorConfiguration($configuration, $phpRector);

                $this->addVisitor($phpRector);
                continue 2;
            }
        }
    }

    /**
     * @param mixed[] $configuration
     * @param PhpRectorInterface $phpRector
     */
    private function addRectorConfiguration(array $configuration, PhpRectorInterface $phpRector): void
    {
        foreach ($configuration as $property => $value) {
            /** @var string $property */
            $this->privatesAccessor->setPrivateProperty($phpRector, ltrim($property, '$'), $value);
        }
    }
}
