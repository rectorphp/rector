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
     * Rule-scoped skips are grouped under their rule ("rule:" on its own line, each path nested
     * below it) so the user knows exactly what to remove; global skips are returned as a plain
     * path. Returns an empty array unless "->reportUnusedSkips()" is enabled.
     *
     * @return string[]
     */
    public function resolve(ProcessResult $processResult): array
    {
        if ($this->shouldSkipReporting()) {
            return [];
        }
        $usedSkips = $processResult->getUsedSkips();
        return array_merge($this->resolveUnusedRuleScopedSkips($usedSkips), $this->resolveUnusedGlobalSkips($usedSkips));
    }
    private function shouldSkipReporting(): bool
    {
        if (!SimpleParameterProvider::provideBoolParameter(Option::REPORT_UNUSED_SKIPS, \false)) {
            return \true;
        }
        // a narrowed run (cli paths, "--only" or "--only-suffix") only touches part of the codebase,
        // so skips outside that scope look falsely unused - reporting them would be noise
        if (SimpleParameterProvider::provideBoolParameter(Option::IS_RUN_NARROWED, \false)) {
            return \true;
        }
        // a cached run only re-processes changed files, so skips on cached files never get a chance
        // to match and would all look falsely unused - reporting them would be noise
        return SimpleParameterProvider::provideBoolParameter(Option::IS_CACHED_RUN, \false);
    }
    /**
     * Map of rule => (trackable skip path => relative display path); skips are tracked at
     * runtime by their path, but rule-scoped ones are printed grouped under their rule so the
     * user knows exactly what to remove. Skip-everywhere rule skips (null path) are forgotten
     * from the container at boot, so they never reach the skipper and cannot be tracked.
     *
     * @return array<string, array<string, string>>
     */
    private function resolveRelativePathsByClass(): array
    {
        $relativePathsByClass = [];
        foreach ($this->skippedClassResolver->resolve() as $rectorClass => $paths) {
            if ($paths === null) {
                continue;
            }
            // rule-scoped paths are intentional, so they are reported even as mask paths
            foreach ($paths as $path) {
                $relativePathsByClass[$rectorClass][$path] = $this->filePathHelper->relativePath($path);
            }
        }
        return $relativePathsByClass;
    }
    /**
     * @return array<string, string>
     */
    private function resolveGlobalRelativePaths(): array
    {
        $globalRelativePaths = [];
        foreach ($this->skippedPathsResolver->resolve() as $globalPath) {
            // global mask paths like "*/some/*" are hard to spot and report false positives, skip them
            if (strpos($globalPath, '*') !== \false) {
                continue;
            }
            $globalRelativePaths[$globalPath] = $this->filePathHelper->relativePath($globalPath);
        }
        return $globalRelativePaths;
    }
    /**
     * @param string[] $usedSkips
     * @return string[]
     */
    private function resolveUnusedRuleScopedSkips(array $usedSkips): array
    {
        $unusedSkips = [];
        // group unused rule-scoped paths under their rule, matching the "->withSkip()" config shape
        foreach ($this->resolveRelativePathsByClass() as $rectorClass => $relativePaths) {
            $unusedRelativePaths = [];
            foreach ($relativePaths as $path => $relativePath) {
                if (!in_array($path, $usedSkips, \true)) {
                    $unusedRelativePaths[] = $relativePath;
                }
            }
            if ($unusedRelativePaths === []) {
                continue;
            }
            // rule on its own line, with each unused path nested below it as a "->listing()" sub-item
            $unusedSkips[] = $rectorClass . ':' . "\n     * " . implode("\n     * ", $unusedRelativePaths);
        }
        return $unusedSkips;
    }
    /**
     * @param string[] $usedSkips
     * @return string[]
     */
    private function resolveUnusedGlobalSkips(array $usedSkips): array
    {
        $unusedSkips = [];
        foreach ($this->resolveGlobalRelativePaths() as $path => $relativePath) {
            if (!in_array($path, $usedSkips, \true)) {
                $unusedSkips[] = $relativePath;
            }
        }
        return $unusedSkips;
    }
}
