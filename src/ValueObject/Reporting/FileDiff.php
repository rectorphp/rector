<?php

declare (strict_types=1);
namespace Rector\ValueObject\Reporting;

use RectorPrefix202506\Nette\Utils\Strings;
use Rector\ChangesReporting\ValueObject\RectorWithLineChange;
use Rector\Contract\Rector\RectorInterface;
use Rector\Parallel\ValueObject\BridgeItem;
use RectorPrefix202506\Symplify\EasyParallel\Contract\SerializableInterface;
use RectorPrefix202506\Webmozart\Assert\Assert;
final class FileDiff implements SerializableInterface
{
    /**
     * @readonly
     */
    private string $relativeFilePath;
    /**
     * @readonly
     */
    private string $diff;
    /**
     * @readonly
     */
    private string $diffConsoleFormatted;
    /**
     * @var RectorWithLineChange[]
     * @readonly
     */
    private array $rectorsWithLineChanges = [];
    /**
     * @var string
     * @see https://en.wikipedia.org/wiki/Diff#Unified_format
     * @see https://regex101.com/r/AUPIX4/2
     */
    private const DIFF_HUNK_HEADER_REGEX = '#@@(.*?)(?<' . self::FIRST_LINE_KEY . '>\\d+)(,(?<' . self::LINE_RANGE_KEY . '>\\d+))?(.*?)@@#';
    /**
     * @var string
     */
    private const FIRST_LINE_KEY = 'first_line';
    private const LINE_RANGE_KEY = 'line_range';
    /**
     * @param RectorWithLineChange[] $rectorsWithLineChanges
     */
    public function __construct(string $relativeFilePath, string $diff, string $diffConsoleFormatted, array $rectorsWithLineChanges = [])
    {
        $this->relativeFilePath = $relativeFilePath;
        $this->diff = $diff;
        $this->diffConsoleFormatted = $diffConsoleFormatted;
        $this->rectorsWithLineChanges = $rectorsWithLineChanges;
        Assert::allIsInstanceOf($rectorsWithLineChanges, RectorWithLineChange::class);
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
        return $this->relativeFilePath;
    }
    public function getAbsoluteFilePath() : ?string
    {
        return \realpath($this->relativeFilePath) ?: null;
    }
    /**
     * @return RectorWithLineChange[]
     */
    public function getRectorChanges() : array
    {
        return $this->rectorsWithLineChanges;
    }
    /**
     * @return string[]
     */
    public function getRectorShortClasses() : array
    {
        $rectorShortClasses = [];
        foreach ($this->getRectorClasses() as $rectorClass) {
            $rectorShortClasses[] = (string) Strings::after($rectorClass, '\\', -1);
        }
        return $rectorShortClasses;
    }
    /**
     * @return array<class-string<RectorInterface>>
     */
    public function getRectorClasses() : array
    {
        $rectorClasses = [];
        foreach ($this->rectorsWithLineChanges as $rectorWithLineChange) {
            $rectorClasses[] = $rectorWithLineChange->getRectorClass();
        }
        return $this->sortClasses($rectorClasses);
    }
    public function getFirstLineNumber() : ?int
    {
        $match = Strings::match($this->diff, self::DIFF_HUNK_HEADER_REGEX);
        // probably some error in diff
        if (!isset($match[self::FIRST_LINE_KEY])) {
            return null;
        }
        return (int) $match[self::FIRST_LINE_KEY];
    }
    public function getLastLineNumber() : ?int
    {
        $match = Strings::match($this->diff, self::DIFF_HUNK_HEADER_REGEX);
        $firstLine = $this->getFirstLineNumber();
        // probably some error in diff
        if (!isset($match[self::LINE_RANGE_KEY])) {
            return $firstLine;
        }
        // line range is not mandatory
        if ($match[self::LINE_RANGE_KEY] === '') {
            return $firstLine;
        }
        $lineRange = (int) $match[self::LINE_RANGE_KEY];
        return $firstLine + $lineRange;
    }
    /**
     * @return array{relative_file_path: string, diff: string, diff_console_formatted: string, rectors_with_line_changes: RectorWithLineChange[]}
     */
    public function jsonSerialize() : array
    {
        return [BridgeItem::RELATIVE_FILE_PATH => $this->relativeFilePath, BridgeItem::DIFF => $this->diff, BridgeItem::DIFF_CONSOLE_FORMATTED => $this->diffConsoleFormatted, BridgeItem::RECTORS_WITH_LINE_CHANGES => $this->rectorsWithLineChanges];
    }
    /**
     * @param array<string, mixed> $json
     */
    public static function decode(array $json) : self
    {
        $rectorWithLineChanges = [];
        foreach ($json[BridgeItem::RECTORS_WITH_LINE_CHANGES] as $rectorWithLineChangesJson) {
            $rectorWithLineChanges[] = RectorWithLineChange::decode($rectorWithLineChangesJson);
        }
        return new self($json[BridgeItem::RELATIVE_FILE_PATH], $json[BridgeItem::DIFF], $json[BridgeItem::DIFF_CONSOLE_FORMATTED], $rectorWithLineChanges);
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
