<?php

namespace Rector\Tests\DowngradePhp72\Rector\FuncCall\DowngradePregUnmatchedAsNullConstantRector\Fixture;

class WithFlags2
{
    public function run()
    {
        preg_match('/(a)(b)*(c)/', 'ac', $matches, $flags | PREG_OFFSET_CAPTURE | PREG_UNMATCHED_AS_NULL);
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp72\Rector\FuncCall\DowngradePregUnmatchedAsNullConstantRector\Fixture;

class WithFlags2
{
    public function run()
    {
        preg_match('/(a)(b)*(c)/', 'ac', $matches, $flags | PREG_OFFSET_CAPTURE);
        array_walk_recursive($matches, function (&$value) {
            if ($value === '') {
                $value = null;
            }
        });
    }
}

?>
