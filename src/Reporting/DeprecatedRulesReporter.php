<?php

declare (strict_types=1);
namespace Rector\Reporting;

use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Configuration\Option;
use Rector\Configuration\Parameter\SimpleParameterProvider;
use Rector\Console\Style\RectorStyle;
final class DeprecatedRulesReporter
{
    /**
     * @readonly
     */
    private RectorStyle $rectorStyle;
    public function __construct(RectorStyle $rectorStyle)
    {
        $this->rectorStyle = $rectorStyle;
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
            $this->rectorStyle->warning(sprintf('Registered rule "%s" is deprecated and will be removed. Upgrade your config to use another rule or remove it', $registeredRectorRule));
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
            $this->rectorStyle->warning(sprintf('Skipped rule "%s" is deprecated', $skippedRectorRule));
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
            $this->rectorStyle->warning(sprintf('The "->withAttributesSets(%s: true)" argument is deprecated and no longer applied. It is already included in the "symfony: true" argument, use it instead.', $deprecatedAttributesSetsArg));
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
            $this->rectorStyle->warning(sprintf('The "->withComposerBased(%s: true)" argument is deprecated and no longer applied. It only added named args to 2 methods of a single package, register the rule directly if needed.', $deprecatedComposerBasedArg));
            ++$reportedCount;
        }
        return $reportedCount;
    }
    public function reportDeprecatedPreparedSetsArgs(): int
    {
        /** @var string[] $deprecatedPreparedSetsArgs */
        $deprecatedPreparedSetsArgs = SimpleParameterProvider::provideArrayParameter(Option::DEPRECATED_PREPARED_SETS_ARGS);
        $reportedCount = 0;
        foreach (array_unique($deprecatedPreparedSetsArgs) as $deprecatedPreparedSetsArg) {
            $this->rectorStyle->warning(sprintf('The "->withPreparedSets(%s: true)" argument is deprecated and no longer applied. Its rules were moved to the "codeQuality" and "codingStyle" sets, use them instead.', $deprecatedPreparedSetsArg));
            ++$reportedCount;
        }
        return $reportedCount;
    }
}
