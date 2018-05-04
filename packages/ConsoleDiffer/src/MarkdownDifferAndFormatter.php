<?php declare(strict_types=1);

namespace Rector\ConsoleDiffer;

use Rector\ConsoleDiffer\Console\Formatter\DiffConsoleFormatter;
use SebastianBergmann\Diff\Differ;

final class MarkdownDifferAndFormatter
{
    /**
     * @var DiffConsoleFormatter
     */
    private $diffConsoleFormatter;

    /**
     * @var Differ
     */
    private $markdownDiffer;

    public function __construct(DiffConsoleFormatter $diffConsoleFormatter, Differ $markdownDiffer)
    {
        $this->diffConsoleFormatter = $diffConsoleFormatter;
        $this->markdownDiffer = $markdownDiffer;
    }

    /**
     * Returns only the diff (- and + lines), no extra elements, lines nor ----- around it
     */
    public function bareDiffAndFormat(string $old, string $new): string
    {
        $diff = $this->bareDiffAndFormatWithoutColors($old, $new);
        if ($diff === '') {
            return '';
        }

        return $this->diffConsoleFormatter->bareFormat($diff);
    }

    public function bareDiffAndFormatWithoutColors(string $old, string $new): string
    {
        if ($old === $new) {
            return '';
        }

        $diff = $this->markdownDiffer->diff($old, $new);

        // remove first line, just meta info added by UnifiedDiffOutputBuilder
        $diff = preg_replace("/^(.*\n){1}/", '', $diff);

        $diff = $this->removeTrailingWhitespaces($diff);

        return $diff;
    }

    /**
     * Removes UnifiedDiffOutputBuilder generated pre-spaces " \n" => "\n"
     */
    private function removeTrailingWhitespaces(string $diff): string
    {
        $diff = preg_replace('#( ){1,}\n#', PHP_EOL, $diff);

        return rtrim($diff);
    }
}
