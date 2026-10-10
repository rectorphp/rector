<?php

declare (strict_types=1);
namespace Rector\Application;

use Rector\Contract\Rector\RectorInterface;
use Rector\Skipper\Skipper\Skipper;
/**
 * @see \Rector\Tests\Application\RectorRegistryTest
 */
final class RectorRegistry
{
    /**
     * @var RectorInterface[]
     */
    private array $rectors;
    /**
     * @readonly
     */
    private Skipper $skipper;
    /**
     * @param RectorInterface[] $rectors
     */
    public function __construct(array $rectors, Skipper $skipper)
    {
        $this->rectors = $rectors;
        $this->skipper = $skipper;
    }
    /**
     * @param RectorInterface[] $rectors
     * @api used in tests to update the active rules
     *
     * @internal Used only in Rector core, not supported outside. Might change any time.
     */
    public function refreshRectors(array $rectors): void
    {
        $this->rectors = $rectors;
    }
    /**
     * @return array<RectorInterface>
     */
    public function forPath(string $filePath): array
    {
        $rectorsForPath = [];
        foreach ($this->rectors as $rector) {
            if ($this->skipper->shouldSkipRectorAndFile($rector, $filePath)) {
                continue;
            }
            $rectorsForPath[] = $rector;
        }
        return $rectorsForPath;
    }
}
