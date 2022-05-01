<?php

declare (strict_types=1);
namespace Rector\Core\ValueObject\Reporting;

use RectorPrefix20220501\Nette\Utils\Strings;
use Rector\ChangesReporting\ValueObject\RectorWithLineChange;
use Rector\Core\Contract\Rector\RectorInterface;
use RectorPrefix20220501\Symplify\EasyParallel\Contract\SerializableInterface;
use RectorPrefix20220501\Webmozart\Assert\Assert;
final class FileDiff implements \RectorPrefix20220501\Symplify\EasyParallel\Contract\SerializableInterface
{
    /**
     * @var string
     * @se https://regex101.com/r/AUPIX4/1
     */
    private const FIRST_LINE_REGEX = '#@@(.*?)(?<' . self::FIRST_LINE_KEY . '>\\d+)(.*?)@@#';
    /**
     * @var string
     */
    private const FIRST_LINE_KEY = 'first_line';
    /**
     * @var string
     */
    private const KEY_RELATIVE_FILE_PATH = 'relative_file_path';
    /**
     * @var string
     */
    private const KEY_DIFF = 'diff';
    /**
     * @var string
     */
    private const KEY_DIFF_CONSOLE_FORMATTED = 'diff_console_formatted';
    /**
     * @var string
     */
    private const KEY_RECTORS_WITH_LINE_CHANGES = 'rectors_with_line_changes';
    /**
     * @readonly
     * @var string
     */
    private $relativeFilePath;
    /**
     * @readonly
     * @var string
     */
    private $diff;
    /**
     * @readonly
     * @var string
     */
    private $diffConsoleFormatted;
    /**
     * @var RectorWithLineChange[]
     * @readonly
     */
    private $rectorsWithLineChanges = [];
    /**
     * @param RectorWithLineChange[] $rectorsWithLineChanges
     */
    public function __construct(string $relativeFilePath, string $diff, string $diffConsoleFormatted, array $rectorsWithLineChanges = [])
    {
        $this->relativeFilePath = $relativeFilePath;
        $this->diff = $diff;
        $this->diffConsoleFormatted = $diffConsoleFormatted;
        $this->rectorsWithLineChanges = $rectorsWithLineChanges;
        \RectorPrefix20220501\Webmozart\Assert\Assert::allIsAOf($rectorsWithLineChanges, \Rector\ChangesReporting\ValueObject\RectorWithLineChange::class);
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
    /**
     * @return RectorWithLineChange[]
     */
    public function getRectorChanges() : array
    {
        return $this->rectorsWithLineChanges;
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
        $match = \RectorPrefix20220501\Nette\Utils\Strings::match($this->diff, self::FIRST_LINE_REGEX);
        // probably some error in diff
        if (!isset($match[self::FIRST_LINE_KEY])) {
            return null;
        }
        return (int) $match[self::FIRST_LINE_KEY] - 1;
    }
    /**
     * @return array{relative_file_path: string, diff: string, diff_console_formatted: string, rectors_with_line_changes: RectorWithLineChange[]}
     */
    public function jsonSerialize() : array
    {
        return [self::KEY_RELATIVE_FILE_PATH => $this->relativeFilePath, self::KEY_DIFF => $this->diff, self::KEY_DIFF_CONSOLE_FORMATTED => $this->diffConsoleFormatted, self::KEY_RECTORS_WITH_LINE_CHANGES => $this->rectorsWithLineChanges];
    }
    /**
     * @param array<string, mixed> $json
     */
    public static function decode(array $json) : \RectorPrefix20220501\Symplify\EasyParallel\Contract\SerializableInterface
    {
        $rectorWithLineChanges = [];
        foreach ($json[self::KEY_RECTORS_WITH_LINE_CHANGES] as $rectorWithLineChangesJson) {
            $rectorWithLineChanges[] = \Rector\ChangesReporting\ValueObject\RectorWithLineChange::decode($rectorWithLineChangesJson);
        }
        return new self($json[self::KEY_RELATIVE_FILE_PATH], $json[self::KEY_DIFF], $json[self::KEY_DIFF_CONSOLE_FORMATTED], $rectorWithLineChanges);
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
