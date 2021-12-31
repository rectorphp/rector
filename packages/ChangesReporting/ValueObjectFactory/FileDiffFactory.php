<?php

declare (strict_types=1);
namespace Rector\ChangesReporting\ValueObjectFactory;

use Rector\Core\Differ\DefaultDiffer;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\Reporting\FileDiff;
use RectorPrefix20211231\Symplify\ConsoleColorDiff\Console\Output\ConsoleDiffer;
final class FileDiffFactory
{
    /**
     * @readonly
     * @var \Rector\Core\Differ\DefaultDiffer
     */
    private $defaultDiffer;
    /**
     * @readonly
     * @var \Symplify\ConsoleColorDiff\Console\Output\ConsoleDiffer
     */
    private $consoleDiffer;
    public function __construct(\Rector\Core\Differ\DefaultDiffer $defaultDiffer, \RectorPrefix20211231\Symplify\ConsoleColorDiff\Console\Output\ConsoleDiffer $consoleDiffer)
    {
        $this->defaultDiffer = $defaultDiffer;
        $this->consoleDiffer = $consoleDiffer;
    }
    public function createFileDiff(\Rector\Core\ValueObject\Application\File $file, string $oldContent, string $newContent) : \Rector\Core\ValueObject\Reporting\FileDiff
    {
        // always keep the most recent diff
        return new \Rector\Core\ValueObject\Reporting\FileDiff($file->getRelativeFilePath(), $this->defaultDiffer->diff($oldContent, $newContent), $this->consoleDiffer->diff($oldContent, $newContent), $file->getRectorWithLineChanges());
    }
}
