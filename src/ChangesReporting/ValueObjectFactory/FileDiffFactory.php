<?php

declare (strict_types=1);
namespace Rector\ChangesReporting\ValueObjectFactory;

use Rector\ChangesReporting\ValueObject\RectorWithLineChange;
use Rector\Console\Formatter\ColorConsoleDiffFormatter;
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
    private FilePathHelper $filePathHelper;
    /**
     * @readonly
     */
    private ColorConsoleDiffFormatter $colorConsoleDiffFormatter;
    public function __construct(DefaultDiffer $defaultDiffer, FilePathHelper $filePathHelper, ColorConsoleDiffFormatter $colorConsoleDiffFormatter)
    {
        $this->defaultDiffer = $defaultDiffer;
        $this->filePathHelper = $filePathHelper;
        $this->colorConsoleDiffFormatter = $colorConsoleDiffFormatter;
    }
    /**
     * @param RectorWithLineChange[] $rectorsWithLineChanges
     */
    public function createFileDiffWithLineChanges(bool $shouldShowDiffs, File $file, string $oldContent, string $newContent, array $rectorsWithLineChanges): FileDiff
    {
        $relativeFilePath = $this->filePathHelper->relativePath($file->getFilePath());
        $diff = $shouldShowDiffs ? $this->defaultDiffer->diff($oldContent, $newContent) : '';
        $consoleDiff = $shouldShowDiffs ? $this->colorConsoleDiffFormatter->format($diff) : '';
        // always keep the most recent diff
        return new FileDiff($relativeFilePath, $diff, $consoleDiff, $rectorsWithLineChanges);
    }
}
