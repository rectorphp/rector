<?php

declare(strict_types=1);

namespace Rector\Core\ValueObject\Reporting;

use Rector\ChangesReporting\ValueObject\RectorWithLineChange;
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
     * @var RectorWithLineChange[]
     */
    private $rectorWithLineChanges = [];

    /**
     * @var SmartFileInfo
     */
    private $smartFileInfo;

    /**
     * @param RectorWithLineChange[] $rectorWithLineChanges
     */
    public function __construct(
        SmartFileInfo $smartFileInfo,
        string $diff,
        string $diffConsoleFormatted,
        array $rectorWithLineChanges = []
    ) {
        $this->smartFileInfo = $smartFileInfo;
        $this->diff = $diff;
        $this->rectorWithLineChanges = $rectorWithLineChanges;
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
     * @return RectorWithLineChange[]
     */
    public function getRectorChanges(): array
    {
        return $this->rectorWithLineChanges;
    }

    /**
     * @return array<class-string<RectorInterface>>
     */
    public function getRectorClasses(): array
    {
        $rectorClasses = [];
        foreach ($this->rectorWithLineChanges as $rectorWithLineChange) {
            $rectorClasses[] = $rectorWithLineChange->getRectorClass();
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
