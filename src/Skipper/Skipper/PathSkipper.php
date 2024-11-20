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
    public function __construct(FileInfoMatcher $fileInfoMatcher, SkippedPathsResolver $skippedPathsResolver)
    {
        $this->fileInfoMatcher = $fileInfoMatcher;
        $this->skippedPathsResolver = $skippedPathsResolver;
    }
    public function shouldSkip(string $filePath) : bool
    {
        $skippedPaths = $this->skippedPathsResolver->resolve();
        return $this->fileInfoMatcher->doesFileInfoMatchPatterns($filePath, $skippedPaths);
    }
}
