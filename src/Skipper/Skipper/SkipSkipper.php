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
            // mark the specific matched path used, scoped to its rule via "class|path" - the same
            // path can be skipped under multiple rules, so a path-only key would mark them all used
            $matchedPath = $this->fileInfoMatcher->matchPattern($filePath, $skippedFiles);
            if ($matchedPath !== null) {
                $this->usedSkipCollector->markUsed($skippedClass . '|' . $matchedPath);
                return \true;
            }
        }
        return \false;
    }
}
