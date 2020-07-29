<?php

declare(strict_types=1);

namespace Rector\RectorGenerator\Tests\PHPUnit\ValueObject;

use Rector\Core\Exception\ShouldNotHappenException;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ExpectedAndOutputFileInfoPair
{
    /**
     * @var SmartFileInfo
     */
    private $expectedFileInfo;

    /**
     * @var SmartFileInfo|null
     */
    private $outputFileInfo;

    public function __construct(SmartFileInfo $expectedFileInfo, ?SmartFileInfo $outputFileInfo)
    {
        $this->expectedFileInfo = $expectedFileInfo;
        $this->outputFileInfo = $outputFileInfo;
    }

    /**
     * @noRector \Rector\Privatization\Rector\ClassMethod\PrivatizeLocalOnlyMethodRector
     */
    public function getExpectedFileContent(): string
    {
        return $this->expectedFileInfo->getContents();
    }

    /**
     * @noRector \Rector\Privatization\Rector\ClassMethod\PrivatizeLocalOnlyMethodRector
     */
    public function getOutputFileContent(): string
    {
        if ($this->outputFileInfo === null) {
            throw new ShouldNotHappenException();
        }

        return $this->outputFileInfo->getContents();
    }

    /**
     * @noRector \Rector\Privatization\Rector\ClassMethod\PrivatizeLocalOnlyMethodRector
     */
    public function doesOutputFileExist(): bool
    {
        return $this->outputFileInfo !== null;
    }
}
