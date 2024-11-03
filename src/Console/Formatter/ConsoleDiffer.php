<?php

declare (strict_types=1);
namespace Rector\Console\Formatter;

use RectorPrefix202411\SebastianBergmann\Diff\Differ;
use RectorPrefix202411\SebastianBergmann\Diff\Output\UnifiedDiffOutputBuilder;
final class ConsoleDiffer
{
    /**
     * @readonly
     * @var \Rector\Console\Formatter\ColorConsoleDiffFormatter
     */
    private $colorConsoleDiffFormatter;
    /**
     * @readonly
     * @var \SebastianBergmann\Diff\Differ
     */
    private $differ;
    public function __construct(\Rector\Console\Formatter\ColorConsoleDiffFormatter $colorConsoleDiffFormatter)
    {
        $this->colorConsoleDiffFormatter = $colorConsoleDiffFormatter;
        // @see https://github.com/sebastianbergmann/diff#strictunifieddiffoutputbuilder
        // @see https://github.com/sebastianbergmann/diff/compare/4.0.4...5.0.0#diff-251edf56a6344c03fa264a4926b06c2cee43c25f66192d5f39ebee912b7442dc for upgrade
        $unifiedDiffOutputBuilder = new UnifiedDiffOutputBuilder();
        $this->differ = new Differ($unifiedDiffOutputBuilder);
    }
    public function diff(string $old, string $new) : string
    {
        $diff = $this->differ->diff($old, $new);
        return $this->colorConsoleDiffFormatter->format($diff);
    }
}
