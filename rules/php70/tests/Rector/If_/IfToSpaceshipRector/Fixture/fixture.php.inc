<?php

namespace Rector\Php70\Tests\Rector\If_\IfToSpaceshipRector\Fixture;

class Fixture
{
    public function run()
    {
        usort($languages, function ($a, $b) {
            if ($a[0] == $b[0]) {
                return 0;
            }

            return ($a[0] < $b[0]) ? 1 : -1;
        });
    }

    public function runAgain()
    {
        usort($languages, function ($a, $b) {
            if ($b[0] === $a[0]) {
                return 0;
            }

            return ($a[0] < $b[0]) ? 1 : -1;
        });
    }

    public function runOneMore()
    {
        usort($languages, function ($a, $b) {
            if ($b[0] === $a[0]) {
                return 0;
            }

            return ($a[0] > $b[0]) ? -1 : 1;
        });
    }
}

?>
-----
<?php

namespace Rector\Php70\Tests\Rector\If_\IfToSpaceshipRector\Fixture;

class Fixture
{
    public function run()
    {
        usort($languages, function ($a, $b) {
            return $b[0] <=> $a[0];
        });
    }

    public function runAgain()
    {
        usort($languages, function ($a, $b) {
            return $b[0] <=> $a[0];
        });
    }

    public function runOneMore()
    {
        usort($languages, function ($a, $b) {
            return $a[0] <=> $b[0];
        });
    }
}

?>
