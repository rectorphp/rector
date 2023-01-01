<?php

declare (strict_types=1);
namespace Rector\Core\Console\Formatter;

use RectorPrefix202301\SebastianBergmann\Diff\Differ;
final class ConsoleDiffer
{
    /**
     * @readonly
     * @var \SebastianBergmann\Diff\Differ
     */
    private $differ;
    /**
     * @readonly
     * @var \Rector\Core\Console\Formatter\ColorConsoleDiffFormatter
     */
    private $colorConsoleDiffFormatter;
    public function __construct(Differ $differ, \Rector\Core\Console\Formatter\ColorConsoleDiffFormatter $colorConsoleDiffFormatter)
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
