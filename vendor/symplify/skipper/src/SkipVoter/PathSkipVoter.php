<?php

declare (strict_types=1);
namespace RectorPrefix20211123\Symplify\Skipper\SkipVoter;

use RectorPrefix20211123\Symplify\Skipper\Contract\SkipVoterInterface;
use RectorPrefix20211123\Symplify\Skipper\Matcher\FileInfoMatcher;
use RectorPrefix20211123\Symplify\Skipper\SkipCriteriaResolver\SkippedPathsResolver;
use Symplify\SmartFileSystem\SmartFileInfo;
final class PathSkipVoter implements \RectorPrefix20211123\Symplify\Skipper\Contract\SkipVoterInterface
{
    /**
     * @var \Symplify\Skipper\Matcher\FileInfoMatcher
     */
    private $fileInfoMatcher;
    /**
     * @var \Symplify\Skipper\SkipCriteriaResolver\SkippedPathsResolver
     */
    private $skippedPathsResolver;
    public function __construct(\RectorPrefix20211123\Symplify\Skipper\Matcher\FileInfoMatcher $fileInfoMatcher, \RectorPrefix20211123\Symplify\Skipper\SkipCriteriaResolver\SkippedPathsResolver $skippedPathsResolver)
    {
        $this->fileInfoMatcher = $fileInfoMatcher;
        $this->skippedPathsResolver = $skippedPathsResolver;
    }
    /**
     * @param object|string $element
     */
    public function match($element) : bool
    {
        return \true;
    }
    /**
     * @param object|string $element
     * @param \Symplify\SmartFileSystem\SmartFileInfo $smartFileInfo
     */
    public function shouldSkip($element, $smartFileInfo) : bool
    {
        $skippedPaths = $this->skippedPathsResolver->resolve();
        return $this->fileInfoMatcher->doesFileInfoMatchPatterns($smartFileInfo, $skippedPaths);
    }
}
