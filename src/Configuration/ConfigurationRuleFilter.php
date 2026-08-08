<?php

declare (strict_types=1);
namespace Rector\Configuration;

use Rector\Configuration\Parameter\SimpleParameterProvider;
use Rector\Contract\Rector\RectorInterface;
use Rector\ValueObject\Configuration;
use Rector\VersionBonding\Contract\ComposerPackageConstraintInterface;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Rector\VersionBonding\ValueObject\ComposerBoundRuleConfiguration;
/**
 * Modify available rector rules based on the configuration options
 * @see \Rector\Tests\Configuration\ConfigurationRuleFilterTest
 */
final class ConfigurationRuleFilter
{
    private ?Configuration $configuration = null;
    public function setConfiguration(Configuration $configuration): void
    {
        $this->configuration = $configuration;
    }
    /**
     * @param list<RectorInterface> $rectors
     * @return list<RectorInterface>
     */
    public function filter(array $rectors): array
    {
        if (!$this->configuration instanceof Configuration) {
            return $rectors;
        }
        $onlyRule = $this->configuration->getOnlyRule();
        if ($onlyRule !== null) {
            return $this->filterOnlyRule($rectors, $onlyRule);
        }
        if ($this->configuration->isComposerBased()) {
            return $this->filterComposerBased($rectors);
        }
        if ($this->configuration->isPhpOnly()) {
            return $this->filterPhpOnly($rectors);
        }
        return $rectors;
    }
    /**
     * @param list<RectorInterface> $rectors
     * @return list<RectorInterface>
     */
    public function filterOnlyRule(array $rectors, string $onlyRule): array
    {
        $activeRectors = [];
        foreach ($rectors as $rector) {
            if ($rector instanceof $onlyRule) {
                $activeRectors[] = $rector;
            }
        }
        return $activeRectors;
    }
    /**
     * Keeps rules that declare a composer package constraint themselves, and rules whose configuration
     * was registered with a composer package constraint.
     *
     * @param list<RectorInterface> $rectors
     * @return list<RectorInterface>
     */
    private function filterComposerBased(array $rectors): array
    {
        $composerBoundRectorClasses = $this->resolveComposerBoundRectorClasses();
        $activeRectors = [];
        foreach ($rectors as $rector) {
            if ($rector instanceof ComposerPackageConstraintInterface) {
                $activeRectors[] = $rector;
                continue;
            }
            if (in_array(get_class($rector), $composerBoundRectorClasses, \true)) {
                $activeRectors[] = $rector;
            }
        }
        return $activeRectors;
    }
    /**
     * Keeps rules bound to a minimal PHP version, e.g. PHP upgrade rules
     *
     * @param list<RectorInterface> $rectors
     * @return list<RectorInterface>
     */
    private function filterPhpOnly(array $rectors): array
    {
        $activeRectors = [];
        foreach ($rectors as $rector) {
            if ($rector instanceof MinPhpVersionInterface) {
                $activeRectors[] = $rector;
            }
        }
        return $activeRectors;
    }
    /**
     * @return string[]
     */
    private function resolveComposerBoundRectorClasses(): array
    {
        $composerBoundRuleConfigurations = SimpleParameterProvider::provideArrayParameter(\Rector\Configuration\Option::COMPOSER_BOUND_RULE_CONFIGURATIONS);
        $rectorClasses = [];
        foreach ($composerBoundRuleConfigurations as $composerBoundRuleConfiguration) {
            if (!$composerBoundRuleConfiguration instanceof ComposerBoundRuleConfiguration) {
                continue;
            }
            if (!$composerBoundRuleConfiguration->isActive()) {
                continue;
            }
            $rectorClasses[] = $composerBoundRuleConfiguration->getRectorClass();
        }
        return $rectorClasses;
    }
}
