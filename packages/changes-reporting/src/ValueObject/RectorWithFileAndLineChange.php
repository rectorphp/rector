<?php

declare(strict_types=1);

namespace Rector\ChangesReporting\ValueObject;

final class RectorWithFileAndLineChange
{
    /**
     * @var string
     */
    private $rectorClass;

    /**
     * @var int
     */
    private $line;

    /**
     * @var string
     */
    private $realPath;

    public function __construct(string $rectorClass, string $realPath, int $line)
    {
        $this->rectorClass = $rectorClass;
        $this->line = $line;
        $this->realPath = $realPath;
    }

    public function getRectorClass(): string
    {
        return $this->rectorClass;
    }

    public function getLine(): int
    {
        return $this->line;
    }

    public function getRealPath(): string
    {
        return $this->realPath;
    }
}
