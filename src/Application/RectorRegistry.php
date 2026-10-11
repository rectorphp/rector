<?php

declare (strict_types=1);
namespace Rector\Application;

use PhpParser\Node;
use Rector\Configuration\ConfigurationRuleFilter;
use Rector\Contract\Rector\RectorInterface;
use Rector\VersionBonding\ComposerPackageConstraintFilter;
use Rector\VersionBonding\PhpVersionedFilter;
/**
 * @see \Rector\Tests\Application\RectorRegistryTest
 */
final class RectorRegistry
{
    /**
     * @readonly
     */
    private PhpVersionedFilter $phpVersionedFilter;
    /**
     * @readonly
     */
    private ComposerPackageConstraintFilter $composerPackageConstraintFilter;
    /**
     * @readonly
     */
    private ConfigurationRuleFilter $configurationRuleFilter;
    /**
     * @var RectorInterface[]
     */
    private array $rectors;
    private bool $areRectorsPrepared = \false;
    /**
     * @var RectorInterface[]
     */
    private array $activeRectors = [];
    /**
     * @var array<class-string<Node>, RectorInterface[]>
     */
    private array $cache = [];
    /**
     * @param RectorInterface[] $rectors
     */
    public function __construct(PhpVersionedFilter $phpVersionedFilter, ComposerPackageConstraintFilter $composerPackageConstraintFilter, ConfigurationRuleFilter $configurationRuleFilter, array $rectors)
    {
        $this->phpVersionedFilter = $phpVersionedFilter;
        $this->composerPackageConstraintFilter = $composerPackageConstraintFilter;
        $this->configurationRuleFilter = $configurationRuleFilter;
        $this->rectors = $rectors;
    }
    /**
     * @param RectorInterface[] $rectors
     *
     * @internal Used only in Rector core, not supported outside. Might change any time.
     */
    public function refreshRectors(array $rectors): void
    {
        $this->rectors = $rectors;
        $this->areRectorsPrepared = \false;
        $this->cache = [];
    }
    /**
     * @return RectorInterface[]
     */
    public function forNode(Node $node): array
    {
        $this->prepareRectors();
        $nodeClass = get_class($node);
        if (!isset($this->cache[$nodeClass])) {
            $this->cache[$nodeClass] = [];
            foreach ($this->activeRectors as $rector) {
                foreach ($rector->getNodeTypes() as $nodeType) {
                    if (is_a($nodeClass, $nodeType, \true)) {
                        $this->cache[$nodeClass][] = $rector;
                        continue 2;
                    }
                }
            }
        }
        return $this->cache[$nodeClass];
    }
    /**
     * Filtering must happen lazily, after the configuration is set in ProcessCommand::execute(),
     * otherwise options like --only are not yet available and get ignored.
     */
    private function prepareRectors(): void
    {
        if ($this->areRectorsPrepared) {
            return;
        }
        $activeRectors = $this->phpVersionedFilter->filter($this->rectors);
        $activeRectors = $this->composerPackageConstraintFilter->filter($activeRectors);
        $this->activeRectors = $this->configurationRuleFilter->filter($activeRectors);
        $this->areRectorsPrepared = \true;
    }
}
