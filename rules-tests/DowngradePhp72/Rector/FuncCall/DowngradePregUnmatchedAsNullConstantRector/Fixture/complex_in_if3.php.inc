<?php

namespace Rector\Tests\DowngradePhp72\Rector\FuncCall\DowngradePregUnmatchedAsNullConstantRector\Fixture;

class ComplexInIf3
{
    public function run()
    {
        $match = (\PREG_PATTERN_ORDER | \PREG_SET_ORDER) & $flags ? 'preg_match_all' : 'preg_match';
        try {
            if ($match($regexp, $this->string, $matches, $flags | \PREG_UNMATCHED_AS_NULL, $offset)) {
                echo 'statement';
            }
        } catch (\Exception $e) {

        }
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp72\Rector\FuncCall\DowngradePregUnmatchedAsNullConstantRector\Fixture;

class ComplexInIf3
{
    public function run()
    {
        $match = (\PREG_PATTERN_ORDER | \PREG_SET_ORDER) & $flags ? 'preg_match_all' : 'preg_match';
        try {
            if ($match($regexp, $this->string, $matches, $flags, $offset)) {
                array_walk_recursive($matches, function (&$value) {
                    if ($value === '') {
                        $value = null;
                    }
                });
                echo 'statement';
            }
        } catch (\Exception $e) {

        }
    }
}

?>
