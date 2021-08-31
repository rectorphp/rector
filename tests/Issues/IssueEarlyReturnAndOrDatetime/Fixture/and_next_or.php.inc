<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\IssueEarlyReturnAndOrDatetime\Fixture;

class AndNextOr
{
    public function run($a, $b, $c, $d)
    {
        if ($a && $b || $c) {
            return null;
        }

        $a = 1;
        $b = 2;

        if ($b > $a) {
            return null;
        }

        $d = 1;

        return;
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\IssueEarlyReturnAndOrDatetime\Fixture;

class AndNextOr
{
    public function run($a, $b, $c, $d)
    {
        if ($a && $b) {
            return null;
        }
        if ($c) {
            return null;
        }
        $a = 1;
        $b = 2;

        if ($b > $a) {
            return null;
        }

        $d = 1;

        return;
    }
}

?>
