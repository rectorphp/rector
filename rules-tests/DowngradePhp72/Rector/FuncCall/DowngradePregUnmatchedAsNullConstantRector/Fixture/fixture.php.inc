<?php

namespace Rector\Tests\DowngradePhp72\Rector\FuncCall\DowngradePregUnmatchedAsNullConstantRector\Fixture;

class Fixture
{
    public function run()
    {
        preg_match('/(a)(b)*(c)/', 'ac', $matches, PREG_UNMATCHED_AS_NULL);
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp72\Rector\FuncCall\DowngradePregUnmatchedAsNullConstantRector\Fixture;

class Fixture
{
    public function run()
    {
        preg_match('/(a)(b)*(c)/', 'ac', $matches);
        array_walk_recursive($matches, function (&$value) {
            if ($value === '') {
                $value = null;
            }
        });
    }
}

?>
