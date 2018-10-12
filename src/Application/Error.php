<?php declare(strict_types=1);

namespace Rector\Application;

use Symplify\PackageBuilder\FileSystem\SmartFileInfo;

final class Error
{
    /**
     * @var SmartFileInfo
     */
    private $fileInfo;

    /**
     * @var string
     */
    private $message;

    /**
     * @var int|null
     */
    private $line;

    public function __construct(SmartFileInfo $smartFileInfo, string $message, ?int $line)
    {
        $this->fileInfo = $smartFileInfo;
        $this->message = $message;
        $this->line = $line;
    }

    public function getFileInfo(): SmartFileInfo
    {
        return $this->fileInfo;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getLine(): ?int
    {
        return $this->line;
    }
}
