<?php

declare (strict_types=1);
namespace Rector\Differ;

use RectorPrefix202506\SebastianBergmann\Diff\Differ;
use RectorPrefix202506\SebastianBergmann\Diff\Output\StrictUnifiedDiffOutputBuilder;
final class DefaultDiffer
{
    /**
     * @readonly
     */
    private Differ $differ;
    public function __construct()
    {
        $strictUnifiedDiffOutputBuilder = new StrictUnifiedDiffOutputBuilder(['fromFile' => 'Original', 'toFile' => 'New']);
        $this->differ = new Differ($strictUnifiedDiffOutputBuilder);
    }
    public function diff(string $old, string $new) : string
    {
        return $this->differ->diff($old, $new);
    }
}
