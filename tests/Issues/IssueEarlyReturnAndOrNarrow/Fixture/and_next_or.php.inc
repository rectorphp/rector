<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\IssueEarlyReturnAndOrNarrow\Fixture;

class AndNextOrReturnVoid
{
    public function run($a, $b, $c, $d)
    {
        if ($a && $b  || $c) {
            return null;
        }

        return;
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\IssueEarlyReturnAndOrNarrow\Fixture;

class AndNextOrReturnVoid
{
    /**
     * @return null|void
     */
    public function run($a, $b, $c, $d)
    {
        if ($a && $b) {
            return null;
        }
        if ($c) {
            return null;
        }
        return;
    }
}

?>
