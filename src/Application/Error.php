<?php declare(strict_types=1);

namespace Rector\Application;

use Rector\Contract\Rector\RectorInterface;
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
     * @var RectorInterface|null
     */
    private $rector;

    public function __construct(
        SmartFileInfo $smartFileInfo,
        string $message,
        ?int $line = null,
        ?RectorInterface $rector = null
    ) {
        $this->fileInfo = $smartFileInfo;
        $this->message = $message;
        $this->line = $line;
        $this->rector = $rector;
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

    public function getRector(): ?RectorInterface
    {
        return $this->rector;
    }
}
