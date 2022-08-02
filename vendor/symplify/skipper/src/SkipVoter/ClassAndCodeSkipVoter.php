<?php

declare (strict_types=1);
namespace RectorPrefix202208\Symplify\Skipper\SkipVoter;

use RectorPrefix202208\Symplify\Skipper\Contract\SkipVoterInterface;
use RectorPrefix202208\Symplify\Skipper\Matcher\FileInfoMatcher;
use RectorPrefix202208\Symplify\Skipper\SkipCriteriaResolver\SkippedClassAndCodesResolver;
use RectorPrefix202208\Symplify\SmartFileSystem\SmartFileInfo;
/**
 * Matching class and code, e.g. App\Category\ArraySniff.SomeCode
 */
final class ClassAndCodeSkipVoter implements SkipVoterInterface
{
    /**
     * @var \Symplify\Skipper\SkipCriteriaResolver\SkippedClassAndCodesResolver
     */
    private $skippedClassAndCodesResolver;
    /**
     * @var \Symplify\Skipper\Matcher\FileInfoMatcher
     */
    private $fileInfoMatcher;
    public function __construct(SkippedClassAndCodesResolver $skippedClassAndCodesResolver, FileInfoMatcher $fileInfoMatcher)
    {
        $this->skippedClassAndCodesResolver = $skippedClassAndCodesResolver;
        $this->fileInfoMatcher = $fileInfoMatcher;
    }
    /**
     * @param string|object $element
     */
    public function match($element) : bool
    {
        if (!\is_string($element)) {
            return \false;
        }
        return \substr_count($element, '.') === 1;
    }
    /**
     * @param string|object $element
     */
    public function shouldSkip($element, SmartFileInfo $smartFileInfo) : bool
    {
        if (\is_object($element)) {
            return \false;
        }
        $skippedClassAndCodes = $this->skippedClassAndCodesResolver->resolve();
        if (!\array_key_exists($element, $skippedClassAndCodes)) {
            return \false;
        }
        // skip regardless the path
        $skippedPaths = $skippedClassAndCodes[$element];
        if ($skippedPaths === null) {
            return \true;
        }
        return $this->fileInfoMatcher->doesFileInfoMatchPatterns($smartFileInfo, $skippedPaths);
    }
}
