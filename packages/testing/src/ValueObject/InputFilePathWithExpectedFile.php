<?php

declare(strict_types=1);

namespace Rector\Testing\ValueObject;

use Rector\FileSystemRector\Contract\AddedFileInterface;
use Symplify\SmartFileSystem\SmartFileInfo;

final class InputFilePathWithExpectedFile
{
    /**
     * @var string
     */
    private $inputFilePath;

    /**
     * @var AddedFileInterface
     */
    private $addedFile;

    public function __construct(string $inputFilePath, AddedFileInterface $addedFile)
    {
        $this->inputFilePath = $inputFilePath;
        $this->addedFile = $addedFile;
    }

    public function getInputFileInfo(): SmartFileInfo
    {
        return new SmartFileInfo($this->inputFilePath);
    }

    public function getAddedFile(): AddedFileInterface
    {
        return $this->addedFile;
    }
}
