<?php

declare(strict_types=1);

namespace Rector\Core\ValueObject\Reporting;

use Nette\Utils\Strings;
use Rector\ChangesReporting\ValueObject\RectorWithLineChange;
use Rector\Core\Contract\Rector\RectorInterface;
use Symplify\SmartFileSystem\SmartFileInfo;

final class FileDiff
{
    /**
     * @var string
     * @se https://regex101.com/r/AUPIX4/1
     */
    private const FIRST_LINE_REGEX = '#@@(.*?)(?<' . self::FIRST_LINE_KEY . '>\d+)(.*?)@@#';

    /**
     * @var string
     */
    private const FIRST_LINE_KEY = 'first_line';

    /**
     * @param RectorWithLineChange[] $rectorWithLineChanges
     */
    public function __construct(
        private SmartFileInfo $smartFileInfo,
        private string $diff,
        private string $diffConsoleFormatted,
        private array $rectorWithLineChanges = []
    ) {
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

    public function getFirstLineNumber(): ?int
    {
        $match = Strings::match($this->diff, self::FIRST_LINE_REGEX);

        // probably some error in diff
        if (! isset($match[self::FIRST_LINE_KEY])) {
            return null;
        }

        return (int) $match[self::FIRST_LINE_KEY] - 1;
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
