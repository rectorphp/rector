<?php

declare (strict_types=1);
namespace RectorPrefix20210603\Symplify\ConsoleColorDiff\Console\Output;

use RectorPrefix20210603\SebastianBergmann\Diff\Differ;
use RectorPrefix20210603\Symplify\ConsoleColorDiff\Console\Formatter\ColorConsoleDiffFormatter;
final class ConsoleDiffer
{
    /**
     * @var Differ
     */
    private $differ;
    /**
     * @var ColorConsoleDiffFormatter
     */
    private $colorConsoleDiffFormatter;
    public function __construct(\RectorPrefix20210603\SebastianBergmann\Diff\Differ $differ, \RectorPrefix20210603\Symplify\ConsoleColorDiff\Console\Formatter\ColorConsoleDiffFormatter $colorConsoleDiffFormatter)
    {
        $this->differ = $differ;
        $this->colorConsoleDiffFormatter = $colorConsoleDiffFormatter;
    }
    public function diff(string $old, string $new) : string
    {
        $diff = $this->differ->diff($old, $new);
        return $this->colorConsoleDiffFormatter->format($diff);
    }
}
