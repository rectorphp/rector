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
     * Map of skip element (rule class or global path) to the set of paths matched under it.
     * Rule-scoped skips collect their matched paths; skip-everywhere rules and global path skips
     * keep an empty path set, the same shape as the "->withSkip()" config.
     *
     * @var array<string, array<string, true>>
     */
    private array $usedSkips = [];
    public function markUsed(string $skip, ?string $path = null): void
    {
        $this->usedSkips[$skip] ??= [];
        if ($path !== null) {
            $this->usedSkips[$skip][$path] = \true;
        }
    }
    /**
     * @return array<string, string[]>
     */
    public function provide(): array
    {
        $usedSkips = [];
        foreach ($this->usedSkips as $skip => $paths) {
            $usedSkips[$skip] = array_keys($paths);
        }
        return $usedSkips;
    }
}
