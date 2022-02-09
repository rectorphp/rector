<?php

declare (strict_types=1);
namespace Rector\Core\Differ;

use RectorPrefix20220209\SebastianBergmann\Diff\Differ;
use RectorPrefix20220209\SebastianBergmann\Diff\Output\StrictUnifiedDiffOutputBuilder;
final class DefaultDiffer
{
    /**
     * @readonly
     * @var \SebastianBergmann\Diff\Differ
     */
    private $differ;
    public function __construct()
    {
        $strictUnifiedDiffOutputBuilder = new \RectorPrefix20220209\SebastianBergmann\Diff\Output\StrictUnifiedDiffOutputBuilder(['fromFile' => 'Original', 'toFile' => 'New']);
        $this->differ = new \RectorPrefix20220209\SebastianBergmann\Diff\Differ($strictUnifiedDiffOutputBuilder);
    }
    public function diff(string $old, string $new) : string
    {
        if ($old === $new) {
            return '';
        }
        return $this->differ->diff($old, $new);
    }
}
