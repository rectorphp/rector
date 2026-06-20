<?php

declare (strict_types=1);
namespace Rector\Reporting;

use Rector\Configuration\Option;
use Rector\Configuration\Parameter\SimpleParameterProvider;
use Rector\Configuration\VendorMissAnalyseGuard;
use Rector\PostRector\Contract\Rector\PostRectorInterface;
use Rector\Skipper\SkipCriteriaResolver\SkippedClassResolver;
use Rector\Skipper\SkipCriteriaResolver\SkippedPathsResolver;
use Rector\ValueObject\ProcessResult;
use RectorPrefix202606\Symfony\Component\Console\Style\SymfonyStyle;
/**
 * @see \Rector\Tests\Reporting\MissConfigurationReporterTest
 */
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
    /**
     * @readonly
     */
    private SkippedClassResolver $skippedClassResolver;
    /**
     * @readonly
     */
    private SkippedPathsResolver $skippedPathsResolver;
    public function __construct(SymfonyStyle $symfonyStyle, VendorMissAnalyseGuard $vendorMissAnalyseGuard, SkippedClassResolver $skippedClassResolver, SkippedPathsResolver $skippedPathsResolver)
    {
        $this->symfonyStyle = $symfonyStyle;
        $this->vendorMissAnalyseGuard = $vendorMissAnalyseGuard;
        $this->skippedClassResolver = $skippedClassResolver;
        $this->skippedPathsResolver = $skippedPathsResolver;
    }
    /**
     * Reports skips configured via "->withSkip()" that never matched any element during the run.
     * Enabled with "->reportUnusedSkips()".
     */
    public function reportUnusedSkips(ProcessResult $processResult): void
    {
        if (!SimpleParameterProvider::provideBoolParameter(Option::REPORT_UNUSED_SKIPS, \false)) {
            return;
        }
        // map of trackable skip path => human-readable display; skips are tracked at runtime by
        // their path, but rule-scoped ones are printed as "rule => path" so the user knows exactly
        // what to remove. Skip-everywhere rule skips (null path) are forgotten from the container
        // at boot, so they never reach the skipper and cannot be tracked.
        $skipDisplaysByPath = [];
        foreach ($this->skippedClassResolver->resolve() as $rectorClass => $paths) {
            if ($paths === null) {
                continue;
            }
            // rule-scoped paths are intentional, so they are reported even as mask paths
            foreach ($paths as $path) {
                $skipDisplaysByPath[$path] = $rectorClass . ' => ' . $path;
            }
        }
        // global mask paths like "*/some/*" are hard to spot and report false positives, skip them
        foreach ($this->skippedPathsResolver->resolve() as $globalPath) {
            if (strpos($globalPath, '*') !== \false) {
                continue;
            }
            $skipDisplaysByPath[$globalPath] = $globalPath;
        }
        $usedSkips = $processResult->getUsedSkips();
        $unusedSkips = [];
        foreach ($skipDisplaysByPath as $path => $skipDisplay) {
            if (!in_array($path, $usedSkips, \true)) {
                $unusedSkips[] = $skipDisplay;
            }
        }
        if ($unusedSkips === []) {
            return;
        }
        $this->symfonyStyle->warning(sprintf('%s never matched any element. You can remove %s from "->withSkip()"', count($unusedSkips) > 1 ? 'These skips are unused, they' : 'This skip is unused, it', count($unusedSkips) > 1 ? 'them' : 'it'));
        $this->symfonyStyle->listing($unusedSkips);
    }
    public function reportSkippedNeverRegisteredRules(): void
    {
        $registeredRules = SimpleParameterProvider::provideArrayParameter(Option::REGISTERED_RECTOR_RULES);
        $skippedRules = SimpleParameterProvider::provideArrayParameter(Option::SKIPPED_RECTOR_RULES);
        $neverRegisteredSkippedRules = array_unique(array_diff($skippedRules, $registeredRules));
        // remove special PostRectorInterface rules, they are registered in a different way
        $neverRegisteredSkippedRules = array_filter($neverRegisteredSkippedRules, fn($skippedRule): bool => !is_a($skippedRule, PostRectorInterface::class, \true));
        if ($neverRegisteredSkippedRules === []) {
            return;
        }
        $this->symfonyStyle->warning(sprintf('%s never registered. You can remove %s from "->withSkip()"', count($neverRegisteredSkippedRules) > 1 ? 'These skipped rules are' : 'This skipped rule is', count($neverRegisteredSkippedRules) > 1 ? 'them' : 'it'));
        $this->symfonyStyle->listing($neverRegisteredSkippedRules);
    }
    /**
     * @param string[] $filePaths
     */
    public function reportVendorInPaths(array $filePaths): void
    {
        if (!$this->vendorMissAnalyseGuard->isVendorAnalyzed($filePaths)) {
            return;
        }
        $this->symfonyStyle->warning(sprintf('Rector has detected a "/vendor" directory in your configured paths. If this is Composer\'s vendor directory, this is not necessary as it will be autoloaded. Scanning the Composer /vendor directory will cause Rector to run much slower and possibly with errors.%sRemove "/vendor" from Rector paths and run again.', "\n\n"));
        sleep(3);
    }
    public function reportStartWithShortOpenTag(): void
    {
        $files = SimpleParameterProvider::provideArrayParameter(Option::SKIPPED_START_WITH_SHORT_OPEN_TAG_FILES);
        if ($files === []) {
            return;
        }
        $suffix = count($files) > 1 ? 's were' : ' was';
        $fileList = implode("\n", $files);
        $this->symfonyStyle->warning(sprintf('The following file%s skipped as starting with short open tag. Migrate to long open PHP tag first: %s%s', $suffix, "\n\n", $fileList));
        sleep(3);
    }
}
