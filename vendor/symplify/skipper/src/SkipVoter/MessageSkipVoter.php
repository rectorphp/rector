<?php

declare (strict_types=1);
namespace RectorPrefix202208\Symplify\Skipper\SkipVoter;

use RectorPrefix202208\Symplify\Skipper\Contract\SkipVoterInterface;
use RectorPrefix202208\Symplify\Skipper\Matcher\FileInfoMatcher;
use RectorPrefix202208\Symplify\Skipper\SkipCriteriaResolver\SkippedMessagesResolver;
use RectorPrefix202208\Symplify\SmartFileSystem\SmartFileInfo;
final class MessageSkipVoter implements SkipVoterInterface
{
    /**
     * @var \Symplify\Skipper\SkipCriteriaResolver\SkippedMessagesResolver
     */
    private $skippedMessagesResolver;
    /**
     * @var \Symplify\Skipper\Matcher\FileInfoMatcher
     */
    private $fileInfoMatcher;
    public function __construct(SkippedMessagesResolver $skippedMessagesResolver, FileInfoMatcher $fileInfoMatcher)
    {
        $this->skippedMessagesResolver = $skippedMessagesResolver;
        $this->fileInfoMatcher = $fileInfoMatcher;
    }
    /**
     * @param string|object $element
     */
    public function match($element) : bool
    {
        if (\is_object($element)) {
            return \false;
        }
        return \substr_count($element, ' ') > 0;
    }
    /**
     * @param string|object $element
     */
    public function shouldSkip($element, SmartFileInfo $smartFileInfo) : bool
    {
        if (\is_object($element)) {
            return \false;
        }
        $skippedMessages = $this->skippedMessagesResolver->resolve();
        if (!\array_key_exists($element, $skippedMessages)) {
            return \false;
        }
        // skip regardless the path
        $skippedPaths = $skippedMessages[$element];
        if ($skippedPaths === null) {
            return \true;
        }
        return $this->fileInfoMatcher->doesFileInfoMatchPatterns($smartFileInfo, $skippedPaths);
    }
}
