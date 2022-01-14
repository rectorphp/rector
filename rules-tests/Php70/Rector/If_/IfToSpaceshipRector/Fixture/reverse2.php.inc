<?php

namespace Rector\Tests\Php70\Rector\If_\IfToSpaceshipRector\Reverse;

class Reverse2
{
    public function run()
    {
        $languages = [];
        usort($languages, function ($a, $b) {
            if ($a[0] === $b[0]) {
                return 0;
            }

            return ($a[0] < $b[0]) ? -1 : 1;
        });
    }
}

?>
-----
<?php

namespace Rector\Tests\Php70\Rector\If_\IfToSpaceshipRector\Reverse;

class Reverse2
{
    public function run()
    {
        $languages = [];
        usort($languages, function ($a, $b) {
            return $a[0] <=> $b[0];
        });
    }
}

?>
