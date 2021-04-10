<?php

declare(strict_types=1);

namespace Rector\Core\ValueObject\Reporting;

use Rector\ChangesReporting\ValueObject\RectorWithFileAndLineChange;
use Rector\Core\Contract\Rector\RectorInterface;
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
     * @return array<class-string<RectorInterface>>
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
     * @template TType as object
     * @param array<class-string<TType>> $rectorClasses
     * @return array<class-string<TType>>
     */
    private function sortClasses(array $rectorClasses): array
    {
        $rectorClasses = array_unique($rectorClasses);
        sort($rectorClasses);

        return $rectorClasses;
    }
}
