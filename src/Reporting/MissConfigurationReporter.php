<?php

declare (strict_types=1);
namespace Rector\Reporting;

use Rector\Configuration\Option;
use Rector\Configuration\Parameter\SimpleParameterProvider;
use Rector\Configuration\VendorMissAnalyseGuard;
use Rector\PostRector\Contract\Rector\PostRectorInterface;
use RectorPrefix202506\Symfony\Component\Console\Style\SymfonyStyle;
final class MissConfigurationReporter
{
    /**
     * @readonly
     */
    private SymfonyStyle $symfonyStyle;
    /**
     * @readonly
     */
    private VendorMissAnalyseGuard $vendorMissAnalyseGuard;
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
        // remove special PostRectorInterface rules, they are registered in a different way
        $neverRegisteredSkippedRules = \array_filter($neverRegisteredSkippedRules, fn($skippedRule): bool => !\is_a($skippedRule, PostRectorInterface::class, \true));
        if ($neverRegisteredSkippedRules === []) {
            return;
        }
        $this->symfonyStyle->warning(\sprintf('%s never registered. You can remove %s from "->withSkip()"', \count($neverRegisteredSkippedRules) > 1 ? 'These skipped rules are' : 'This skipped rule is', \count($neverRegisteredSkippedRules) > 1 ? 'them' : 'it'));
        $this->symfonyStyle->listing($neverRegisteredSkippedRules);
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
    public function reportStartWithShortOpenTag() : void
    {
        $files = SimpleParameterProvider::provideArrayParameter(Option::SKIPPED_START_WITH_SHORT_OPEN_TAG_FILES);
        if ($files === []) {
            return;
        }
        $suffix = \count($files) > 1 ? 's were' : ' was';
        $fileList = \implode(\PHP_EOL, $files);
        $this->symfonyStyle->warning(\sprintf('The following file%s skipped as starting with short open tag. Migrate to long open PHP tag first: %s%s', $suffix, \PHP_EOL . \PHP_EOL, $fileList));
        \sleep(3);
    }
}
