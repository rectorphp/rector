<?php

namespace Rector\Tests\DowngradePhp72\Rector\Function_\DowngradePregUnmatchedAsNullConstantRector\Fixture;

class WithFlags
{
    public function run()
    {
        preg_match('/(a)(b)*(c)/', 'ac', $matches, $flags | PREG_UNMATCHED_AS_NULL);
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp72\Rector\Function_\DowngradePregUnmatchedAsNullConstantRector\Fixture;

class WithFlags
{
    public function run()
    {
        preg_match('/(a)(b)*(c)/', 'ac', $matches, $flags);
        foreach ($matches as $key => $match) {
            if ($match === '') {
                $matches[$key] = null;
            }
        }
    }
}

?>
