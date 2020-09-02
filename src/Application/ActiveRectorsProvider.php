<?php

declare(strict_types=1);

namespace Rector\Core\Application;

use Rector\Core\Contract\Rector\RectorInterface;
use Rector\PostRector\Contract\Rector\PostRectorInterface;
use Rector\RectorGenerator\Contract\InternalRectorInterface;

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
    public function __construct(array $rectors)
    {
        $this->rectors = $rectors;
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

        return array_filter($rectors, function (RectorInterface $rector): bool {
            // utils rules
            if ($rector instanceof InternalRectorInterface) {
                return false;
            }

            // skip as internal and always run
            return ! $rector instanceof PostRectorInterface;
        });
    }
}
