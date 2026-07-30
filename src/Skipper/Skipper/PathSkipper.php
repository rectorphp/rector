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
        $matchedPath = $this->fileInfoMatcher->matchPattern($filePath, $this->skippedPathsResolver->resolve());
        if ($matchedPath === null) {
            return \false;
        }
        $this->usedSkipCollector->markUsed($matchedPath);
        return \true;
    }
}
