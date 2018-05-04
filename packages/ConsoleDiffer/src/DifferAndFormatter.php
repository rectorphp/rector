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

    public function __construct(Differ $differ, DiffConsoleFormatter $diffConsoleFormatter)
    {
        $this->differ = $differ;
        $this->diffConsoleFormatter = $diffConsoleFormatter;
    }

    public function diffAndFormat(string $old, string $new): string
    {
        if ($old === $new) {
            return '';
        }

        $diff = $this->differ->diff($old, $new);

        return $this->diffConsoleFormatter->format($diff);
    }
}
