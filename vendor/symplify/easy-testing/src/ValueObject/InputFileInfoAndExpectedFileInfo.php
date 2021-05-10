<?php

declare (strict_types=1);
namespace RectorPrefix20210510\Symplify\EasyTesting\ValueObject;

use Symplify\SmartFileSystem\SmartFileInfo;
final class InputFileInfoAndExpectedFileInfo
{
    /**
     * @var SmartFileInfo
     */
    private $inputFileInfo;
    /**
     * @var SmartFileInfo
     */
    private $expectedFileInfo;
    public function __construct(SmartFileInfo $inputFileInfo, SmartFileInfo $expectedFileInfo)
    {
        $this->inputFileInfo = $inputFileInfo;
        $this->expectedFileInfo = $expectedFileInfo;
    }
    public function getInputFileInfo() : SmartFileInfo
    {
        return $this->inputFileInfo;
    }
    public function getExpectedFileInfo() : SmartFileInfo
    {
        return $this->expectedFileInfo;
    }
    public function getExpectedFileContent() : string
    {
        return $this->expectedFileInfo->getContents();
    }
    public function getExpectedFileInfoRealPath() : string
    {
        return $this->expectedFileInfo->getRealPath();
    }
}
