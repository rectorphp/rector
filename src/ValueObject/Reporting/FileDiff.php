<?php

declare(strict_types=1);

namespace Rector\Core\ValueObject\Reporting;

use Symplify\SmartFileSystem\SmartFileInfo;

final class FileDiff
{
    /**
     * @var string
     */
    private $diff;

    /**
     * @var string
     */
    private $diffConsoleFormatted;

    /**
     * @var string[]
     */
    private $appliedRectorClasses = [];

    /**
     * @var SmartFileInfo
     */
    private $smartFileInfo;

    /**
     * @param string[] $appliedRectorClasses
     */
    public function __construct(
        SmartFileInfo $smartFileInfo,
        string $diff,
        string $diffConsoleFormatted,
        array $appliedRectorClasses = []
    ) {
        $this->smartFileInfo = $smartFileInfo;
        $this->diff = $diff;
        $this->appliedRectorClasses = $appliedRectorClasses;
        $this->diffConsoleFormatted = $diffConsoleFormatted;
    }

    public function getDiff(): string
    {
        return $this->diff;
    }

    public function getDiffConsoleFormatted(): string
    {
        return $this->diffConsoleFormatted;
    }

    public function getRelativeFilePath(): string
    {
        return $this->smartFileInfo->getRelativeFilePath();
    }

    /**
     * @return string[]
     */
    public function getAppliedRectorClasses(): array
    {
        return $this->appliedRectorClasses;
    }
}
