<?php

namespace Rector\Tests\DowngradePhp72\Rector\FuncCall\DowngradePregUnmatchedAsNullConstantRector\Fixture;

class ComplexInIfNegation
{
    public function run()
    {
        try {
            if (!preg_match_all($regexp, $this->string, $matches, $flags | \PREG_UNMATCHED_AS_NULL, $offset)) {
            }
        } catch (\Exception $e) {

        }
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp72\Rector\FuncCall\DowngradePregUnmatchedAsNullConstantRector\Fixture;

class ComplexInIfNegation
{
    public function run()
    {
        try {
            if (!preg_match_all($regexp, $this->string, $matches, $flags, $offset)) {
            }
            array_walk_recursive($matches, function (&$value) {
                if ($value === '') {
                    $value = null;
                }
            });
        } catch (\Exception $e) {

        }
    }
}

?>
