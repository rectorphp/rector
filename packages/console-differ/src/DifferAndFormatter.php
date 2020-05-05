<?php

declare(strict_types=1);

namespace Rector\ConsoleDiffer;

use SebastianBergmann\Diff\Differ;
use Symplify\ConsoleColorDiff\Console\Formatter\ColorConsoleDiffFormatter;

final class DifferAndFormatter
{
    /**
     * @var Differ
     */
    private $differ;

    /**
     * @var ColorConsoleDiffFormatter
     */
    private $colorConsoleDiffFormatter;

    public function __construct(Differ $differ, ColorConsoleDiffFormatter $colorConsoleDiffFormatter)
    {
        $this->differ = $differ;
        $this->colorConsoleDiffFormatter = $colorConsoleDiffFormatter;
    }

    public function diff(string $old, string $new): string
    {
        if ($old === $new) {
            return '';
        }

        return $this->differ->diff($old, $new);
    }

    public function diffAndFormat(string $old, string $new): string
    {
        if ($old === $new) {
            return '';
        }

        $diff = $this->diff($old, $new);

        return $this->colorConsoleDiffFormatter->format($diff);
    }
}
