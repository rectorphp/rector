<?php

declare (strict_types=1);
namespace Rector\Bridge;

use Rector\Config\RectorConfig;
use Rector\Contract\Rector\RectorInterface;
use RectorPrefix202410\Webmozart\Assert\Assert;
/**
 * @api
 * @experimental since 1.1.2
 * Utils class to ease building bridges by 3rd-party tools
 *
 * @see \Rector\Tests\Bridge\SetRectorsResolverTest
 */
final class SetRectorsResolver
{
    /**
     * @param string[] $configFilePaths
     * @return array<int, class-string<RectorInterface>|array<class-string<RectorInterface>, mixed[]>>
     */
    public function resolveFromFilePathsIncludingConfiguration(array $configFilePaths) : array
    {
        Assert::allString($configFilePaths);
        Assert::allFileExists($configFilePaths);
        $combinedRectorRulesWithConfiguration = [];
        foreach ($configFilePaths as $configFilePath) {
            $rectorRulesWithConfiguration = $this->resolveFromFilePathIncludingConfiguration($configFilePath);
            $combinedRectorRulesWithConfiguration = \array_merge($combinedRectorRulesWithConfiguration, $rectorRulesWithConfiguration);
        }
        return $combinedRectorRulesWithConfiguration;
    }
    /**
     * @return array<int, class-string<RectorInterface>|array<class-string<RectorInterface>, mixed[]>>
     */
    public function resolveFromFilePathIncludingConfiguration(string $configFilePath) : array
    {
        $rectorConfig = $this->loadRectorConfigFromFilePath($configFilePath);
        $rectorClassesWithOptionalConfiguration = $rectorConfig->getRectorClasses();
        foreach ($rectorConfig->getRuleConfigurations() as $rectorClass => $configuration) {
            // remove from non-configurable, if added again with better config
            if (\in_array($rectorClass, $rectorClassesWithOptionalConfiguration)) {
                $rectorRulePosition = \array_search($rectorClass, $rectorClassesWithOptionalConfiguration, \true);
                if (\is_int($rectorRulePosition)) {
                    unset($rectorClassesWithOptionalConfiguration[$rectorRulePosition]);
                }
            }
            $rectorClassesWithOptionalConfiguration[] = [$rectorClass => $configuration];
        }
        // sort keys
        return \array_values($rectorClassesWithOptionalConfiguration);
    }
    private function loadRectorConfigFromFilePath(string $configFilePath) : RectorConfig
    {
        Assert::fileExists($configFilePath);
        $rectorConfig = new RectorConfig();
        /** @var callable $configCallable */
        $configCallable = (require $configFilePath);
        $configCallable($rectorConfig);
        return $rectorConfig;
    }
}
