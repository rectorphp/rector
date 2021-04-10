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

    public function getFileInfo(): SmartFileInfo
    {
        return $this->smartFileInfo;
    }

    /**
     * @return RectorWithFileAndLineChange[]
     */
    public function getRectorChanges(): array
    {
        return $this->rectorWithFileAndLineChanges;
    }

    /**
     * @return string[]
     */
    public function getRectorClasses(): array
    {
        $rectorClasses = [];
        foreach ($this->rectorWithFileAndLineChanges as $rectorWithFileAndLineChange) {
            $rectorClasses[] = $rectorWithFileAndLineChange->getRectorClass();
        }

        return $this->sortClasses($rectorClasses);
    }

    /**
     * @return string[]
     */
    public function getRectorClassesWithChangelogUrl(): array
    {
        $rectorClasses = [];
        foreach ($this->rectorWithFileAndLineChanges as $rectorWithFileAndLineChange) {
            $rectorClasses[] = $rectorWithFileAndLineChange->getRectorClassWithChangelogUrl();
        }

        return $this->sortClasses($rectorClasses);
    }

    /**
     * @return array<string, string>
     */
    public function getRectorClassesWithChangelogUrlAndRectorClassAsKey(): array
    {
        $rectorClasses = [];
        foreach ($this->rectorWithFileAndLineChanges as $rectorWithFileAndLineChange) {
            if ($rectorWithFileAndLineChange->getChangelogUrl() !== null) {
                $rectorClasses[$rectorWithFileAndLineChange->getRectorClass()] = $rectorWithFileAndLineChange->getChangelogUrl();
            }
        }
        $rectorClasses = array_unique($rectorClasses);

        ksort($rectorClasses);

        return $rectorClasses;
    }

    /**
     * @param string[] $rectorClasses
     * @return string[]
     */
    private function sortClasses(array $rectorClasses): array
    {
        $rectorClasses = array_unique($rectorClasses);

        sort($rectorClasses);

        return $rectorClasses;
    }
}
