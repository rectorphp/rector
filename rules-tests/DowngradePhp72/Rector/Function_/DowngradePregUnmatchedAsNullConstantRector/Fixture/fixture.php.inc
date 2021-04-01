<?php

namespace Rector\Tests\DowngradePhp72\Rector\Function_\DowngradePregUnmatchedAsNullConstantRector\Fixture;

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

namespace Rector\Tests\DowngradePhp72\Rector\Function_\DowngradePregUnmatchedAsNullConstantRector\Fixture;

class Fixture
{
    public function run()
    {
        preg_match('/(a)(b)*(c)/', 'ac', $matches);
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
