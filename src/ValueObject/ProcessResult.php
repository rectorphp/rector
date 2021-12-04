<?php

declare(strict_types=1);

namespace Rector\Core\ValueObject;

use Rector\Core\ValueObject\Application\SystemError;
use Rector\Core\ValueObject\Reporting\FileDiff;
use Symplify\SmartFileSystem\SmartFileInfo;
use Webmozart\Assert\Assert;

/**
 * @see \Rector\Core\ValueObjectFactory\ProcessResultFactory
 */
final class ProcessResult
{
    /**
     * @param FileDiff[] $fileDiffs
     * @param SystemError[] $systemErrors
     */
    public function __construct(
        private readonly array $systemErrors,
        private readonly array $fileDiffs,
        private readonly int $addedFilesCount,
        private readonly int $removedFilesCount,
        private readonly int $removedNodeCount
    ) {
        Assert::allIsAOf($fileDiffs, FileDiff::class);
        Assert::allIsAOf($systemErrors, SystemError::class);
    }

    /**
     * @return FileDiff[]
     */
    public function getFileDiffs(): array
    {
        return $this->fileDiffs;
    }

    /**
     * @return SystemError[]
     */
    public function getErrors(): array
    {
        return $this->systemErrors;
    }

    public function getAddedFilesCount(): int
    {
        return $this->addedFilesCount;
    }

    public function getRemovedFilesCount(): int
    {
        return $this->removedFilesCount;
    }

    public function getRemovedAndAddedFilesCount(): int
    {
        return $this->removedFilesCount + $this->addedFilesCount;
    }

    public function getRemovedNodeCount(): int
    {
        return $this->removedNodeCount;
    }

    /**
     * @return SmartFileInfo[]
     */
    public function getChangedFileInfos(): array
    {
        $fileInfos = [];
        foreach ($this->fileDiffs as $fileDiff) {
            $fileInfos[] = new SmartFileInfo($fileDiff->getRelativeFilePath());
        }

        return array_unique($fileInfos);
    }
}
