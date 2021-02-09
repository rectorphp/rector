<?php

declare(strict_types=1);

namespace Rector\Core\ValueObject\Reporting;

use Rector\ChangesReporting\ValueObject\RectorWithFileAndLineChange;
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
     * @var RectorWithFileAndLineChange[]
     */
    private $rectorWithFileAndLineChanges = [];

    /**
     * @var SmartFileInfo
     */
    private $smartFileInfo;

    /**
     * @param RectorWithFileAndLineChange[] $rectorWithFileAndLineChanges
     */
    public function __construct(
        SmartFileInfo $smartFileInfo,
        string $diff,
        string $diffConsoleFormatted,
        array $rectorWithFileAndLineChanges = []
    ) {
        $this->smartFileInfo = $smartFileInfo;
        $this->diff = $diff;
        $this->rectorWithFileAndLineChanges = $rectorWithFileAndLineChanges;
        $this->diffConsoleFormatted = $diffConsoleFormatted;
    }

    private function getDiff(): string
    {
        return $this->diff;
    }

    private function getDiffConsoleFormatted(): string
    {
        return $this->diffConsoleFormatted;
    }

    private function getRelativeFilePath(): string
    {
        return $this->smartFileInfo->getRelativeFilePath();
    }

    private function getFileInfo(): SmartFileInfo
    {
        return $this->smartFileInfo;
    }

    /**
     * @return RectorWithFileAndLineChange[]
     */
    private function getRectorChanges(): array
    {
        return $this->rectorWithFileAndLineChanges;
    }

    /**
     * @return string[]
     */
    private function getRectorClasses(): array
    {
        $rectorClasses = [];
        foreach ($this->rectorWithFileAndLineChanges as $rectorWithFileAndLineChange) {
            $rectorClasses[] = $rectorWithFileAndLineChange->getRectorClass();
        }

        $rectorClasses = array_unique($rectorClasses);

        sort($rectorClasses);

        return $rectorClasses;
    }
}
