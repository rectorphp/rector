<?php

declare(strict_types=1);

namespace Rector\ChangesReporting\Collector;

use PhpParser\Node;
use Rector\ChangesReporting\ValueObject\RectorWithFileAndLineChange;
use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\Exception\NotRectorException;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RectorChangeCollector
{
    /**
     * @var RectorWithFileAndLineChange[]
     */
    private $rectorWithFileAndLineChanges = [];

    public function addRectorClassWithLine(string $rectorClass, SmartFileInfo $smartFileInfo, int $line): void
    {
        if (! is_a($rectorClass, RectorInterface::class, true)) {
            throw new NotRectorException($rectorClass);
        }

        $this->rectorWithFileAndLineChanges[] = new RectorWithFileAndLineChange(
            $rectorClass,
            $smartFileInfo->getRealPath(),
            $line
        );
    }

    /**
     * @return RectorWithFileAndLineChange[]
     */
    public function getRectorChangesByFileInfo(SmartFileInfo $smartFileInfo): array
    {
        return array_filter(
            $this->rectorWithFileAndLineChanges,
            function (RectorWithFileAndLineChange $rectorWithFileAndLineChange) use ($smartFileInfo) {
                return $rectorWithFileAndLineChange->getRealPath() === $smartFileInfo->getRealPath();
            }
        );
    }

    public function notifyNodeFileInfo(Node $node): void
    {
        $fileInfo = $node->getAttribute(AttributeKey::FILE_INFO);
        if ($fileInfo === null) {
            // this file was changed before and this is a sub-new node
            // array Traverse to all new nodes would have to be used, but it's not worth the performance
            return;
        }

        $this->addRectorClassWithLine(static::class, $fileInfo, $node->getLine());
    }
}
