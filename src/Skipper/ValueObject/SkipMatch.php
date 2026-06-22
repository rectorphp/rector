<?php

declare (strict_types=1);
namespace Rector\Skipper\ValueObject;

/**
 * Result of a matched class/path skip: the skipped class and the concrete path pattern
 * that matched, if the skip is scoped to specific paths.
 */
final class SkipMatch
{
    /**
     * @readonly
     */
    private string $skippedClass;
    /**
     * @readonly
     */
    private ?string $matchedPath;
    public function __construct(string $skippedClass, ?string $matchedPath)
    {
        $this->skippedClass = $skippedClass;
        $this->matchedPath = $matchedPath;
    }
    public function getSkippedClass(): string
    {
        return $this->skippedClass;
    }
    public function getMatchedPath(): ?string
    {
        return $this->matchedPath;
    }
}
