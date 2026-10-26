<?php

declare (strict_types=1);
namespace Rector\Reporting;

use Rector\Configuration\Option;
use Rector\Configuration\Parameter\SimpleParameterProvider;
use Rector\Configuration\VendorMissAnalyseGuard;
use Rector\Contract\Rector\RectorInterface;
use Rector\PostRector\Contract\Rector\PostRectorInterface;
use Rector\ValueObject\ProcessResult;
use RectorPrefix202610\Symfony\Component\Console\Style\SymfonyStyle;
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
    private \Rector\Reporting\UnusedSkipResolver $unusedSkipResolver;
    /**
     * @readonly
     */
    private \Rector\Reporting\NeededSkipVerifier $neededSkipVerifier;
    public function __construct(SymfonyStyle $symfonyStyle, VendorMissAnalyseGuard $vendorMissAnalyseGuard, \Rector\Reporting\UnusedSkipResolver $unusedSkipResolver, \Rector\Reporting\NeededSkipVerifier $neededSkipVerifier)
    {
        $this->symfonyStyle = $symfonyStyle;
        $this->vendorMissAnalyseGuard = $vendorMissAnalyseGuard;
        $this->unusedSkipResolver = $unusedSkipResolver;
        $this->neededSkipVerifier = $neededSkipVerifier;
    }
    /**
     * Reports skips configured via "->withSkip()" that never matched any element during the run.
     * Enabled with "->reportUnusedSkips()".
     */
    public function reportUnusedSkips(ProcessResult $processResult): void
    {
        $unusedSkips = $this->unusedSkipResolver->resolve($processResult);
        if ($unusedSkips === []) {
            return;
        }
        $this->symfonyStyle->warning(sprintf('%s never matched any element. You can remove %s from "->withSkip()"', count($unusedSkips) > 1 ? 'These skips are unused, they' : 'This skip is unused, it', count($unusedSkips) > 1 ? 'them' : 'it'));
        // add a blank line between items, so grouped rule skips stay visually separated
        $spacedUnusedSkips = array_map(static fn(string $unusedSkip): string => $unusedSkip . "\n", $unusedSkips);
        $this->symfonyStyle->listing($spacedUnusedSkips);
    }
    /**
     * Reports rule-scoped skips that matched a file but whose rule would not have changed it, so the
     * skip is unnecessary. Enabled with "->reportUnusedSkips()". The re-run is forced to a dry run,
     * so it is safe on an apply run too.
     */
    public function reportUnnecessarySkips(): void
    {
        $unnecessarySkips = $this->neededSkipVerifier->verify();
        if ($unnecessarySkips === []) {
            return;
        }
        $this->symfonyStyle->warning(sprintf('%s matched a file, but the rule would not have changed it. You can remove %s from "->withSkip()"', count($unnecessarySkips) > 1 ? 'These skips are unnecessary, they' : 'This skip is unnecessary, it', count($unnecessarySkips) > 1 ? 'them' : 'it'));
        // add a blank line between items, so grouped rule skips stay visually separated
        $spacedUnnecessarySkips = array_map(static fn(string $unnecessarySkip): string => $unnecessarySkip . "\n", $unnecessarySkips);
        $this->symfonyStyle->listing($spacedUnnecessarySkips);
    }
    public function reportSkippedNeverRegisteredRules(): int
    {
        $registeredRules = SimpleParameterProvider::provideArrayParameter(Option::REGISTERED_RECTOR_RULES);
        $skippedRules = SimpleParameterProvider::provideArrayParameter(Option::SKIPPED_RECTOR_RULES);
        $neverRegisteredSkippedRules = array_unique(array_diff($skippedRules, $registeredRules));
        // remove special PostRectorInterface rules, they are registered in a different way
        $neverRegisteredSkippedRules = array_filter($neverRegisteredSkippedRules, fn($skippedRule): bool => !is_a($skippedRule, PostRectorInterface::class, \true));
        if ($neverRegisteredSkippedRules === []) {
            return 0;
        }
        $this->symfonyStyle->warning(sprintf('%s never registered. You can remove %s from "->withSkip()"', count($neverRegisteredSkippedRules) > 1 ? 'These skipped rules are' : 'This skipped rule is', count($neverRegisteredSkippedRules) > 1 ? 'them' : 'it'));
        $this->symfonyStyle->listing($neverRegisteredSkippedRules);
        return count($neverRegisteredSkippedRules);
    }
    public function reportSkippedNonRectorClasses(): int
    {
        $skippedNonRectorClasses = SimpleParameterProvider::provideArrayParameter(Option::SKIPPED_NON_RECTOR_CLASSES);
        if ($skippedNonRectorClasses === []) {
            return 0;
        }
        $this->symfonyStyle->warning(sprintf('%s "%s" %s not a Rector rule, so %s never be skipped. Only classes that implement "%s" can be used in "->withSkip()"', count($skippedNonRectorClasses) > 1 ? 'These skipped classes' : 'This skipped class', implode('", "', $skippedNonRectorClasses), count($skippedNonRectorClasses) > 1 ? 'are' : 'is', count($skippedNonRectorClasses) > 1 ? 'they can' : 'it can', RectorInterface::class));
        return count($skippedNonRectorClasses);
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
