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
    private $expectedAddedFileWithContent;

    public function __construct(string $inputFilePath, AddedFileWithContent $expectedAddedFileWithContent)
    {
        $this->inputFilePath = $inputFilePath;
        $this->expectedAddedFileWithContent = $expectedAddedFileWithContent;
    }

    public function getInputFileInfo(): SmartFileInfo
    {
        return new SmartFileInfo($this->inputFilePath);
    }

    public function getExpectedAddedFileWithContent(): AddedFileWithContent
    {
        return $this->expectedAddedFileWithContent;
    }
}
