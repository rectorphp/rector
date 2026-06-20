<?php

declare (strict_types=1);
namespace Rector\Reporting;

use Rector\Configuration\Option;
use Rector\Configuration\Parameter\SimpleParameterProvider;
use Rector\FileSystem\FilePathHelper;
use Rector\Skipper\SkipCriteriaResolver\SkippedClassResolver;
use Rector\Skipper\SkipCriteriaResolver\SkippedPathsResolver;
use Rector\ValueObject\ProcessResult;
/**
 * @see \Rector\Tests\Reporting\UnusedSkipResolverTest
 */
final class UnusedSkipResolver
{
    /**
     * @readonly
     */
    private SkippedClassResolver $skippedClassResolver;
    /**
     * @readonly
     */
    private SkippedPathsResolver $skippedPathsResolver;
    /**
     * @readonly
     */
    private FilePathHelper $filePathHelper;
    public function __construct(SkippedClassResolver $skippedClassResolver, SkippedPathsResolver $skippedPathsResolver, FilePathHelper $filePathHelper)
    {
        $this->skippedClassResolver = $skippedClassResolver;
        $this->skippedPathsResolver = $skippedPathsResolver;
        $this->filePathHelper = $filePathHelper;
    }
    /**
     * Resolves skips configured via "->withSkip()" that never matched any element during the run.
     * Rule-scoped skips are returned as "rule => path" so the user knows exactly what to remove;
     * global skips are returned as a plain path. Returns an empty array unless
     * "->reportUnusedSkips()" is enabled.
     *
     * @return string[]
     */
    public function resolve(ProcessResult $processResult): array
    {
        if (!SimpleParameterProvider::provideBoolParameter(Option::REPORT_UNUSED_SKIPS, \false)) {
            return [];
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
                $skipDisplaysByPath[$path] = $rectorClass . ' => ' . $this->filePathHelper->relativePath($path);
            }
        }
        // global mask paths like "*/some/*" are hard to spot and report false positives, skip them
        foreach ($this->skippedPathsResolver->resolve() as $globalPath) {
            if (strpos($globalPath, '*') !== \false) {
                continue;
            }
            $skipDisplaysByPath[$globalPath] = $this->filePathHelper->relativePath($globalPath);
        }
        $usedSkips = $processResult->getUsedSkips();
        $unusedSkips = [];
        foreach ($skipDisplaysByPath as $path => $skipDisplay) {
            if (!in_array($path, $usedSkips, \true)) {
                $unusedSkips[] = $skipDisplay;
            }
        }
        return $unusedSkips;
    }
}
