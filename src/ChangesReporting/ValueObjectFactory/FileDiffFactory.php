<?php

declare (strict_types=1);
namespace Rector\ChangesReporting\ValueObjectFactory;

use Rector\ChangesReporting\ValueObject\RectorWithLineChange;
use Rector\Console\Formatter\ConsoleDiffer;
use Rector\Differ\DefaultDiffer;
use Rector\FileSystem\FilePathHelper;
use Rector\ValueObject\Application\File;
use Rector\ValueObject\Reporting\FileDiff;
final class FileDiffFactory
{
    /**
     * @readonly
     */
    private DefaultDiffer $defaultDiffer;
    /**
     * @readonly
     */
    private ConsoleDiffer $consoleDiffer;
    /**
     * @readonly
     */
    private FilePathHelper $filePathHelper;
    public function __construct(DefaultDiffer $defaultDiffer, ConsoleDiffer $consoleDiffer, FilePathHelper $filePathHelper)
    {
        $this->defaultDiffer = $defaultDiffer;
        $this->consoleDiffer = $consoleDiffer;
        $this->filePathHelper = $filePathHelper;
    }
    /**
     * @param RectorWithLineChange[] $rectorsWithLineChanges
     */
    public function createFileDiffWithLineChanges(bool $shouldShowDiffs, File $file, string $oldContent, string $newContent, array $rectorsWithLineChanges) : FileDiff
    {
        $relativeFilePath = $this->filePathHelper->relativePath($file->getFilePath());
        // always keep the most recent diff
        return new FileDiff($relativeFilePath, $shouldShowDiffs ? $this->defaultDiffer->diff($oldContent, $newContent) : '', $shouldShowDiffs ? $this->consoleDiffer->diff($oldContent, $newContent) : '', $rectorsWithLineChanges);
    }
}
