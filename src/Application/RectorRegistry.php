<?php

declare (strict_types=1);
namespace Rector\Application;

use PhpParser\Node;
use Rector\Configuration\ConfigurationRuleFilter;
use Rector\Contract\Rector\OrderedRectorInterface;
use Rector\Contract\Rector\RectorInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Skipper\Skipper\Skipper;
use Rector\ValueObject\Application\File;
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
     * @readonly
     */
    private Skipper $skipper;
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
    public function __construct(PhpVersionedFilter $phpVersionedFilter, ComposerPackageConstraintFilter $composerPackageConstraintFilter, ConfigurationRuleFilter $configurationRuleFilter, Skipper $skipper, array $rectors)
    {
        $this->phpVersionedFilter = $phpVersionedFilter;
        $this->composerPackageConstraintFilter = $composerPackageConstraintFilter;
        $this->configurationRuleFilter = $configurationRuleFilter;
        $this->skipper = $skipper;
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
     * Filters out rectors skipped for this file before they ever run, so the skip no longer needs a
     * second refactor() probe on a clone to learn whether it mattered.
     *
     * Known limit: a configured skip is marked used as soon as its path matches the file, even if the
     * rule would not have changed it. This drops precise unused-skip reporting in exchange for not
     * running the rule twice per skipped node.
     *
     * @return RectorInterface[]
     */
    public function forNodeAndFile(Node $node, File $file): array
    {
        $rectors = $this->forNode($node);
        $filePath = $file->getFilePath();
        return array_filter($rectors, fn(RectorInterface $rector): bool => !$this->skipper->shouldSkipRectorAndFile($rector, $filePath));
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
        $activeRectors = $this->configurationRuleFilter->filter($activeRectors);
        $this->activeRectors = $this->sortByPrecededRule($activeRectors);
        $this->areRectorsPrepared = \true;
    }
    /**
     * Rules implementing OrderedRectorInterface are moved after the rule they depend on,
     * so a chain of dependent rules converges in a single file pass.
     *
     * @param RectorInterface[] $rectors
     * @return RectorInterface[]
     */
    private function sortByPrecededRule(array $rectors): array
    {
        $rectorByClass = [];
        foreach ($rectors as $rector) {
            $rectorByClass[get_class($rector)] = $rector;
        }
        $precededClassByClass = [];
        foreach ($rectors as $rector) {
            if (!$rector instanceof OrderedRectorInterface) {
                continue;
            }
            $precededClass = $rector->precededRule();
            // ignore a predecessor filtered out of the active set
            if (isset($rectorByClass[$precededClass])) {
                $precededClassByClass[get_class($rector)] = $precededClass;
            }
        }
        if ($precededClassByClass === []) {
            return $rectors;
        }
        $sortedRectors = [];
        $emittedClasses = [];
        $remainingRectors = $rectors;
        while ($remainingRectors !== []) {
            $hasProgress = \false;
            foreach ($remainingRectors as $key => $rector) {
                $precededClass = $precededClassByClass[get_class($rector)] ?? null;
                if ($precededClass !== null && !isset($emittedClasses[$precededClass])) {
                    continue;
                }
                $sortedRectors[] = $rector;
                $emittedClasses[get_class($rector)] = \true;
                unset($remainingRectors[$key]);
                $hasProgress = \true;
            }
            if (!$hasProgress) {
                $remainingClasses = [];
                foreach ($remainingRectors as $rector) {
                    $remainingClasses[] = get_class($rector);
                }
                throw new ShouldNotHappenException(sprintf('Circular rule ordering detected among: %s', implode(', ', $remainingClasses)));
            }
        }
        return $sortedRectors;
    }
}
