<?php

declare (strict_types=1);
namespace Rector\Reporting;

use Rector\Configuration\Option;
use Rector\Configuration\Parameter\SimpleParameterProvider;
use Rector\Configuration\VendorMissAnalyseGuard;
use RectorPrefix202406\Symfony\Component\Console\Style\SymfonyStyle;
final class MissConfigurationReporter
{
    /**
     * @readonly
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     */
    private $symfonyStyle;
    /**
     * @readonly
     * @var \Rector\Configuration\VendorMissAnalyseGuard
     */
    private $vendorMissAnalyseGuard;
    public function __construct(SymfonyStyle $symfonyStyle, VendorMissAnalyseGuard $vendorMissAnalyseGuard)
    {
        $this->symfonyStyle = $symfonyStyle;
        $this->vendorMissAnalyseGuard = $vendorMissAnalyseGuard;
    }
    public function reportSkippedNeverRegisteredRules() : void
    {
        $registeredRules = SimpleParameterProvider::provideArrayParameter(Option::REGISTERED_RECTOR_RULES);
        $skippedRules = SimpleParameterProvider::provideArrayParameter(Option::SKIPPED_RECTOR_RULES);
        $neverRegisteredSkippedRules = \array_unique(\array_diff($skippedRules, $registeredRules));
        foreach ($neverRegisteredSkippedRules as $neverRegisteredSkippedRule) {
            $this->symfonyStyle->warning(\sprintf('Skipped rule "%s" is never registered. You can remove it from "->withSkip()"', $neverRegisteredSkippedRule));
        }
    }
    /**
     * @param string[] $filePaths
     */
    public function reportVendorInPaths(array $filePaths) : void
    {
        if (!$this->vendorMissAnalyseGuard->isVendorAnalyzed($filePaths)) {
            return;
        }
        $this->symfonyStyle->warning(\sprintf('Rector has detected a "/vendor" directory in your configured paths. If this is Composer\'s vendor directory, this is not necessary as it will be autoloaded. Scanning the Composer /vendor directory will cause Rector to run much slower and possibly with errors.%sRemove "/vendor" from Rector paths and run again.', \PHP_EOL . \PHP_EOL));
        \sleep(3);
    }
}
