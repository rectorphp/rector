<?php

declare(strict_types=1);

namespace Rector\ChangesReporting\ValueObjectFactory;

use Rector\ChangesReporting\Collector\RectorChangeCollector;
use Rector\Core\Differ\DefaultDiffer;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\Reporting\FileDiff;
use Symplify\ConsoleColorDiff\Console\Output\ConsoleDiffer;

final class FileDiffFactory
{
    /**
     * @var RectorChangeCollector
     */
    private $rectorChangeCollector;

    /**
     * @var DefaultDiffer
     */
    private $defaultDiffer;

    /**
     * @var ConsoleDiffer
     */
    private $consoleDiffer;

    public function __construct(
        RectorChangeCollector $rectorChangeCollector,
        DefaultDiffer $defaultDiffer,
        ConsoleDiffer $consoleDiffer
    ) {
        $this->rectorChangeCollector = $rectorChangeCollector;
        $this->defaultDiffer = $defaultDiffer;
        $this->consoleDiffer = $consoleDiffer;
    }

    public function createFileDiff(File $file, string $oldContent, string $newContent): FileDiff
    {
        $smartFileInfo = $file->getSmartFileInfo();
        $rectorChanges = $this->rectorChangeCollector->getRectorChangesByFileInfo($smartFileInfo);

        // always keep the most recent diff
        return new FileDiff(
            $smartFileInfo,
            $this->defaultDiffer->diff($oldContent, $newContent),
            $this->consoleDiffer->diff($oldContent, $newContent),
            $rectorChanges
        );
    }
}
