<?php

declare(strict_types=1);

namespace Rector\ChangesReporting\Collector;

use PhpParser\Node;
use Rector\ChangesReporting\ValueObject\RectorWithFileAndLineChange;
use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Logging\CurrentRectorProvider;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RectorChangeCollector
{
    /**
     * @var RectorWithFileAndLineChange[]
     */
    private $rectorWithFileAndLineChanges = [];

    /**
     * @var CurrentRectorProvider
     */
    private $currentRectorProvider;

    public function __construct(CurrentRectorProvider $currentRectorProvider)
    {
        $this->currentRectorProvider = $currentRectorProvider;
    }

    /**
     * @return RectorWithFileAndLineChange[]
     */
    public function getRectorChangesByFileInfo(SmartFileInfo $smartFileInfo): array
    {
        return array_filter(
            $this->rectorWithFileAndLineChanges,
            function (RectorWithFileAndLineChange $rectorWithFileAndLineChange) use ($smartFileInfo): bool {
                return $rectorWithFileAndLineChange->getRealPath() === $smartFileInfo->getRealPath();
            }
        );
    }

    public function notifyNodeFileInfo(Node $node): void
    {
        $fileInfo = $node->getAttribute(AttributeKey::FILE_INFO);
        if (! $fileInfo instanceof SmartFileInfo) {
            // this file was changed before and this is a sub-new node
            // array Traverse to all new nodes would have to be used, but it's not worth the performance
            return;
        }

        $currentRector = $this->currentRectorProvider->getCurrentRector();
        if (! $currentRector instanceof RectorInterface) {
            throw new ShouldNotHappenException();
        }

        $this->addRectorClassWithLine($currentRector, $fileInfo, $node->getLine());
    }

    private function addRectorClassWithLine(RectorInterface $rector, SmartFileInfo $smartFileInfo, int $line): void
    {
        $this->rectorWithFileAndLineChanges[] = new RectorWithFileAndLineChange(
            $rector,
            $smartFileInfo->getRealPath(),
            $line
        );
    }
}
