<?php

declare (strict_types=1);
namespace Rector\Skipper\Skipper;

use Rector\Skipper\Matcher\FileInfoMatcher;
use Symplify\SmartFileSystem\SmartFileInfo;
final class SkipSkipper
{
    /**
     * @readonly
     * @var \Rector\Skipper\Matcher\FileInfoMatcher
     */
    private $fileInfoMatcher;
    public function __construct(FileInfoMatcher $fileInfoMatcher)
    {
        $this->fileInfoMatcher = $fileInfoMatcher;
    }
    /**
     * @param array<string, string[]|null> $skippedClasses
     * @param object|string $checker
     * @param \Symplify\SmartFileSystem\SmartFileInfo|string $file
     */
    public function doesMatchSkip($checker, $file, array $skippedClasses) : bool
    {
        foreach ($skippedClasses as $skippedClass => $skippedFiles) {
            if (!\is_a($checker, $skippedClass, \true)) {
                continue;
            }
            // skip everywhere
            if (!\is_array($skippedFiles)) {
                return \true;
            }
            if ($this->fileInfoMatcher->doesFileInfoMatchPatterns($file, $skippedFiles)) {
                return \true;
            }
        }
        return \false;
    }
}
