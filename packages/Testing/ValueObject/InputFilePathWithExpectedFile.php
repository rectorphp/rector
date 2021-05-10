<?php

declare(strict_types=1);

namespace Rector\Testing\ValueObject;

use Rector\FileSystemRector\ValueObject\AddedFileWithContent;
use Symplify\SmartFileSystem\SmartFileInfo;

final class InputFilePathWithExpectedFile
{
    public function __construct(
        private string $inputFilePath,
        private AddedFileWithContent $addedFileWithContent
    ) {
    }

    public function getInputFileInfo(): SmartFileInfo
    {
        return new SmartFileInfo($this->inputFilePath);
    }

    public function getAddedFileWithContent(): AddedFileWithContent
    {
        return $this->addedFileWithContent;
    }
}
