<?php

declare(strict_types=1);

namespace Rector\Autodiscovery\Tests\Rector\FileSystem\MoveInterfacesToContractNamespaceDirectoryRector\ValueObject;

use Symplify\SmartFileSystem\SmartFileInfo;

final class InputFilePathWithExpectedFile
{
    /**
     * @var string
     */
    private $expectedContent;

    /**
     * @var string
     */
    private $inputFilePath;

    /**
     * @var string
     */
    private $expectedFilePath;

    public function __construct(string $inputFilePath, string $expectedFilePath, string $expectedContent)
    {
        $this->expectedContent = $expectedContent;
        $this->inputFilePath = $inputFilePath;
        $this->expectedFilePath = $expectedFilePath;
    }

    public function getExpectedContent(): string
    {
        return $this->expectedContent;
    }

    public function getExpectedFilePath(): string
    {
        return $this->expectedFilePath;
    }

    public function getInputFileInfo(): SmartFileInfo
    {
        return new SmartFileInfo($this->inputFilePath);
    }
}
