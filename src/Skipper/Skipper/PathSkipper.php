<?php

declare (strict_types=1);
namespace Rector\Skipper\Skipper;

use Rector\Skipper\Matcher\FileInfoMatcher;
use Rector\Skipper\SkipCriteriaResolver\SkippedPathsResolver;
final class PathSkipper
{
    /**
     * @readonly
     */
    private FileInfoMatcher $fileInfoMatcher;
    /**
     * @readonly
     */
    private SkippedPathsResolver $skippedPathsResolver;
    /**
     * @readonly
     */
    private \Rector\Skipper\Skipper\UsedSkipCollector $usedSkipCollector;
    public function __construct(FileInfoMatcher $fileInfoMatcher, SkippedPathsResolver $skippedPathsResolver, \Rector\Skipper\Skipper\UsedSkipCollector $usedSkipCollector)
    {
        $this->fileInfoMatcher = $fileInfoMatcher;
        $this->skippedPathsResolver = $skippedPathsResolver;
        $this->usedSkipCollector = $usedSkipCollector;
    }
    public function shouldSkip(string $filePath): bool
    {
        foreach ($this->skippedPathsResolver->resolve() as $skippedPath) {
            if ($this->fileInfoMatcher->doesFileInfoMatchPatterns($filePath, [$skippedPath])) {
                $this->usedSkipCollector->markUsed($skippedPath);
                return \true;
            }
        }
        return \false;
    }
}
