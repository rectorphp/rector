<?php

declare (strict_types=1);
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
     * @var \Rector\FileSystemRector\ValueObject\AddedFileWithContent
     */
    private $addedFileWithContent;
    public function __construct(string $inputFilePath, \Rector\FileSystemRector\ValueObject\AddedFileWithContent $addedFileWithContent)
    {
        $this->inputFilePath = $inputFilePath;
        $this->addedFileWithContent = $addedFileWithContent;
    }
    public function getInputFileInfo() : \Symplify\SmartFileSystem\SmartFileInfo
    {
        return new \Symplify\SmartFileSystem\SmartFileInfo($this->inputFilePath);
    }
    public function getAddedFileWithContent() : \Rector\FileSystemRector\ValueObject\AddedFileWithContent
    {
        return $this->addedFileWithContent;
    }
}
