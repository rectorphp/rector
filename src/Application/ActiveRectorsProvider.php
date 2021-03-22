<?php

declare(strict_types=1);

namespace Rector\Core\Application;

use Rector\Core\Contract\Rector\RectorInterface;
use Rector\PostRector\Contract\Rector\PostRectorInterface;
use Symplify\Skipper\Skipper\Skipper;

/**
 * Provides list of Rector rules, that are not internal → only those registered by user
 */
final class ActiveRectorsProvider
{
    /**
     * @var RectorInterface[]
     */
    private $rectors = [];

    /**
     * @param RectorInterface[] $rectors
     */
    public function __construct(array $rectors, Skipper $skipper)
    {
        foreach ($rectors as $key => $rector) {
            if ($skipper->shouldSkipElement($rector)) {
                unset($rectors[$key]);
            }
        }

        $this->rectors = $rectors;
    }

    /**
     * @template T as RectorInterface
     * @param class-string<T> $type
     * @return array<T>
     */
    public function provideByType(string $type): array
    {
        return array_filter($this->rectors, function (RectorInterface $rector) use ($type): bool {
            return is_a($rector, $type, true);
        });
    }

    /**
     * @return RectorInterface[]
     */
    public function provide(): array
    {
        return $this->filterOutInternalRectorsAndSort($this->rectors);
    }

    /**
     * @param RectorInterface[] $rectors
     * @return RectorInterface[]
     */
    private function filterOutInternalRectorsAndSort(array $rectors): array
    {
        sort($rectors);

        $rectors = array_filter($rectors, function (RectorInterface $rector): bool {
            // skip as internal and always run
            return ! $rector instanceof PostRectorInterface;
        });

        usort($rectors, function (RectorInterface $firstRector, RectorInterface $secondRector): int {
            return get_class($firstRector) <=> get_class($secondRector);
        });

        return $rectors;
    }
}
