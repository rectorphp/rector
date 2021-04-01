<?php

namespace Rector\Tests\DowngradePhp72\Rector\FuncCall\DowngradePregUnmatchedAsNullConstantRector\Fixture;

class WithFlags4
{
    public function run()
    {
        preg_match('/(a)(b)*(c)/', 'ac', $matches, PREG_OFFSET_CAPTURE | PREG_UNMATCHED_AS_NULL | $flag | $anotherFlag);
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp72\Rector\FuncCall\DowngradePregUnmatchedAsNullConstantRector\Fixture;

class WithFlags4
{
    public function run()
    {
        preg_match('/(a)(b)*(c)/', 'ac', $matches, PREG_OFFSET_CAPTURE | $flag | $anotherFlag);
        array_walk_recursive($matches, function (&$value) {
            if ($value === '') {
                $value = null;
            }
        });
    }
}

?>
