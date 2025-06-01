<?php

declare (strict_types=1);
namespace Rector\Reporting;

use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Configuration\Option;
use Rector\Configuration\Parameter\SimpleParameterProvider;
use RectorPrefix202506\Symfony\Component\Console\Style\SymfonyStyle;
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
    public function reportDeprecatedRules() : void
    {
        /** @var string[] $registeredRectorRules */
        $registeredRectorRules = SimpleParameterProvider::provideArrayParameter(Option::REGISTERED_RECTOR_RULES);
        foreach ($registeredRectorRules as $registeredRectorRule) {
            if (!\is_a($registeredRectorRule, DeprecatedInterface::class, \true)) {
                continue;
            }
            $this->symfonyStyle->warning(\sprintf('Registered rule "%s" is deprecated and will be removed. Upgrade your config to use another rule or remove it', $registeredRectorRule));
        }
    }
    public function reportDeprecatedSkippedRules() : void
    {
        /** @var string[] $skippedRectorRules */
        $skippedRectorRules = SimpleParameterProvider::provideArrayParameter(Option::SKIPPED_RECTOR_RULES);
        foreach ($skippedRectorRules as $skippedRectorRule) {
            if (!\is_a($skippedRectorRule, DeprecatedInterface::class, \true)) {
                continue;
            }
            $this->symfonyStyle->warning(\sprintf('Skipped rule "%s" is deprecated', $skippedRectorRule));
        }
    }
}
