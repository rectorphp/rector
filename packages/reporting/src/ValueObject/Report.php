<?php

declare(strict_types=1);

namespace Rector\Reporting\ValueObject;

use Symplify\SmartFileSystem\SmartFileInfo;

final class Report
{
    /**
     * @var int
     */
    private $line;

    /**
     * @var string
     */
    private $report;

    /**
     * @var string
     */
    private $rectorClass;

    /**
     * @var SmartFileInfo
     */
    private $smartFileInfo;

    public function __construct(string $report, string $rectorClass, SmartFileInfo $smartFileInfo, int $line)
    {
        $this->smartFileInfo = $smartFileInfo;
        $this->line = $line;
        $this->report = $report;
        $this->rectorClass = $rectorClass;
    }

    public function getRelativeFilePath(): string
    {
        return $this->smartFileInfo->getRelativeFilePathFromCwd();
    }

    public function getLine(): int
    {
        return $this->line;
    }

    public function getReport(): string
    {
        return $this->report;
    }

    public function getRectorClass(): string
    {
        return $this->rectorClass;
    }
}
