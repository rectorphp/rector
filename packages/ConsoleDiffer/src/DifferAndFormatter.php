<?php declare(strict_types=1);

namespace Rector\ConsoleDiffer;

use Rector\ConsoleDiffer\Console\Formatter\DiffConsoleFormatter;
use SebastianBergmann\Diff\Differ;

final class DifferAndFormatter
{
    /**
     * @var Differ
     */
    private $differ;

    /**
     * @var DiffConsoleFormatter
     */
    private $diffConsoleFormatter;

    /**
     * @var Differ
     */
    private $bareDiffer;

    public function __construct(Differ $differ, DiffConsoleFormatter $diffConsoleFormatter, Differ $bareDiffer)
    {
        $this->differ = $differ;
        $this->diffConsoleFormatter = $diffConsoleFormatter;
        $this->bareDiffer = $bareDiffer;
    }

    public function diffAndFormat(string $old, string $new): string
    {
        if ($old === $new) {
            return '';
        }

        $diff = $this->differ->diff($old, $new);

        return $this->diffConsoleFormatter->format($diff);
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

        $diff = $this->bareDiffer->diff($old, $new);
        // impossible to configure - removed manually

        return substr($diff, strlen('@@ @@ '));
    }
}
