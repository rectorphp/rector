<?php

declare(strict_types=1);

namespace Rector\Core\ValueObject\Application;

use Symplify\SmartFileSystem\SmartFileInfo;

final class File
{
    /**
     * @var SmartFileInfo
     */
    private $smartFileInfo;

    /**
     * @var string
     */
    private $fileContent;

    public function __construct(SmartFileInfo $smartFileInfo, string $fileContent)
    {
        $this->smartFileInfo = $smartFileInfo;
        $this->fileContent = $fileContent;
    }

    public function getSmartFileInfo(): SmartFileInfo
    {
        return $this->smartFileInfo;
    }

    public function getFileContent(): string
    {
        return $this->fileContent;
    }

    public function changeFileContent(string $fileContent): void
    {
        $this->fileContent = $fileContent;
    }
}
