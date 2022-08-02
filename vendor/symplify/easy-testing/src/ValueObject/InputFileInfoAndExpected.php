<?php

declare (strict_types=1);
namespace RectorPrefix202208\Symplify\EasyTesting\ValueObject;

use RectorPrefix202208\Symplify\SmartFileSystem\SmartFileInfo;
/**
 * @api
 */
final class InputFileInfoAndExpected
{
    /**
     * @var \Symplify\SmartFileSystem\SmartFileInfo
     */
    private $inputFileInfo;
    /**
     * @var mixed
     */
    private $expected;
    /**
     * @param mixed $expected
     */
    public function __construct(SmartFileInfo $inputFileInfo, $expected)
    {
        $this->inputFileInfo = $inputFileInfo;
        $this->expected = $expected;
    }
    public function getInputFileContent() : string
    {
        return $this->inputFileInfo->getContents();
    }
    public function getInputFileInfo() : SmartFileInfo
    {
        return $this->inputFileInfo;
    }
    public function getInputFileRealPath() : string
    {
        return $this->inputFileInfo->getRealPath();
    }
    /**
     * @return mixed
     */
    public function getExpected()
    {
        return $this->expected;
    }
}
