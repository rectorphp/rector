<?php

declare (strict_types=1);
namespace Rector\Skipper\Skipper;

/**
 * Collects skip elements (rule classes and paths) that actually matched during the run,
 * so unused skips can be reported and removed.
 *
 * @see \Rector\Tests\Skipper\Skipper\UsedSkipCollectorTest
 */
final class UsedSkipCollector
{
    /**
     * @var array<string, true>
     */
    private array $usedSkips = [];
    public function markUsed(string $skip): void
    {
        $this->usedSkips[$skip] = \true;
    }
    /**
     * @return string[]
     */
    public function provide(): array
    {
        return array_keys($this->usedSkips);
    }
}
