<?php

declare (strict_types=1);
namespace Rector\Skipper\Skipper;

use Rector\Skipper\Matcher\FileInfoMatcher;
final class SkipSkipper
{
    /**
     * @readonly
     */
    private FileInfoMatcher $fileInfoMatcher;
    /**
     * @readonly
     */
    private \Rector\Skipper\Skipper\UsedSkipCollector $usedSkipCollector;
    public function __construct(FileInfoMatcher $fileInfoMatcher, \Rector\Skipper\Skipper\UsedSkipCollector $usedSkipCollector)
    {
        $this->fileInfoMatcher = $fileInfoMatcher;
        $this->usedSkipCollector = $usedSkipCollector;
    }
    /**
     * @param array<string, string[]|null> $skippedClasses
     * @param object|string $checker
     */
    public function doesMatchSkip($checker, string $filePath, array $skippedClasses): bool
    {
        foreach ($skippedClasses as $skippedClass => $skippedFiles) {
            if (!is_a($checker, $skippedClass, \true)) {
                continue;
            }
            // skip everywhere
            if (!is_array($skippedFiles)) {
                $this->usedSkipCollector->markUsed($skippedClass);
                return \true;
            }
            if ($this->fileInfoMatcher->doesFileInfoMatchPatterns($filePath, $skippedFiles)) {
                $this->usedSkipCollector->markUsed($skippedClass);
                return \true;
            }
        }
        return \false;
    }
}
