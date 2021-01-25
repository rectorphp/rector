<?php

declare(strict_types=1);

namespace Rector\Core\Application;

use Rector\Core\Contract\Rector\RectorInterface;
use Rector\PostRector\Contract\Rector\PostRectorInterface;
use Rector\RectorGenerator\Contract\InternalRectorInterface;
use Symplify\Skipper\Skipper\Skipper;
use Symplify\SmartFileSystem\SmartFileInfo;

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
        $dummyFileInfo = new SmartFileInfo(__DIR__ . '/../../config/config.php');

        foreach ($rectors as $key => $rector) {
            // @todo add should skip element to avoid faking a file info?
            if ($skipper->shouldSkipElementAndFileInfo($rector, $dummyFileInfo)) {
                unset($rectors[$key]);
            }
        }

        $this->rectors = $rectors;
    }

    /**
     * @return RectorInterface[]
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
