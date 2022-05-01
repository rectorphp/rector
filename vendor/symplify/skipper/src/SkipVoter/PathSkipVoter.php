<?php

declare (strict_types=1);
namespace RectorPrefix20220501\Symplify\Skipper\SkipVoter;

use RectorPrefix20220501\Symplify\Skipper\Contract\SkipVoterInterface;
use RectorPrefix20220501\Symplify\Skipper\Matcher\FileInfoMatcher;
use RectorPrefix20220501\Symplify\Skipper\SkipCriteriaResolver\SkippedPathsResolver;
use Symplify\SmartFileSystem\SmartFileInfo;
final class PathSkipVoter implements \RectorPrefix20220501\Symplify\Skipper\Contract\SkipVoterInterface
{
    /**
     * @var \Symplify\Skipper\Matcher\FileInfoMatcher
     */
    private $fileInfoMatcher;
    /**
     * @var \Symplify\Skipper\SkipCriteriaResolver\SkippedPathsResolver
     */
    private $skippedPathsResolver;
    public function __construct(\RectorPrefix20220501\Symplify\Skipper\Matcher\FileInfoMatcher $fileInfoMatcher, \RectorPrefix20220501\Symplify\Skipper\SkipCriteriaResolver\SkippedPathsResolver $skippedPathsResolver)
    {
        $this->fileInfoMatcher = $fileInfoMatcher;
        $this->skippedPathsResolver = $skippedPathsResolver;
    }
    /**
     * @param string|object $element
     */
    public function match($element) : bool
    {
        return \true;
    }
    /**
     * @param string|object $element
     */
    public function shouldSkip($element, \Symplify\SmartFileSystem\SmartFileInfo $smartFileInfo) : bool
    {
        $skippedPaths = $this->skippedPathsResolver->resolve();
        return $this->fileInfoMatcher->doesFileInfoMatchPatterns($smartFileInfo, $skippedPaths);
    }
}
