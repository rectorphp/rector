<?php

namespace Rector\Tests\DowngradePhp72\Rector\Function_\DowngradePregUnmatchedAsNullConstantRector\Fixture;

class PregMatchAll
{
    public function run()
    {
        preg_match_all('/(a)(b)*(c)/', 'ac', $matches);
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp72\Rector\Function_\DowngradePregUnmatchedAsNullConstantRector\Fixture;

class PregMatchAll
{
    public function run()
    {
        preg_match_all('/(a)(b)*(c)/', 'ac', $matches);
        array_walk_recursive($matches, function (& $value) {
            if ($value === '') {
                $value = null;
            }
        });
    }
}

?>
