<?php declare(strict_types=1);

namespace Rector\Application;

use Symplify\PackageBuilder\FileSystem\SmartFileInfo;

final class Error
{
    /**
     * @var int|null
     */
    private $line;

    /**
     * @var string
     */
    private $message;

    /**
     * @var SmartFileInfo
     */
    private $fileInfo;

    /**
     * @var string|null
     */
    private $rectorClass;

    public function __construct(
        SmartFileInfo $smartFileInfo,
        string $message,
        ?int $line = null,
        ?string $rectorClass = null
    ) {
        $this->fileInfo = $smartFileInfo;
        $this->message = $message;
        $this->line = $line;
        $this->rectorClass = $rectorClass;
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

    public function getRectorClass(): ?string
    {
        return $this->rectorClass;
    }
}
