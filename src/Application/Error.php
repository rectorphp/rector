<?php declare(strict_types=1);

namespace Rector\Application;

use Symfony\Component\Finder\SplFileInfo;

final class Error
{
    /**
     * @var SplFileInfo
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

    public function __construct(SplFileInfo $fileInfo, string $message, ?int $line)
    {
        $this->fileInfo = $fileInfo;
        $this->message = $message;
        $this->line = $line;
    }

    public function getFileInfo(): SplFileInfo
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
