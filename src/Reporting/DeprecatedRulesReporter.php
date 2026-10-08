<?php

declare (strict_types=1);
namespace Rector\Reporting;

use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Configuration\Option;
use Rector\Configuration\Parameter\SimpleParameterProvider;
use RectorPrefix202610\Symfony\Component\Console\Style\SymfonyStyle;
final class DeprecatedRulesReporter
{
    /**
     * @readonly
     */
    private SymfonyStyle $symfonyStyle;
    public function __construct(SymfonyStyle $symfonyStyle)
    {
        $this->symfonyStyle = $symfonyStyle;
    }
    public function reportDeprecatedRules(): int
    {
        /** @var string[] $registeredRectorRules */
        $registeredRectorRules = SimpleParameterProvider::provideArrayParameter(Option::REGISTERED_RECTOR_RULES);
        $reportedCount = 0;
        foreach ($registeredRectorRules as $registeredRectorRule) {
            if (!is_a($registeredRectorRule, DeprecatedInterface::class, \true)) {
                continue;
            }
            $this->symfonyStyle->warning(sprintf('Registered rule "%s" is deprecated and will be removed. Upgrade your config to use another rule or remove it', $registeredRectorRule));
            ++$reportedCount;
        }
        return $reportedCount;
    }
    public function reportDeprecatedSkippedRules(): int
    {
        /** @var string[] $skippedRectorRules */
        $skippedRectorRules = SimpleParameterProvider::provideArrayParameter(Option::SKIPPED_RECTOR_RULES);
        $reportedCount = 0;
        foreach ($skippedRectorRules as $skippedRectorRule) {
            if (!is_a($skippedRectorRule, DeprecatedInterface::class, \true)) {
                continue;
            }
            $this->symfonyStyle->warning(sprintf('Skipped rule "%s" is deprecated', $skippedRectorRule));
            ++$reportedCount;
        }
        return $reportedCount;
    }
    public function reportDeprecatedCacheMetaExtensions(): int
    {
        /** @var string[] $cacheMetaExtensions */
        $cacheMetaExtensions = SimpleParameterProvider::provideArrayParameter(Option::CACHE_META_EXTENSIONS);
        $reportedCount = 0;
        foreach ($cacheMetaExtensions as $cacheMetumExtension) {
            $this->symfonyStyle->warning(sprintf('Cache meta extension "%s" is deprecated and no longer applied. It is a niche mechanism, let Rector handle cache on its own. If custom invalidation is needed, handle it in CI in a more generic way, e.g. by clearing the cache directory.', $cacheMetumExtension));
            ++$reportedCount;
        }
        return $reportedCount;
    }
    public function reportDeprecatedPhpSetsMethods(): int
    {
        /** @var string[] $deprecatedPhpSetsMethods */
        $deprecatedPhpSetsMethods = SimpleParameterProvider::provideArrayParameter(Option::DEPRECATED_PHP_SETS_METHODS);
        $reportedCount = 0;
        foreach (array_unique($deprecatedPhpSetsMethods) as $deprecatedPhpSetsMethod) {
            $this->symfonyStyle->warning(sprintf('The "->%s()" method is deprecated and no longer applied. Use "->withPhpLevel()" instead, to raise PHP level one rule at a time.', $deprecatedPhpSetsMethod));
            ++$reportedCount;
        }
        return $reportedCount;
    }
    public function reportDeprecatedAttributesSetsArgs(): int
    {
        /** @var string[] $deprecatedAttributesSetsArgs */
        $deprecatedAttributesSetsArgs = SimpleParameterProvider::provideArrayParameter(Option::DEPRECATED_ATTRIBUTES_SETS_ARGS);
        $reportedCount = 0;
        foreach (array_unique($deprecatedAttributesSetsArgs) as $deprecatedAttributesSetsArg) {
            $this->symfonyStyle->warning(sprintf('The "->withAttributesSets(%s: true)" argument is deprecated and no longer applied. It is already included in the "symfony: true" argument, use it instead.', $deprecatedAttributesSetsArg));
            ++$reportedCount;
        }
        return $reportedCount;
    }
    public function reportDeprecatedComposerBasedArgs(): int
    {
        /** @var string[] $deprecatedComposerBasedArgs */
        $deprecatedComposerBasedArgs = SimpleParameterProvider::provideArrayParameter(Option::DEPRECATED_COMPOSER_BASED_ARGS);
        $reportedCount = 0;
        foreach (array_unique($deprecatedComposerBasedArgs) as $deprecatedComposerBasedArg) {
            $this->symfonyStyle->warning(sprintf('The "->withComposerBased(%s: true)" argument is deprecated and no longer applied. It only added named args to 2 methods of a single package, register the rule directly if needed.', $deprecatedComposerBasedArg));
            ++$reportedCount;
        }
        return $reportedCount;
    }
}
