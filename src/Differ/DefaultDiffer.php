<?php

declare (strict_types=1);
namespace Rector\Core\Differ;

use RectorPrefix202304\SebastianBergmann\Diff\Differ;
use RectorPrefix202304\SebastianBergmann\Diff\Output\StrictUnifiedDiffOutputBuilder;
final class DefaultDiffer
{
    /**
     * @readonly
     * @var \SebastianBergmann\Diff\Differ
     */
    private $differ;
    public function __construct()
    {
        $strictUnifiedDiffOutputBuilder = new StrictUnifiedDiffOutputBuilder(['fromFile' => 'Original', 'toFile' => 'New']);
        $this->differ = new Differ($strictUnifiedDiffOutputBuilder);
    }
    public function diff(string $old, string $new) : string
    {
        if ($old === $new) {
            return '';
        }
        return $this->differ->diff($old, $new);
    }
}
