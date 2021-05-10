<?php

declare(strict_types=1);

namespace Rector\ChangesReporting\ValueObjectFactory;

use Rector\Core\Differ\DefaultDiffer;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\Reporting\FileDiff;
use Symplify\ConsoleColorDiff\Console\Output\ConsoleDiffer;

final class FileDiffFactory
{
    public function __construct(
        private DefaultDiffer $defaultDiffer,
        private ConsoleDiffer $consoleDiffer
    ) {
    }

    public function createFileDiff(File $file, string $oldContent, string $newContent): FileDiff
    {
        // always keep the most recent diff
        return new FileDiff(
            $file->getSmartFileInfo(),
            $this->defaultDiffer->diff($oldContent, $newContent),
            $this->consoleDiffer->diff($oldContent, $newContent),
            $file->getRectorWithLineChanges()
        );
    }
}
