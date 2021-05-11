<?php

declare (strict_types=1);
namespace Rector\Core\ValueObject\Reporting;

use Rector\ChangesReporting\ValueObject\RectorWithLineChange;
use Rector\Core\Contract\Rector\RectorInterface;
use Symplify\SmartFileSystem\SmartFileInfo;
final class FileDiff
{
    /**
     * @var \Symplify\SmartFileSystem\SmartFileInfo
     */
    private $smartFileInfo;
    /**
     * @var string
     */
    private $diff;
    /**
     * @var string
     */
    private $diffConsoleFormatted;
    /**
     * @var mixed[]
     */
    private $rectorWithLineChanges = [];
    /**
     * @param RectorWithLineChange[] $rectorWithLineChanges
     */
    public function __construct(\Symplify\SmartFileSystem\SmartFileInfo $smartFileInfo, string $diff, string $diffConsoleFormatted, array $rectorWithLineChanges = [])
    {
        $this->smartFileInfo = $smartFileInfo;
        $this->diff = $diff;
        $this->diffConsoleFormatted = $diffConsoleFormatted;
        $this->rectorWithLineChanges = $rectorWithLineChanges;
    }
    public function getDiff() : string
    {
        return $this->diff;
    }
    public function getDiffConsoleFormatted() : string
    {
        return $this->diffConsoleFormatted;
    }
    public function getRelativeFilePath() : string
    {
        return $this->smartFileInfo->getRelativeFilePath();
    }
    public function getFileInfo() : \Symplify\SmartFileSystem\SmartFileInfo
    {
        return $this->smartFileInfo;
    }
    /**
     * @return RectorWithLineChange[]
     */
    public function getRectorChanges() : array
    {
        return $this->rectorWithLineChanges;
    }
    /**
     * @return array<class-string<RectorInterface>>
     */
    public function getRectorClasses() : array
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
    private function sortClasses(array $rectorClasses) : array
    {
        $rectorClasses = \array_unique($rectorClasses);
        \sort($rectorClasses);
        return $rectorClasses;
    }
}
