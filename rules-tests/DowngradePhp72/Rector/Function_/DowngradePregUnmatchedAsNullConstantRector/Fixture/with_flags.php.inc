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
        (function (&$matches) {
            foreach ($matches as &$match) {
                if ($match === '') {
                    $match = null;
                }
            }
        })($matches);
    }
}

?>
