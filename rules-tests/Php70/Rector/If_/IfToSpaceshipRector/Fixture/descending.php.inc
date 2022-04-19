<?php

namespace Rector\Tests\Php70\Rector\If_\IfToSpaceshipRector\Reverse;

class Descending
{
    public function run()
    {
        $languages = [];
        usort($languages, function ($a, $b) {
            if ($a[0] === $b[0]) {
                return 0;
            } else {
                return ($a[0] < $b[0]) ? 1 : -1;
            }
        });

        usort($languages, function ($a, $b) {
            if ($a[0] === $b[0]) {
                return 0;
            } else {
                return ($b[0] < $a[0]) ? -1 : 1;
            }
        });

        usort($languages, function ($a, $b) {
            if ($a[0] === $b[0]) {
                return 0;
            }
            return ($a[0] < $b[0]) ? 1 : -1;
        });

        usort($languages, function ($a, $b) {
            if ($a[0] === $b[0]) {
                return 0;
            } else {
                return ($a[0] > $b[0]) ? -1 : 1;
            }
        });

        usort($languages, function ($a, $b) {
            if ($a[0] === $b[0]) {
                return 0;
            }
            return ($a[0] > $b[0]) ? -1 : 1;
        });
    }
}

?>
-----
<?php

namespace Rector\Tests\Php70\Rector\If_\IfToSpaceshipRector\Reverse;

class Descending
{
    public function run()
    {
        $languages = [];
        usort($languages, function ($a, $b) {
            return $b[0] <=> $a[0];
        });

        usort($languages, function ($a, $b) {
            return $b[0] <=> $a[0];
        });

        usort($languages, function ($a, $b) {
            return $b[0] <=> $a[0];
        });

        usort($languages, function ($a, $b) {
            return $b[0] <=> $a[0];
        });

        usort($languages, function ($a, $b) {
            return $b[0] <=> $a[0];
        });
    }
}

?>
