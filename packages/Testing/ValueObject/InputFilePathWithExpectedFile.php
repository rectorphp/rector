<?php

declare(strict_types=1);

namespace Rector\Testing\ValueObject;

use Rector\FileSystemRector\ValueObject\AddedFileWithContent;
use Symplify\SmartFileSystem\SmartFileInfo;

final class InputFilePathWithExpectedFile
{
    /**
     * @var string
     */
    private $inputFilePath;

    /**
     * @var AddedFileWithContent
     */
    private $addedFileWithContent;

    public function __construct(string $inputFilePath, AddedFileWithContent $addedFileWithContent)
    {
        $this->inputFilePath = $inputFilePath;
        $this->addedFileWithContent = $addedFileWithContent;
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
