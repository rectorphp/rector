<?php

namespace Rector\Php70\Tests\Rector\If_\IfToSpaceshipRector\Fixture;

class Skip
{
    public function run()
    {
        usort($languages, function ($a, $b) {
            if ($a[0] > $b[0]) {
                return 0;
            }

            return ($a[0] < $b[0]) ? 1 : -1;
        });

        usort($languages, function ($a, $b) {
            if ($a[0] === $b[0]) {
                return -1;
            }

            return ($a[0] < $b[0]) ? 1 : -1;
        });

        usort($languages, function ($a, $b) {
            if ($a[0] === $b[0]) {
                return 0;
            }

            return ($a[0] <= $b[0]) ? 1 : -1;
        });

        usort($languages, function ($a, $b) {
            if ($a[0] === $b[0]) {
                return 0;
            }

            return ($a[0] < $b[0]) ? -1 : 1;
        });

        usort($languages, function ($a, $b) {
            if ($a[0] === $b[0]) {
                return 0;
            }

            return ($a[0] > $b[0]) ? 0 : 1;
        });
    }
}
